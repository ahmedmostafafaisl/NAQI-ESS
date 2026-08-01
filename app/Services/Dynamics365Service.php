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
        $this->resource = rtrim(config('dynamics365.resource'), '/');
        $this->apiVersion = config('dynamics365.api_version');
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
    public function getAccessToken(): string
    {
        $cacheKey = config('dynamics365.token_cache_key');

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

        Cache::put(config('dynamics365.token_cache_key'), $result['access_token'], $result['ttl_seconds']);

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
    protected function requestAccessToken(): array
    {
        $tenantId = config('dynamics365.tenant_id');

        try {
            $response = Http::asForm()
                ->timeout(config('dynamics365.timeout'))
                ->post("https://login.microsoftonline.com/{$tenantId}/oauth2/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('dynamics365.client_id'),
                    'client_secret' => config('dynamics365.client_secret'),
                    'resource' => $this->resource,
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
        $buffer = (int) config('dynamics365.token_expiry_buffer', 60);
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
    public function callService(string $service, string $operation, array $payload = [], ?string $serviceGroup = null): array
    {
        $serviceGroup ??= config('dynamics365.service_group');
        $path = "/api/services/{$serviceGroup}/{$service}/{$operation}";

        try {
            $response = Http::withToken($this->getAccessToken())
                ->timeout(config('dynamics365.timeout'))
                ->retry(config('dynamics365.retry_times'), config('dynamics365.retry_sleep_ms'))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->resource}{$path}", $payload);
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
    public function loginUser(string $email, string $password, string $deviceToken = '', ?string $lang = null): array
    {
        $result = $this->callService('INDXNaqiEssAuthSvc', 'Login', [
            '_contract' => [
                'lang' => $lang ?? config('dynamics365.default_lang'),
                'Email' => $email,
                'Password' => $password,
                'DeviceToken' => $deviceToken,
            ],
        ]);

        if (! $result['success']) {
            return $this->loginFailure($result['error'], $result['body']);
        }

        $body = $result['body'];

        // Transport succeeded (HTTP 200) but Dynamics itself may still report
        // a logical failure (e.g. wrong password) via Status/Error/Code.
        if (empty($body['Status']) || ! empty($body['Error']) || (int) ($body['Code'] ?? 0) !== 200) {
            return $this->loginFailure($body['Error'] ?? 'Login rejected by Dynamics 365.', $body);
        }

        $data = $body['Data'] ?? [];

        return [
            'success' => true,
            'error' => null,
            'code' => $body['Code'] ?? 200,
            'token' => $data['Token'] ?? null,
            'worker' => $data['Worker'] ?? null,
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
                'language' => $lang ?? config('dynamics365.default_lang'),
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
            'language' => $lang ?? config('dynamics365.default_lang'),
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
            'language' => $lang ?? config('dynamics365.default_lang'),
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
     * Base authenticated HTTP client pointed at the Web API root.
     */
    protected function client()
    {
        return Http::withToken($this->getAccessToken())
            ->baseUrl("{$this->resource}/api/data/{$this->apiVersion}")
            ->timeout(config('dynamics365.timeout'))
            ->retry(config('dynamics365.retry_times'), config('dynamics365.retry_sleep_ms'))
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
        $entitySet = config('dynamics365.entities.employees');

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
        return $this->find(config('dynamics365.entities.employees'), $dynamicsId);
    }

    /** Submit a leave request to Dynamics on behalf of a user */
    public function submitLeaveRequest(User $user, array $data): string
    {
        $entitySet = config('dynamics365.entities.leave_requests');

        return $this->create($entitySet, array_merge($data, [
            'naqi_employeeid@odata.bind' => '/' . config('dynamics365.entities.employees') . "({$user->dynamics_id})",
        ]));
    }

    /** Fetch attendance logs for an employee within a date range */
    public function getAttendance(User $user, string $from, string $to): array
    {
        $entitySet = config('dynamics365.entities.attendance');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id} and naqi_date ge {$from} and naqi_date le {$to}",
        ]);
    }

    /** Fetch payslips for an employee */
    public function getPayslips(User $user): array
    {
        $entitySet = config('dynamics365.entities.payslips');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id}",
            '$orderby' => 'naqi_period desc',
        ]);
    }
}
