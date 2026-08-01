<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dynamics365Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DynamicsAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dynamics.sync');
    }

    public function index(): View
    {
        return view('dynamics.attendance');
    }

    /**
     * Fetch the month calendar and stash the email/token/lang/team-worker in
     * session so the day-click AJAX call below doesn't need to resend them —
     * this is a test tool behind an admin permission, not a production auth
     * mechanism, so a short-lived session value is fine here.
     */
    public function calendar(Request $request, Dynamics365Service $dynamics): View|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'team_worker_personnel_number' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $result = $dynamics->getAttendanceCalendar(
            email: $request->email,
            token: $request->token,
            month: $request->integer('month'),
            year: $request->integer('year'),
            teamWorkerPersonnelNumber: $request->team_worker_personnel_number,
            lang: $request->lang,
        );

        if ($result['success']) {
            session([
                'dynamics_attendance_context' => [
                    'email' => $request->email,
                    'token' => $request->token,
                    'team_worker_personnel_number' => $request->team_worker_personnel_number,
                    'lang' => $request->lang,
                ],
            ]);
        }

        return view('dynamics.attendance', [
            'calendarResult' => $result,
            'month' => $request->integer('month'),
            'year' => $request->integer('year'),
        ]);
    }

    /**
     * AJAX endpoint called when a day is clicked on the rendered calendar.
     * Pulls email/token/lang/team-worker from the session context set by
     * calendar() above, so the click only needs to send the date.
     */
    public function day(Request $request, Dynamics365Service $dynamics): JsonResponse
    {
        $request->validate([
            'punch_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $context = session('dynamics_attendance_context');

        if (! $context) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired — fetch the calendar again first.',
            ], 440);
        }

        $result = $dynamics->getAttendanceRecord(
            email: $context['email'],
            token: $context['token'],
            punchDate: $request->punch_date,
            teamWorkerPersonnelNumber: $context['team_worker_personnel_number'] ?? null,
            lang: $context['lang'] ?? null,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 401);
        }

        unset($result['success'], $result['error'], $result['raw']);

        return response()->json(['success' => true, 'data' => $result]);
    }
}
