<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dynamics\AllRequestsRequest;
use App\Http\Requests\Dynamics\AttendanceCalendarRequest;
use App\Http\Requests\Dynamics\AttendanceRecordRequest;
use App\Http\Requests\Dynamics\HomePageRequest;
use App\Http\Requests\Dynamics\TeamMembersRequest;
use App\Http\Requests\Dynamics\WorkersDirectoryRequest;
use App\Http\Resources\AttendanceCalendarResource;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Resources\HomePageResource;
use App\Http\Resources\RequestItemResource;
use App\Http\Resources\TeamMembersResource;
use App\Http\Resources\WorkerDirectoryEntryResource;
use App\Models\DynamicsUser;
use App\Services\Dynamics365Service;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pass-through endpoints for Dynamics 365 F&O custom services that operate
 * on a specific employee's session (identified by the `token` returned from
 * POST /api/v1/auth/dynamics-login). Same pattern as dynamicsLogin() in
 * AuthController: nothing here is persisted to our local database — the
 * client is responsible for holding onto its own email/token and passing
 * them on each call.
 *
 * ARCHITECTURE NOTE: the first 6 methods here (teamMembers through
 * workersDirectory) use dedicated Request/Resource classes. The remaining 5
 * (requestDetail, vacationTypes, createVacation, cancelVacation,
 * excuseTypes) are deliberately NOT yet migrated — their Dynamics response
 * shapes are still unconfirmed (see Dynamics365Service), and building a
 * Resource around data whose shape isn't confirmed would just be
 * decorative. They'll get the same treatment once real responses are seen.
 */
class DynamicsController extends Controller
{
    public function __construct(protected Dynamics365Service $dynamics) {}

    public function teamMembers(TeamMembersRequest $request): JsonResponse
    {
        $result = $this->dynamics->getTeamMembers(
            email: $request->validated('email'),
            token: $request->validated('token'),
            lang: $request->validated('lang'),
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success(new TeamMembersResource($result));
    }

    public function attendanceCalendar(AttendanceCalendarRequest $request): JsonResponse
    {
        $result = $this->dynamics->getAttendanceCalendar(
            email: $request->validated('email'),
            token: $request->validated('token'),
            month: (int) $request->validated('month'),
            year: (int) $request->validated('year'),
            teamWorkerPersonnelNumber: $request->validated('team_worker_personnel_number'),
            lang: $request->validated('lang'),
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success(new AttendanceCalendarResource($result));
    }

    public function attendanceRecord(AttendanceRecordRequest $request): JsonResponse
    {
        $result = $this->dynamics->getAttendanceRecord(
            email: $request->validated('email'),
            token: $request->validated('token'),
            punchDate: $request->validated('punch_date'),
            teamWorkerPersonnelNumber: $request->validated('team_worker_personnel_number'),
            lang: $request->validated('lang'),
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success(new AttendanceRecordResource($result));
    }

    /**
     * Combined action: log in with email/password against Dynamics 365, then
     * immediately fetch that employee's home-dashboard data — the two things
     * a mobile app's home screen needs right after login, in one round trip
     * instead of two. Same update-or-create into dynamics_users as
     * AuthController::dynamicsLogin(), since this is still fundamentally a
     * login action.
     *
     * NOTE: the DynamicsUser::updateOrCreate() call below touches the same
     * model AuthController manages — that overlap is deliberately left as-is
     * for now and will be resolved when Auth gets migrated to this
     * architecture next, rather than half-fixing it from this side first.
     */
    public function homePage(HomePageRequest $request): JsonResponse
    {
        $loginResult = $this->dynamics->loginUser(
            email: $request->validated('email'),
            password: $request->validated('password'),
            deviceToken: $request->validated('device_token') ?? '',
            lang: $request->validated('lang'),
        );

        if (! $loginResult['success']) {
            return ApiResponse::error($loginResult['error'], 401);
        }

        $attributes = ['password' => $request->validated('password')];
        if ($request->filled('device_token')) {
            $attributes['device_token'] = $request->validated('device_token');
        }
        DynamicsUser::updateOrCreate(['email' => $request->validated('email')], $attributes);

        $homeResult = $this->dynamics->getHomePageData(
            email: $request->validated('email'),
            token: $loginResult['token'],
            lang: $request->validated('lang'),
        );

        if (! $homeResult['success']) {
            return ApiResponse::error($homeResult['error'], 401);
        }

        return ApiResponse::success(new HomePageResource([
            'token' => $loginResult['token'],
            'worker' => $loginResult['worker'],
            'is_manager' => $loginResult['is_manager'],
            ...$homeResult,
        ]));
    }

    /**
     * Fetch all of the employee's requests (vacation, loan, overtime,
     * expense claim, etc.) as ONE flat list rather than Dynamics' 14
     * separate per-type arrays. See Dynamics365Service::getAllRequests().
     */
    public function allRequests(AllRequestsRequest $request): JsonResponse
    {
        $result = $this->dynamics->getAllRequests(
            email: $request->validated('email'),
            token: $request->validated('token'),
            lang: $request->validated('lang'),
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success([
            'requests' => RequestItemResource::collection($result['requests']),
        ]);
    }

    /**
     * Look up the company worker directory filtered by starting letter
     * (e.g. "A"). Confirmed shape: array of {name, position, phone, email}.
     */
    public function workersDirectory(WorkersDirectoryRequest $request): JsonResponse
    {
        $result = $this->dynamics->getWorkersDirectory(
            email: $request->validated('email'),
            token: $request->validated('token'),
            letter: $request->validated('letter'),
            lang: $request->validated('lang'),
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success(WorkerDirectoryEntryResource::collection($result['directory']));
    }

    // -----------------------------------------------------------------
    // Deliberately not yet migrated — see class docblock.
    // -----------------------------------------------------------------

    /**
     * Fetch full details of a single request. $request_type must be the
     * RequestTypeId from a getAllRequests() item's `details` (e.g.
     * "VecationReq"), not the human-readable label.
     */
    public function requestDetail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'request_id' => ['required', 'string'],
            'request_type' => ['required', 'string'],
            'worker_rec_id' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getRequestDetail(
            email: $request->email,
            token: $request->token,
            requestId: $request->request_id,
            requestType: $request->request_type,
            workerRecId: $request->worker_rec_id,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success($result['details']);
    }

    /**
     * Look up available vacation/leave types. Response shape from Dynamics
     * wasn't available when this was built, so items are passed through as-is.
     */
    public function vacationTypes(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getWorkerVacationTypeLookup(
            email: $request->email,
            token: $request->token,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success($result['vacation_types']);
    }

    /**
     * Submit a new vacation/leave request. `vacation_type` must be a leave
     * type ID from the vacation-types lookup endpoint, not a free-text label.
     */
    public function createVacation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'vacation_type' => ['required', 'string'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'files' => ['sometimes', 'array'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->createVacation(
            email: $data['email'],
            token: $data['token'],
            vacationTypeId: $data['vacation_type'],
            fromDate: $data['from_date'],
            toDate: $data['to_date'],
            reason: $data['reason'] ?? '',
            files: $data['files'] ?? [],
            lang: $data['lang'] ?? null,
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 422);
        }

        return ApiResponse::success($result['details'], 'Vacation request submitted successfully.');
    }

    /** Cancel an existing vacation/leave request. */
    public function cancelVacation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'request_id' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->cancelVacation(
            email: $data['email'],
            token: $data['token'],
            requestId: $data['request_id'],
            lang: $data['lang'] ?? null,
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 422);
        }

        return ApiResponse::success($result['details'], 'Vacation request cancelled successfully.');
    }

    /**
     * Look up available excuse types. Response shape from Dynamics wasn't
     * available when this was built, so items are passed through as-is.
     */
    public function excuseTypes(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getWorkerExcuseTypeLookup(
            email: $request->email,
            token: $request->token,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return ApiResponse::error($result['error'], 401);
        }

        return ApiResponse::success($result['excuse_types']);
    }
}
