<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dynamics365Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class DynamicsWorkspaceController extends Controller
{
    protected const SESSION_KEY = 'dynamics_workspace';

    public function __construct()
    {
        $this->middleware('permission:dynamics.sync');
    }

    public function index(Dynamics365Service $dynamics): View
    {
        $context = session(self::SESSION_KEY);

        if (! $context) {
            return view('dynamics.workspace', ['step' => 'login']);
        }

        $teamResult = $dynamics->getTeamMembers(
            email: $context['email'],
            token: $context['token'],
        );

        // If the session token itself has gone stale, send the admin back to
        // the login step with a clear reason rather than a confusing crash.
        if (! $teamResult['success']) {
            session()->forget(self::SESSION_KEY);

            return view('dynamics.workspace', [
                'step' => 'login',
                'sessionExpiredError' => $teamResult['error'],
            ]);
        }

        $selectedMember = $context['selected_member'] ?? null;
        $calendarResult = null;
        $month = $context['month'] ?? now()->month;
        $year = $context['year'] ?? now()->year;

        if ($selectedMember && ! empty($context['month']) && ! empty($context['year'])) {
            $calendarResult = $dynamics->getAttendanceCalendar(
                email: $context['email'],
                token: $context['token'],
                month: $month,
                year: $year,
                teamWorkerPersonnelNumber: $selectedMember['personnel_number'],
            );
        }

        return view('dynamics.workspace', [
            'step' => $selectedMember ? 'calendar' : 'team',
            'context' => $context,
            'teamResult' => $teamResult,
            'selectedMember' => $selectedMember,
            'calendarResult' => $calendarResult,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function login(Request $request, Dynamics365Service $dynamics): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $dynamics->loginUser(email: $request->email, password: $request->password);

        if (! $result['success']) {
            return back()->withErrors(['email' => $result['error']])->withInput($request->only('email'));
        }

        session([self::SESSION_KEY => [
            'email' => $request->email,
            'token' => $result['token'],
            'worker' => $result['worker'],
            'is_manager' => $result['is_manager'],
        ]]);

        return redirect()->route('admin.dynamics.workspace.index');
    }

    public function selectMember(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['nullable', 'string'],
            'position' => ['nullable', 'string'],
            'personnel_number' => ['required', 'string'],
        ]);

        $context = session(self::SESSION_KEY);
        abort_unless($context, 440);

        $context['selected_member'] = [
            'name' => $request->name,
            'position' => $request->position,
            'personnel_number' => $request->personnel_number,
        ];
        unset($context['month'], $context['year']);

        session([self::SESSION_KEY => $context]);

        return redirect()->route('admin.dynamics.workspace.index');
    }

    public function backToTeam(): RedirectResponse
    {
        $context = session(self::SESSION_KEY);
        abort_unless($context, 440);

        unset($context['selected_member'], $context['month'], $context['year']);
        session([self::SESSION_KEY => $context]);

        return redirect()->route('admin.dynamics.workspace.index');
    }

    public function calendar(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
        ]);

        $context = session(self::SESSION_KEY);
        abort_unless($context && ! empty($context['selected_member']), 440);

        $context['month'] = $request->integer('month');
        $context['year'] = $request->integer('year');
        session([self::SESSION_KEY => $context]);

        return redirect()->route('admin.dynamics.workspace.index');
    }

    /** AJAX: called when a day is clicked on the rendered calendar. */
    public function day(Request $request, Dynamics365Service $dynamics): JsonResponse
    {
        $request->validate(['punch_date' => ['required', 'date_format:Y-m-d']]);

        $context = session(self::SESSION_KEY);

        if (! $context || empty($context['selected_member'])) {
            return response()->json(['success' => false, 'message' => 'Session expired — reload the page.'], 440);
        }

        $result = $dynamics->getAttendanceRecord(
            email: $context['email'],
            token: $context['token'],
            punchDate: $request->punch_date,
            teamWorkerPersonnelNumber: $context['selected_member']['personnel_number'],
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 401);
        }

        unset($result['success'], $result['error'], $result['raw']);

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function logout(): RedirectResponse
    {
        session()->forget(self::SESSION_KEY);

        return redirect()->route('admin.dynamics.workspace.index');
    }
}
