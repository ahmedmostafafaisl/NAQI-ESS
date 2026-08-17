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

    public function testWorkersDirectory(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'directory_email' => ['required', 'email'],
            'directory_password' => ['required', 'string'],
            'directory_letter' => ['required', 'string', 'max:1'],
            'directory_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->directory_email,
            password: $request->directory_password,
            lang: $request->directory_lang,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['directoryResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $directoryResult = $dynamics->getWorkersDirectory(
            email: $request->directory_email,
            token: $loginResult['token'],
            letter: $request->directory_letter,
            lang: $request->directory_lang,
        );

        return view('dynamics.test', compact('directoryResult'));
    }

    public function testVacationTypes(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'vacation_types_email' => ['required', 'email'],
            'vacation_types_password' => ['required', 'string'],
            'vacation_types_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->vacation_types_email,
            password: $request->vacation_types_password,
            lang: $request->vacation_types_lang,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['vacationTypesResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $vacationTypesResult = $dynamics->getWorkerVacationTypeLookup(
            email: $request->vacation_types_email,
            token: $loginResult['token'],
            lang: $request->vacation_types_lang,
        );

        return view('dynamics.test', compact('vacationTypesResult'));
    }

    public function testCreateVacation(Request $request, Dynamics365Service $dynamics): View
    {
        $data = $request->validate([
            'cv_email' => ['required', 'email'],
            'cv_password' => ['required', 'string'],
            'cv_vacation_type' => ['required', 'string'],
            'cv_from_date' => ['required', 'date'],
            'cv_to_date' => ['required', 'date', 'after_or_equal:cv_from_date'],
            'cv_reason' => ['nullable', 'string', 'max:1000'],
            'cv_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $data['cv_email'],
            password: $data['cv_password'],
            lang: $data['cv_lang'] ?? null,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['createVacationResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $createVacationResult = $dynamics->createVacation(
            email: $data['cv_email'],
            token: $loginResult['token'],
            vacationTypeId: $data['cv_vacation_type'],
            fromDate: $data['cv_from_date'],
            toDate: $data['cv_to_date'],
            reason: $data['cv_reason'] ?? '',
            lang: $data['cv_lang'] ?? null,
        );

        return view('dynamics.test', compact('createVacationResult'));
    }

    /**
     * AJAX: logs in and fetches vacation types, for populating the dropdown
     * on the "Create vacation request" card. Returns the raw list — field
     * names for id/label are still unconfirmed (see
     * Dynamics365Service::getWorkerVacationTypeLookup), so the frontend
     * guesses which key is which. Once a real response is confirmed, this
     * can return a clean {id, label} shape instead and the JS heuristic
     * can be deleted.
     */
    public function vacationTypesLookupAjax(Request $request, Dynamics365Service $dynamics): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->email,
            password: $request->password,
            lang: $request->lang,
        );

        if (! $loginResult['success']) {
            return response()->json(['success' => false, 'message' => $loginResult['error']], 401);
        }

        $result = $dynamics->getWorkerVacationTypeLookup(
            email: $request->email,
            token: $loginResult['token'],
            lang: $request->lang,
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 401);
        }

        return response()->json(['success' => true, 'data' => $result['vacation_types']]);
    }

    public function testCancelVacation(Request $request, Dynamics365Service $dynamics): View
    {
        $data = $request->validate([
            'cancel_email' => ['required', 'email'],
            'cancel_password' => ['required', 'string'],
            'cancel_request_id' => ['required', 'string'],
            'cancel_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $data['cancel_email'],
            password: $data['cancel_password'],
            lang: $data['cancel_lang'] ?? null,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['cancelVacationResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $cancelVacationResult = $dynamics->cancelVacation(
            email: $data['cancel_email'],
            token: $loginResult['token'],
            requestId: $data['cancel_request_id'],
            lang: $data['cancel_lang'] ?? null,
        );

        return view('dynamics.test', compact('cancelVacationResult'));
    }

    public function testExcuseTypes(Request $request, Dynamics365Service $dynamics): View
    {
        $request->validate([
            'excuse_types_email' => ['required', 'email'],
            'excuse_types_password' => ['required', 'string'],
            'excuse_types_lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ]);

        $loginResult = $dynamics->loginUser(
            email: $request->excuse_types_email,
            password: $request->excuse_types_password,
            lang: $request->excuse_types_lang,
        );

        if (! $loginResult['success']) {
            return view('dynamics.test', ['excuseTypesResult' => ['success' => false, 'error' => $loginResult['error']]]);
        }

        $excuseTypesResult = $dynamics->getWorkerExcuseTypeLookup(
            email: $request->excuse_types_email,
            token: $loginResult['token'],
            lang: $request->excuse_types_lang,
        );

        return view('dynamics.test', compact('excuseTypesResult'));
    }
}
