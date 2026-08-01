<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
