<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = match (true) {
            filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) !== false => 'email',
            preg_match('/^[0-9+ ]+$/', $credentials['login']) === 1 => 'phone',
            default => 'username',
        };

        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password'], 'type' => 'employee'], $request->boolean('remember'))) {
            return back()->withErrors(['login' => __('admin.auth.invalid_credentials')])->onlyInput('login');
        }

        $request->session()->regenerate();

        if (! Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            Auth::logout();

            return back()->withErrors(['login' => __('admin.auth.not_authorized')]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
