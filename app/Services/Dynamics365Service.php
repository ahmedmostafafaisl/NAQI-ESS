<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Generic client + domain helpers for talking to Microsoft Dynamics 365
 * (Web API, OAuth2 client-credentials flow via Azure AD).
 *
 * Token endpoint is deliberately the v1 endpoint (`/oauth2/token`) with a
 * `resource` parameter — NOT the v2.0 (`/oauth2/v2.0/token`) `scope`-based
 * flow. This matches the tenant configuration confirmed working via Postman;
 * some Dynamics 365 tenants reject the v2.0/scope flow for the Web API.
 */
class Dynamics365Service
{
    protected string $resource;
    protected string $apiVersion;

    public function __construct()
    {
        $this->resource = rtrim(config('services.dynamics365.resource'), '/');
        // $this->resource = "https://naqi-dev07e0d2be09243f5188devaos.axcloud.dynamics.com";
        $this->apiVersion = config('services.dynamics365.api_version');
    }

    /**
     * Fetch (and cache) an OAuth2 access token using the client credentials grant.
     * Cached for the token's real `expires_in` (minus a safety buffer), not a
     * hardcoded value — so we never hold onto a token past its actual expiry
     * and never refetch earlier than necessary either.
     *
     * Throws RuntimeException on failure. Nothing is cached on failure, so the
     * very next call will simply retry against Azure.
     */
    public function getAccessToken(?string $resource = null): string
    {
        // A token is only valid for the resource/environment it was issued
        // for. The shared cache key isn't resource-aware, so when a caller
        // overrides the default resource, we deliberately skip the cache
        // entirely rather than risk handing back a token for the wrong
        // environment. Only the default (no override) path is cached.
        if ($resource !== null) {
            $result = $this->requestAccessToken($resource);

            if (! $result['success']) {
                throw new RuntimeException($result['error']);
            }

            return $result['access_token'];
        }

        $cacheKey = config('services.dynamics365.token_cache_key');

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $result = $this->requestAccessToken();

        if (! $result['success']) {
            throw new RuntimeException($result['error']);
        }

        Cache::put($cacheKey, $result['access_token'], $result['ttl_seconds']);

        return $result['access_token'];
    }

    /**
     * Force a fresh token request (bypassing the cache) and return full,
     * display-safe details — used by the "Test Connection" admin page so a
     * human can actually see whether auth against Dynamics 365 is working,
     * without dumping the raw token to a screen.
     *
     * @return array{
     *   success: bool,
     *   token_type: ?string,
     *   expires_in: ?int,
     *   expires_at: ?string,
     *   access_token_preview: ?string,
     *   cached_until: ?string,
     *   error: ?string,
     * }
     */
    public function testConnection(): array
    {
        $result = $this->requestAccessToken();

        if (! $result['success']) {
            return [
                'success' => false,
                'token_type' => null,
                'expires_in' => null,
                'expires_at' => null,
                'access_token_preview' => null,
                'cached_until' => null,
                'error' => $result['error'],
            ];
        }

        Cache::put(config('services.dynamics365.token_cache_key'), $result['access_token'], $result['ttl_seconds']);

        $token = $result['access_token'];
        $preview = substr($token, 0, 12) . '...' . substr($token, -8);

        return [
            'success' => true,
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'expires_at' => now()->addSeconds($result['expires_in'])->toDateTimeString(),
            'access_token_preview' => $preview,
            'cached_until' => now()->addSeconds($result['ttl_seconds'])->toDateTimeString(),
            'error' => null,
        ];
    }

    /**
     * The actual HTTP call to Azure AD's token endpoint. Never caches —
     * callers (getAccessToken / testConnection) decide what to do with the result.
     *
     * @return array{success:bool, access_token?:string, token_type?:string, expires_in?:int, ttl_seconds?:int, error?:string}
     */
    protected function requestAccessToken(?string $resource = null): array
    {
        $tenantId = config('services.dynamics365.tenant_id');

        try {
            $response = Http::asForm()
                ->timeout(config('services.dynamics365.timeout'))
                ->post("https://login.microsoftonline.com/{$tenantId}/oauth2/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('services.dynamics365.client_id'),
                    'client_secret' => config('services.dynamics365.client_secret'),
                    'resource' => $resource ?? $this->resource,
                ]);
        } catch (\Throwable $e) {
            Log::error('Dynamics365: token request threw an exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Could not reach Azure AD: ' . $e->getMessage()];
        }

        if ($response->failed()) {
            Log::error('Dynamics365: failed to obtain access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $body = $response->json();
            $message = $body['error_description'] ?? $body['error'] ?? $response->body();

            return ['success' => false, 'error' => "Azure AD rejected the request ({$response->status()}): {$message}"];
        }

        $body = $response->json();

        if (empty($body['access_token'])) {
            Log::error('Dynamics365: token response missing access_token', ['body' => $body]);

            return ['success' => false, 'error' => 'Azure AD returned 200 but no access_token was present in the response.'];
        }

        $expiresIn = (int) ($body['expires_in'] ?? 3600);
        $buffer = (int) config('services.dynamics365.token_expiry_buffer', 60);
        $ttl = max(30, $expiresIn - $buffer); // never cache for less than 30s

        Log::info('Dynamics365: access token obtained successfully', [
            'token_type' => $body['token_type'] ?? null,
            'expires_in' => $expiresIn,
            'cached_for_seconds' => $ttl,
        ]);

        return [
            'success' => true,
            'access_token' => $body['access_token'],
            'token_type' => $body['token_type'] ?? 'Bearer',
            'expires_in' => $expiresIn,
            'ttl_seconds' => $ttl,
        ];
    }

    /**
     * Call a Dynamics 365 Finance & Operations custom X++ service — a
     * completely different calling convention from the Dataverse OData
     * client() below. F&O custom services live at:
     *   {resource}/api/services/{serviceGroup}/{service}/{operation}
     * and are authenticated with the same app-level Azure AD bearer token,
     * but the request/response shape is whatever that specific X++ service
     * contract defines (commonly wrapped in a `_contract` object).
     *
     * @return array{success:bool, status:int, body:array, error:?string}
     */
    public function callService(string $service, string $operation, array $payload = [], ?string $serviceGroup = null, ?string $resource = null): array
    {
        $serviceGroup ??= config('services.dynamics365.service_group');
        $path = "/api/services/{$serviceGroup}/{$service}/{$operation}";
        $targetResource = $resource ?? $this->resource;

        try {
            $response = Http::withToken($this->getAccessToken($resource))
                ->timeout(config('services.dynamics365.timeout'))
                ->retry(config('services.dynamics365.retry_times'), config('services.dynamics365.retry_sleep_ms'))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$targetResource}{$path}", $payload);
        } catch (\Throwable $e) {
            Log::error('Dynamics365: service call threw an exception', ['path' => $path, 'error' => $e->getMessage()]);

            return ['success' => false, 'status' => 0, 'body' => [], 'error' => 'Could not reach Dynamics 365: ' . $e->getMessage()];
        }

        $body = $response->json() ?? [];

        if ($response->failed()) {
            Log::error('Dynamics365: service call failed', ['path' => $path, 'status' => $response->status(), 'body' => $body]);

            return [
                'success' => false,
                'status' => $response->status(),
                'body' => $body,
                'error' => $body['Error'] ?? $body['error'] ?? "HTTP {$response->status()}",
            ];
        }

        return ['success' => true, 'status' => $response->status(), 'body' => $body, 'error' => null];
    }

    /**
     * Authenticate an end-user (employee/customer) against Dynamics 365 F&O's
     * custom Login service (INDXNaqiEssAuthSvc/Login). This is separate from
     * the app-level Azure AD token above: that token authenticates *this app*
     * to call Dynamics at all, while this call authenticates a specific
     * human's email/password and returns their HR context plus a Dynamics
     * session token (`Data.Token`) for subsequent user-scoped service calls.
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   code: ?int,
     *   token: ?string,
     *   worker: ?string,
     *   is_manager: bool,
     *   first_login: ?bool,
     *   image: ?string,
     *   language: ?string,
     *   services_access_list: array,
     *   raw: array,
     * }
     */
    public function loginUser(
        string $email,
        string $password,
        string $deviceToken = '',
        string $lang = '',
        ?string $deviceId = null,
        string $appVersion = '',
        string $devicePlatform = '',
        ?string $resource = null,
    ): array {
        $result = $this->callService('INDXNaqiEssAuthSvc', 'Login', [
            '_contract' => [
                'lang' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Password' => $password,
                'DeviceToken' => $deviceToken,
                'Version' => $appVersion,
                'mobile' => $devicePlatform,
            ],
        ], resource: $resource);

        if (! $result['success']) {
            return $this->loginFailure($result['error'], $result['body']);
        }

        $body = $result['body'];

        // Transport succeeded (HTTP 200) but Dynamics itself may still report
        // a logical failure (e.g. wrong password) via Status/Error/Code.
        // Prefer whatever Dynamics itself says here — Error is the primary
        // field, Message is a defensive fallback some contracts use instead
        // — only falling back to our own generic text if Dynamics gave us
        // literally nothing to go on.
        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return $this->loginFailure($body['Error'] ?? $body['Message'] ?? 'Login rejected by Dynamics 365.', $body);
        }

        $data = $body['Data'] ?? [];

        return [
            'success' => true,
            'error' => null,
            'code' => $body['Code'] ?? 200,
            'token' => $data['Token'] ?? null,
            'worker' => $data['Worker'] ?? null,
            'mobile' => $data['Mobile'] ?: null,
            'is_manager' => (bool) ($body['IsManager'] ?? false),
            'first_login' => $data['FirstLogin'] ?? null,
            'image' => $data['Image'] ?? null,
            'language' => $data['Language'] ?? null,
            'services_access_list' => $body['UserServicesAccessList']['ServicesList'] ?? [],
            'raw' => $body,
        ];
    }

    protected function loginFailure(?string $error, array $raw): array
    {
        return [
            'success' => false,
            'error' => $error ?: 'Login failed.',
            'code' => $raw['Code'] ?? null,
            'token' => null,
            'worker' => null,
            'mobile' => null,
            'is_manager' => false,
            'first_login' => null,
            'image' => null,
            'language' => null,
            'services_access_list' => [],
            'raw' => $raw,
        ];
    }

    /**
     * Fetch the direct reports (and manager) of the user identified by the
     * given Dynamics session token, via the custom Action service
     * INDXNaqiEssActionMyTeamSvc/getWorkerTeam. Requires a valid session
     * `token` obtained from loginUser() first — this call identifies *whose*
     * team to return by that token, not by the Azure AD app token alone.
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   is_manager: bool,
     *   team: array<int, array{name:string, position:string, personnel_number:string}>,
     *   managers: array<int, array{name:string, position:string, personnel_number:string}>,
     *   raw: array,
     * }
     */
    public function getTeamMembers(string $email, string $token, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionMyTeamSvc', 'getWorkerTeam', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
            ],
        ]);

        if (! $result['success']) {
            return $this->teamMembersFailure($result['error'], $result['body']);
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return $this->teamMembersFailure($body['Error'] ?? 'Request rejected by Dynamics 365.', $body);
        }

        $data = $body['Data'] ?? [];
        $mapWorker = fn(array $w) => [
            'name' => $w['WorkerName'] ?? '',
            'position' => $w['WorkerPosition'] ?? '',
            'personnel_number' => $w['WorkerPersonnelNumber'] ?? '',
        ];

        return [
            'success' => true,
            'error' => null,
            'is_manager' => (bool) ($body['IsManager'] ?? false),
            'team' => array_map($mapWorker, $data['MyTeamDetails'] ?? []),
            'managers' => array_map($mapWorker, $data['MyManagersDetails'] ?? []),
            'raw' => $body,
        ];
    }

    protected function teamMembersFailure(?string $error, array $raw): array
    {
        return [
            'success' => false,
            'error' => $error ?: 'Request failed.',
            'is_manager' => false,
            'team' => [],
            'managers' => [],
            'raw' => $raw,
        ];
    }

    /**
     * Fetch a month's attendance calendar (which days are working days, off
     * days, or holidays) for the employee identified by the session token,
     * via INDXNaqiEssActionAttendanceSvc/getWorkerAttendanceCalendar.
     * Optionally pass $teamWorkerPersonnelNumber for a manager viewing a
     * direct report's calendar instead of their own.
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   days: array<int, array{day:int, day_name:string, is_off_day:bool, is_holiday:bool}>,
     *   raw: array,
     * }
     */
    public function getAttendanceCalendar(string $email, string $token, int $month, int $year, ?string $teamWorkerPersonnelNumber = null, ?string $lang = null): array
    {
        $contract = [
            'language' => $lang ?? config('services.dynamics365.default_lang'),
            'Email' => $email,
            'Token' => $token,
            'Month' => $month,
            'Year' => $year,
        ];

        if ($teamWorkerPersonnelNumber) {
            $contract['TeamWorkerPersonnelNumber'] = $teamWorkerPersonnelNumber;
        }

        $result = $this->callService('INDXNaqiEssActionAttendanceSvc', 'getWorkerAttendanceCalendar', ['_contract' => $contract]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'days' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'days' => [], 'raw' => $body];
        }

        $details = $body['Data']['WorkerAttendanceCalendarDetails'] ?? [];

        $days = array_map(fn(array $d) => [
            'day' => (int) ($d['Day'] ?? 0),
            'day_name' => $d['DayName'] ?? '',
            'is_off_day' => (bool) ($d['IsOffDay'] ?? false),
            'is_holiday' => (bool) ($d['IsHoliday'] ?? false),
        ], $details);

        return ['success' => true, 'error' => null, 'days' => $days, 'raw' => $body];
    }

    /**
     * Fetch a single day's clock-in/out record for the employee identified
     * by the session token, via
     * INDXNaqiEssActionAttendanceSvc/getWorkerAttendanceRecord.
     * $punchDate must be 'Y-m-d'. Optionally pass $teamWorkerPersonnelNumber
     * for a manager viewing a direct report's record instead of their own.
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   attendance_status: ?string,
     *   time_in: ?string,
     *   time_out: ?string,
     *   profile_time_in: ?string,
     *   profile_time_out: ?string,
     *   worked_hours: ?float,
     *   difference: ?float,
     *   punch_latitude: ?float,
     *   punch_longitude: ?float,
     *   raw: array,
     * }
     */
    public function getAttendanceRecord(string $email, string $token, string $punchDate, ?string $teamWorkerPersonnelNumber = null, ?string $lang = null): array
    {
        $contract = [
            'language' => $lang ?? config('services.dynamics365.default_lang'),
            'Email' => $email,
            'Token' => $token,
            'PunchDate' => $punchDate,
        ];

        if ($teamWorkerPersonnelNumber) {
            $contract['TeamWorkerPersonnelNumber'] = $teamWorkerPersonnelNumber;
        }

        $result = $this->callService('INDXNaqiEssActionAttendanceSvc', 'getWorkerAttendanceRecord', ['_contract' => $contract]);

        if (! $result['success']) {
            return $this->attendanceRecordFailure($result['error'], $result['body']);
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return $this->attendanceRecordFailure($body['Error'] ?? 'Request rejected by Dynamics 365.', $body);
        }

        $data = $body['Data'] ?? [];

        return [
            'success' => true,
            'error' => null,
            'attendance_status' => $data['AttendanceStatus'] ?? null,
            'time_in' => $data['TimeIn'] ?? null,
            'time_out' => $data['TimeOut'] ?? null,
            'profile_time_in' => $data['ProfileTimeIn'] ?? null,
            'profile_time_out' => $data['ProfileTimeOut'] ?? null,
            'worked_hours' => isset($data['WorkedHrs']) ? round((float) $data['WorkedHrs'], 2) : null,
            'difference' => isset($data['Difference']) ? round((float) $data['Difference'], 2) : null,
            'punch_latitude' => isset($data['Punchlatitude']) ? (float) $data['Punchlatitude'] : null,
            'punch_longitude' => isset($data['Punchlongitude']) ? (float) $data['Punchlongitude'] : null,
            'raw' => $body,
        ];
    }

    protected function attendanceRecordFailure(?string $error, array $raw): array
    {
        return [
            'success' => false,
            'error' => $error ?: 'Request failed.',
            'attendance_status' => null,
            'time_in' => null,
            'time_out' => null,
            'profile_time_in' => null,
            'profile_time_out' => null,
            'worked_hours' => null,
            'difference' => null,
            'punch_latitude' => null,
            'punch_longitude' => null,
            'raw' => $raw,
        ];
    }

    /**
     * Fetch the employee home-dashboard aggregate (shift times, clock
     * in/out status, task/approval counters, leave balances, announcements,
     * upcoming events) via INDXNaqiEssHomePageSvc/getHomePageData.
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   name: ?string,
     *   gender: ?string,
     *   shift_start_time: ?string,
     *   shift_end_time: ?string,
     *   can_clock_in: bool,
     *   can_clock_out: bool,
     *   clock_in_time: ?string,
     *   clock_out_time: ?string,
     *   worker_tasks_counter: int,
     *   team_approval_counter: int,
     *   sick_leave: array,
     *   annual_leave: array,
     *   worker_off_today: ?array,
     *   worker_remotely_today: ?array,
     *   company_announcements: array,
     *   company_upcoming_events: array,
     *   raw: array,
     * }
     */
    public function getHomePageData(string $email, string $token, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssHomePageSvc', 'getHomePageData', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
            ],
        ]);

        if (! $result['success']) {
            return $this->homePageFailure($result['error'], $result['body']);
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return $this->homePageFailure($body['Error'] ?? 'Request rejected by Dynamics 365.', $body);
        }

        $data = $body['Data'] ?? [];

        $mapLeave = fn(array $l) => [
            'leave_type' => $l['LeaveType'] ?? '',
            'rate' => isset($l['Rate']) ? round((float) $l['Rate'], 2) : null,
            'used_balance' => isset($l['UsedBalance']) ? round((float) $l['UsedBalance'], 2) : null,
            'carried_over_balance' => isset($l['CarriedOverBalance']) ? round((float) $l['CarriedOverBalance'], 2) : null,
            'available_balance' => isset($l['AvailableBalance']) ? round((float) $l['AvailableBalance'], 2) : null,
        ];

        return [
            'success' => true,
            'error' => null,
            'name' => $data['Name'] ?? null,
            'gender' => $data['Gender'] ?? null,
            'shift_start_time' => $data['ShiftStartTime'] ?? null,
            'shift_end_time' => $data['ShiftEndTime'] ?? null,
            'can_clock_in' => (bool) ($data['CanClockIn'] ?? false),
            'can_clock_out' => (bool) ($data['CanClockOut'] ?? false),
            'clock_in_time' => $data['ClockInTime'] ?: null,
            'clock_out_time' => $data['ClockOutTime'] ?: null,
            'worker_tasks_counter' => (int) ($data['WorkerTasksCounter'] ?? 0),
            'team_approval_counter' => (int) ($data['TeamApprovalCounter'] ?? 0),
            'sick_leave' => array_map($mapLeave, $data['SickLeave'] ?? []),
            'annual_leave' => array_map($mapLeave, $data['AnnualLeave'] ?? []),
            'worker_off_today' => $data['WorkerOffTodayContract'] ?? null,
            'worker_remotely_today' => $data['WorkerRemotelyTodayContract'] ?? null,
            'company_announcements' => $data['CompanyAnnouncementContract'] ?? [],
            'company_upcoming_events' => $data['CompanyUpcomingEventsContract'] ?? [],
            'raw' => $body,
        ];
    }

    protected function homePageFailure(?string $error, array $raw): array
    {
        return [
            'success' => false,
            'error' => $error ?: 'Request failed.',
            'name' => null,
            'gender' => null,
            'shift_start_time' => null,
            'shift_end_time' => null,
            'can_clock_in' => false,
            'can_clock_out' => false,
            'clock_in_time' => null,
            'clock_out_time' => null,
            'worker_tasks_counter' => 0,
            'team_approval_counter' => 0,
            'sick_leave' => [],
            'annual_leave' => [],
            'worker_off_today' => null,
            'worker_remotely_today' => null,
            'company_announcements' => [],
            'company_upcoming_events' => [],
            'raw' => $raw,
        ];
    }

    /**
     * Fetch every pending/past request the employee has submitted, via
     * INDXNaqiEssActionAllRequestSvc/getAllRequests — and flatten Dynamics'
     * 14 separate per-type arrays (WorkerVacationRequestContract,
     * WorkerLoanRequestContract, WorkerOverTimeContract, etc.) into ONE
     * unified list, each item tagged with its category, rather than
     * returning the type-segregated shape Dynamics gives back.
     *
     * @return array{success:bool, error:?string, requests:array, raw:array}
     */
    public function getAllRequests(string $email, string $token, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionAllRequestSvc', 'getAllRequests', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'requests' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'requests' => [], 'raw' => $body];
        }

        $data = $body['Data'] ?? [];

        // Every "WorkerXxxContract" array in Data represents one request
        // category. Map each to a short, stable slug for the flattened list.
        $categories = [
            'WorkerExpenseClaimRequestContract' => 'expense_claim',
            'WorkerLetterRequestContract' => 'letter',
            'WorkerBankRequestContract' => 'bank',
            'WorkerAssetRequestContract' => 'asset',
            'WorkerAssetClearanceRequestContract' => 'asset_clearance',
            'WorkerLoanRequestContract' => 'loan',
            'WorkerOverTimeContract' => 'overtime',
            'WorkerWorkingRemotelyContract' => 'working_remotely',
            'WorkerMissingPunchContract' => 'missing_punch',
            'WorkerVacationRequestContract' => 'vacation',
            'WorkerOffboardContract' => 'offboard',
            'WorkerLeaveRequestContract' => 'leave',
            'WorkerBusinessTripContract' => 'business_trip',
            'WorkerVisaReEntryContract' => 'visa_re_entry',
        ];

        $requests = [];

        foreach ($categories as $contractKey => $category) {
            foreach ($data[$contractKey] ?? [] as $item) {
                $requests[] = [
                    'category' => $category,
                    'request_id' => $item['RequestId'] ?? null,
                    'request_type' => $item['RequestType'] ?? null,
                    'status' => $item['Status'] ?? null,
                    'creation_date' => $item['CreationDate'] ?? null,
                    'period' => $item['Period'] ?? null,
                    // Type-specific fields (VacationType/VacationFromDate, etc.)
                    // are preserved here rather than flattened further, since
                    // each category has a different shape.
                    'details' => $item,
                ];
            }
        }

        // Most recent first.
        usort($requests, fn($a, $b) => strcmp((string) $b['creation_date'], (string) $a['creation_date']));

        return ['success' => true, 'error' => null, 'requests' => $requests, 'raw' => $body];
    }

    /**
     * Fetch full details of a single request via
     * INDXNaqiEssActionAllRequestSvc/getWorkerRequestDetail.
     *
     * $requestType must be the RequestTypeId value (e.g. "VecationReq" —
     * that's Dynamics' own spelling, not a typo introduced here), not the
     * human-readable RequestType label. Both $requestType and $workerRecId
     * come from the `details` array of an item already returned by
     * getAllRequests() (as RequestTypeId / WorkerRecId respectively).
     *
     * No sample response shape was available for this endpoint, so — unlike
     * getAllRequests()/getHomePageData() — this deliberately does NOT rename
     * or restructure fields; it passes back whatever Dynamics puts in `Data`
     * as-is under `details`, to avoid guessing wrong at field names.
     *
     * @return array{success:bool, error:?string, details:array, raw:array}
     */
    public function getRequestDetail(string $email, string $token, string $requestId, string $requestType, string $workerRecId, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionAllRequestSvc', 'getWorkerRequestDetail', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
                'RequestId' => $requestId,
                'RequestType' => $requestType,
                'WorkerRecId' => $workerRecId,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'details' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'details' => [], 'raw' => $body];
        }

        return ['success' => true, 'error' => null, 'details' => $body['Data'] ?? [], 'raw' => $body];
    }

    /**
     * Look up the company worker directory filtered by starting letter, via
     * INDXNaqiEssActionDirectorySvc/getWorkersDirectory.
     *
     * No sample response was available for this endpoint, so — same
     * approach as getRequestDetail() — this does NOT rename or restructure
     * fields; it passes back whatever Dynamics puts in `Data` as-is under
     * `directory`, to avoid guessing wrong at field names. Once a real
     * response is available this should get the same normalization
     * treatment as getAllRequests()/getHomePageData().
     *
     * @return array{success:bool, error:?string, directory:mixed, raw:array}
     */
    /**
     * Look up the company worker directory filtered by starting letter, via
     * INDXNaqiEssActionDirectorySvc/getWorkersDirectory. Each entry is
     * normalized to one clean row (name/position/phone/email), same
     * pattern as getTeamMembers().
     *
     * Dynamics may return the list directly as `Data`, or wrap it under a
     * named key (e.g. a "...Details" contract array) — this handles either
     * shape by locating whichever value is actually a list of records,
     * rather than assuming one fixed wrapper key.
     *
     * @return array{success:bool, error:?string, directory:array<int, array{name:string, position:string, phone:string, email:string}>, raw:array}
     */
    public function getWorkersDirectory(string $email, string $token, string $letter, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionDirectorySvc', 'getWorkersDirectory', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
                'Letter' => $letter,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'directory' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'directory' => [], 'raw' => $body];
        }

        $data = $body['Data'] ?? [];
        $items = $this->extractListFromData($data);

        $directory = array_map(fn(array $w) => [
            'name' => $w['WorkerName'] ?? '',
            'position' => $w['WorkerPosition'] ?? '',
            'phone' => $w['WorkerPhone'] ?? '',
            'email' => $w['WorkerEmail'] ?? '',
        ], $items);

        return ['success' => true, 'error' => null, 'directory' => $directory, 'raw' => $body];
    }

    /**
     * Given a Dynamics `Data` payload, find the actual list of records —
     * whether `Data` itself is that list, or the list is wrapped under a
     * named key inside it.
     */
    protected function extractListFromData(mixed $data): array
    {
        if (is_array($data) && array_is_list($data)) {
            return $data;
        }

        foreach ((array) $data as $value) {
            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        return [];
    }

    /**
     * Look up the available vacation/leave types, via
     * INDXNaqiEssActionTimeOffRequestSvc/getWorkerVacationTypeLookUp.
     *
     * No sample response was available for this endpoint, so this returns
     * whatever list Dynamics gives back as-is (same approach as
     * getWorkersDirectory() before its real shape was confirmed) — no
     * field renaming yet. Once a real response is available, normalize
     * this the same way getTeamMembers()/getWorkersDirectory() are.
     *
     * @return array{success:bool, error:?string, vacation_types:array, raw:array}
     */
    public function getWorkerVacationTypeLookup(string $email, string $token, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionTimeOffRequestSvc', 'getWorkerVacationTypeLookUp', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'vacation_types' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'vacation_types' => [], 'raw' => $body];
        }

        $items = $this->extractListFromData($body['Data'] ?? []);

        return ['success' => true, 'error' => null, 'vacation_types' => $items, 'raw' => $body];
    }

    /**
     * Base authenticated HTTP client pointed at the Web API root.
     */
    /**
     * Submit a new vacation/leave request, via
     * INDXNaqiEssActionTimeOffRequestSvc/createVacation.
     *
     * $vacationTypeId must be a leave type ID obtained from
     * getWorkerVacationTypeLookup() first — Dynamics expects the type's ID,
     * not a free-text label. $fromDate/$toDate are plain 'Y-m-d' dates; this
     * formats them into the 'Y-m-d\TH:i:s.000' shape Dynamics' contract
     * expects (matching the exact format seen in the request sample).
     *
     * No sample *response* was available for this endpoint, so on success
     * this passes back whatever Dynamics returns in `Data` as-is under
     * `details`, same approach as the other endpoints built without a
     * confirmed response shape.
     *
     * @return array{success:bool, error:?string, details:array, raw:array}
     */
    public function createVacation(
        string $email,
        string $token,
        string $vacationTypeId,
        string $fromDate,
        string $toDate,
        string $reason = '',
        array $files = [],
        ?string $lang = null,
    ): array {
        $result = $this->callService('INDXNaqiEssActionTimeOffRequestSvc', 'createVacation', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
                'VacationType' => $vacationTypeId,
                'VacationFromDate' => $this->toDynamicsDateTime($fromDate),
                'VacationToDate' => $this->toDynamicsDateTime($toDate),
                'VacationReason' => $reason,
                'Files' => $files,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'details' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'details' => [], 'raw' => $body];
        }

        return ['success' => true, 'error' => null, 'details' => $body['Data'] ?? [], 'raw' => $body];
    }

    /** 'Y-m-d' -> 'Y-m-d\TH:i:s.000', matching the exact format Dynamics' time-off contracts expect. */
    protected function toDynamicsDateTime(string $date): string
    {
        return \Illuminate\Support\Carbon::parse($date)->format('Y-m-d\TH:i:s.v');
    }

    /**
     * Cancel an existing vacation/leave request, via
     * INDXNaqiEssActionTimeOffRequestSvc/cancelWorkerVacationRequest.
     * $requestId is the RequestId of an existing request (e.g. from
     * getAllRequests() or the response of createVacation()).
     *
     * No sample response was available for this endpoint either, so
     * (same as createVacation) whatever comes back in `Data` on success is
     * passed through as-is under `details`.
     *
     * @return array{success:bool, error:?string, details:array, raw:array}
     */
    public function cancelVacation(string $email, string $token, string $requestId, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionTimeOffRequestSvc', 'cancelWorkerVacationRequest', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
                'RequestId' => $requestId,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'details' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'details' => [], 'raw' => $body];
        }

        return ['success' => true, 'error' => null, 'details' => $body['Data'] ?? [], 'raw' => $body];
    }

    /**
     * Look up available excuse types (short-leave/permission types, e.g.
     * "late arrival", "early departure"), via
     * INDXNaqiEssActionExecuseRequestSvc/getWorkerLeaveTypeLookUp — "Execuse"
     * is Dynamics' own spelling of the service name, not a typo introduced
     * here.
     *
     * No sample response was available for this endpoint, so — same as
     * getWorkerVacationTypeLookup() before its shape was confirmed — this
     * returns whatever list Dynamics gives back as-is, no field renaming yet.
     *
     * @return array{success:bool, error:?string, excuse_types:array, raw:array}
     */
    public function getWorkerExcuseTypeLookup(string $email, string $token, ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssActionExecuseRequestSvc', 'getWorkerLeaveTypeLookUp', [
            '_contract' => [
                'language' => $lang ?? config('services.dynamics365.default_lang'),
                'Email' => $email,
                'Token' => $token,
            ],
        ]);

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'], 'excuse_types' => [], 'raw' => $result['body']];
        }

        $body = $result['body'];

        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return ['success' => false, 'error' => $body['Error'] ?? 'Request rejected by Dynamics 365.', 'excuse_types' => [], 'raw' => $body];
        }

        $items = $this->extractListFromData($body['Data'] ?? []);

        return ['success' => true, 'error' => null, 'excuse_types' => $items, 'raw' => $body];
    }

    protected function client()
    {
        return Http::withToken($this->getAccessToken())
            ->baseUrl("{$this->resource}/api/data/{$this->apiVersion}")
            ->timeout(config('services.dynamics365.timeout'))
            ->retry(config('services.dynamics365.retry_times'), config('services.dynamics365.retry_sleep_ms'))
            ->withHeaders([
                'Accept' => 'application/json',
                'OData-MaxVersion' => '4.0',
                'OData-Version' => '4.0',
                'Content-Type' => 'application/json; charset=utf-8',
                'Prefer' => 'return=representation',
            ]);
    }

    /** Retrieve a collection with optional OData query params ($filter, $select, $expand...) */
    public function get(string $entitySet, array $query = []): array
    {
        $response = $this->client()->get($entitySet, $query);
        $this->throwIfFailed($response, "GET {$entitySet}");

        return $response->json();
    }

    /** Retrieve a single record by its GUID */
    public function find(string $entitySet, string $id, array $select = []): array
    {
        $query = $select ? ['$select' => implode(',', $select)] : [];
        $response = $this->client()->get("{$entitySet}({$id})", $query);
        $this->throwIfFailed($response, "GET {$entitySet}({$id})");

        return $response->json();
    }

    /** Create a record, returns the new record's GUID */
    public function create(string $entitySet, array $payload): string
    {
        $response = $this->client()->post($entitySet, $payload);
        $this->throwIfFailed($response, "POST {$entitySet}");

        $entityUri = $response->header('OData-EntityId') ?? '';
        preg_match('/\(([0-9a-fA-F-]{36})\)/', $entityUri, $matches);

        return $matches[1] ?? $response->json('id') ?? '';
    }

    /** Update an existing record (partial patch) */
    public function update(string $entitySet, string $id, array $payload): void
    {
        $response = $this->client()->patch("{$entitySet}({$id})", $payload);
        $this->throwIfFailed($response, "PATCH {$entitySet}({$id})");
    }

    /** Delete a record */
    public function delete(string $entitySet, string $id): void
    {
        $response = $this->client()->delete("{$entitySet}({$id})");
        $this->throwIfFailed($response, "DELETE {$entitySet}({$id})");
    }

    protected function throwIfFailed($response, string $context): void
    {
        if ($response->failed()) {
            Log::error("Dynamics365: {$context} failed", ['body' => $response->body()]);
            throw new RuntimeException("Dynamics 365 request failed ({$context}): " . $response->body());
        }
    }

    /*
    |--------------------------------------------------------------------
    | Naqi ESS domain helpers
    |--------------------------------------------------------------------
    | High level, ESS-specific wrappers built on top of the generic
    | client above. Adjust entity/field names to match your org's schema.
    */

    /** Push a local employee record into Dynamics as a contact and store the GUID */
    public function syncEmployee(User $user): string
    {
        $entitySet = config('services.dynamics365.entities.employees');

        $payload = [
            'fullname' => $user->username,
            'emailaddress1' => $user->email,
            'mobilephone' => $user->phone,
            'naqi_personnelnumber' => $user->personnel_number,
        ];

        if ($user->dynamics_id) {
            $this->update($entitySet, $user->dynamics_id, $payload);
            $id = $user->dynamics_id;
        } else {
            $id = $this->create($entitySet, $payload);
        }

        $user->update(['dynamics_id' => $id, 'dynamics_synced_at' => now()]);

        return $id;
    }

    /** Pull an employee's profile from Dynamics */
    public function getEmployee(string $dynamicsId): array
    {
        return $this->find(config('services.dynamics365.entities.employees'), $dynamicsId);
    }

    /** Submit a leave request to Dynamics on behalf of a user */
    public function submitLeaveRequest(User $user, array $data): string
    {
        $entitySet = config('services.dynamics365.entities.leave_requests');

        return $this->create($entitySet, array_merge($data, [
            'naqi_employeeid@odata.bind' => '/' . config('services.dynamics365.entities.employees') . "({$user->dynamics_id})",
        ]));
    }

    /** Fetch attendance logs for an employee within a date range */
    public function getAttendance(User $user, string $from, string $to): array
    {
        $entitySet = config('services.dynamics365.entities.attendance');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id} and naqi_date ge {$from} and naqi_date le {$to}",
        ]);
    }

    /** Fetch payslips for an employee */
    public function getPayslips(User $user): array
    {
        $entitySet = config('services.dynamics365.entities.payslips');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id}",
            '$orderby' => 'naqi_period desc',
        ]);
    }
}
