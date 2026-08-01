<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dynamics365Service;
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
}
