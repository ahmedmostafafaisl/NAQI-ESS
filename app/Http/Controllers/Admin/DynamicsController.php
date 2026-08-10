<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dynamics365Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DynamicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dynamics.sync');
    }

    public function index(): View
    {
        return view('dynamics.test');
    }

    public function testConnection(Dynamics365Service $dynamics): View
    {
        $result = $dynamics->testConnection();

        return view('dynamics.test', compact('result'));
    }

    public function testUserLogin(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'test_email' => ['required', 'email'],
            'test_password' => ['required', 'string'],
            'test_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
            'test_device_token' => ['nullable', 'string'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->test_email,
            password: $request->test_password,
            deviceToken: $request->test_device_token ?? '',
            lang: $request->test_lang,
        );

        return view('dynamics.test', compact('loginResult'));
    }

    public function testTeamMembers(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'team_email' => ['required', 'email'],
            'team_token' => ['required', 'string'],
            'team_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $teamResult = $dynamics->getTeamMembers(
            email: $request->team_email,
            token: $request->team_token,
            lang: $request->team_lang,
        );

        return view('dynamics.test', compact('teamResult'));
    }

    public function testHomePage(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'home_email' => ['required', 'email'],
            'home_password' => ['required', 'string'],
            'home_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->home_email,
            password: $request->home_password,
            lang: $request->home_lang,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['homeResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $homeResult = $dynamics->getHomePageData(
            email: $request->home_email,
            token: $loginResult['token'],
            lang: $request->home_lang,
        );

        return view('dynamics.test', compact('homeResult'));
    }

    public function testAllRequests(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'requests_email' => ['required', 'email'],
            'requests_password' => ['required', 'string'],
            'requests_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->requests_email,
            password: $request->requests_password,
            lang: $request->requests_lang,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['requestsResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        // Stashed so the "view detail" click below can call Dynamics again
        // without asking for the password a second time.
        session(['dynamics_requests_context' => [
            'email' => $request->requests_email,
            'token' => $loginResult['token'],
            'lang' => $request->requests_lang,
        ]]);

        $requestsResult = $dynamics->getAllRequests(
            email: $request->requests_email,
            token: $loginResult['token'],
            lang: $request->requests_lang,
        );

        return view('dynamics.test', compact('requestsResult'));
    }

    /**
     * AJAX: called when a row is clicked in the All Requests results table.
     * Reuses the email/token stashed in session by testAllRequests() above.
     */
    public function requestDetail(Request $request, Dynamics365Service $dynamics): JsonResponse
    {
        $request->validate([
            'request_id' => ['required', 'string'],
            'request_type' => ['required', 'string'],
            'worker_rec_id' => ['required', 'string'],
        ]);

        $context = session('dynamics_requests_context');

        if (! $context) {
            return response()->json(['success' => false, 'message' => __('admin.dynamics.session_expired_reload')], 440);
        }

        $result = $dynamics->getRequestDetail(
            email: $context['email'],
            token: $context['token'],
            requestId: $request->request_id,
            requestType: $request->request_type,
            workerRecId: $request->worker_rec_id,
            lang: $context['lang'] ?? null,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 401);
        }

        return response()->json(['success' => true, 'data' => $result['details']]);
    }
}
