<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DynamicsUser;
use App\Services\Dynamics365Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pass-through endpoints for Dynamics 365 F&O custom services that operate
 * on a specific employee's session (identified by the `token` returned from
 * POST /api/v1/auth/dynamics-login). Same pattern as dynamicsLogin() in
 * AuthController: nothing here is persisted to our local database — the
 * client is responsible for holding onto its own email/token and passing
 * them on each call.
 */
class DynamicsController extends Controller
{
    public function __construct(protected Dynamics365Service $dynamics) {}

    public function teamMembers(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getTeamMembers(
            email: $request->email,
            token: $request->token,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'], 'data' => []], 401);
        }

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => [
                'is_manager' => $result['is_manager'],
                'team' => $result['team'],
                'managers' => $result['managers'],
            ],
        ]);
    }

    public function attendanceCalendar(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'team_worker_personnel_number' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getAttendanceCalendar(
            email: $request->email,
            token: $request->token,
            month: $request->integer('month'),
            year: $request->integer('year'),
            teamWorkerPersonnelNumber: $request->team_worker_personnel_number,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'], 'data' => []], 401);
        }

        return response()->json(['success' => true, 'message' => '', 'data' => ['days' => $result['days']]]);
    }

    public function attendanceRecord(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'punch_date' => ['required', 'date_format:Y-m-d'],
            'team_worker_personnel_number' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getAttendanceRecord(
            email: $request->email,
            token: $request->token,
            punchDate: $request->punch_date,
            teamWorkerPersonnelNumber: $request->team_worker_personnel_number,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'], 'data' => []], 401);
        }

        unset($result['success'], $result['error'], $result['raw']);

        return response()->json(['success' => true, 'message' => '', 'data' => $result]);
    }

    /**
     * Combined action: log in with email/password against Dynamics 365, then
     * immediately fetch that employee's home-dashboard data — the two things
     * a mobile app's home screen needs right after login, in one round trip
     * instead of two. Same update-or-create into dynamics_users as
     * AuthController::dynamicsLogin(), since this is still fundamentally a
     * login action.
     */
    public function homePage(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_token' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $this->dynamics->loginUser(
            email: $request->email,
            password: $request->password,
            deviceToken: $request->device_token ?? '',
            lang: $request->lang,
        );

        if (! $loginResult['success']) {
            return response()->json(['success' => false, 'message' => $loginResult['error'], 'data' => []], 401);
        }

        $attributes = ['password' => $request->password];
        if ($request->filled('device_token')) {
            $attributes['device_token'] = $request->device_token;
        }
        DynamicsUser::updateOrCreate(['email' => $request->email], $attributes);

        $homeResult = $this->dynamics->getHomePageData(
            email: $request->email,
            token: $loginResult['token'],
            lang: $request->lang,
        );

        if (! $homeResult['success']) {
            return response()->json(['success' => false, 'message' => $homeResult['error'], 'data' => []], 401);
        }

        unset($homeResult['success'], $homeResult['error'], $homeResult['raw']);

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => [
                'token' => $loginResult['token'],
                'worker' => $loginResult['worker'],
                'is_manager' => $loginResult['is_manager'],
                ...$homeResult,
            ],
        ]);
    }

    /**
     * Fetch all of the employee's requests (vacation, loan, overtime,
     * expense claim, etc.) as ONE flat list rather than Dynamics' 14
     * separate per-type arrays. See Dynamics365Service::getAllRequests().
     */
    public function allRequests(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $this->dynamics->getAllRequests(
            email: $request->email,
            token: $request->token,
            lang: $request->lang,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'], 'data' => []], 401);
        }

        return response()->json(['success' => true, 'message' => '', 'data' => ['requests' => $result['requests']]]);
    }

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
            return response()->json(['success' => false, 'message' => $result['error'], 'data' => []], 401);
        }

        return response()->json(['success' => true, 'message' => '', 'data' => $result['details']]);
    }
}
