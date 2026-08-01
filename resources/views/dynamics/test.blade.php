@extends('layouts.app')
@section('title', __('admin.dynamics.title'))

@section('content')
    <div class="max-w-2xl space-y-6">

        <div class="flex justify-end">
            <a href="{{ route('admin.dynamics.attendance.index') }}" class="text-sm text-indigo-600 hover:underline">
                {{ __('admin.dynamics.go_to_attendance') }} →
            </a>
        </div>

        <!-- Panel 1: app-level connection (Azure AD client credentials) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.test_title') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.test_description') }}</p>

            <form method="POST" action="{{ route('admin.dynamics.test') }}">
                @csrf
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    {{ __('admin.dynamics.test_button') }}
                </button>
            </form>
        </div>

        @isset($result)
            @if($result['success'])
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700">{{ __('admin.dynamics.success_title') }}</h3>
                    </div>

                    <table class="w-full text-sm">
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.token_type') }}</td>
                            <td class="py-2 font-mono text-slate-700">{{ $result['token_type'] }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.token_preview') }}</td>
                            <td class="py-2 font-mono text-slate-700">{{ $result['access_token_preview'] }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.expires_in') }}</td>
                            <td class="py-2 text-slate-700">{{ $result['expires_in'] }} {{ __('admin.dynamics.seconds') }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.expires_at') }}</td>
                            <td class="py-2 text-slate-700">{{ $result['expires_at'] }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.cached_until') }}</td>
                            <td class="py-2 text-slate-700">{{ $result['cached_until'] }}</td>
                        </tr>
                    </table>
                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.success_hint') }}</p>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700">{{ __('admin.dynamics.failure_title') }}</h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all">{{ $result['error'] }}</p>
                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.failure_hint') }}</p>
                </div>
            @endif
        @endisset

        <!-- Panel 2: user login (INDXNaqiEssAuthSvc/Login) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.login_test_title') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.login_test_description') }}</p>

            <form method="POST" action="{{ route('admin.dynamics.test-login') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                    <input type="email" name="test_email" value="{{ old('test_email') }}" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                    <input type="password" name="test_password" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.language') }}</label>
                        <select name="test_lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                            <option value="">{{ __('admin.dynamics.use_default_lang') }}
                                ({{ config('dynamics365.default_lang') }})</option>
                            <option value="en-us" @selected(old('test_lang') === 'en-us')>en-us</option>
                            <option value="ar-sa" @selected(old('test_lang') === 'ar-sa')>ar-sa</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.device_token') }}</label>
                        <input type="text" name="test_device_token" value="{{ old('test_device_token') }}"
                            placeholder="{{ __('admin.dynamics.device_token_optional') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                    </div>
                </div>
                @error('test_email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    {{ __('admin.dynamics.login_test_button') }}
                </button>
            </form>
        </div>

        @isset($loginResult)
            @if($loginResult['success'])
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700">{{ __('admin.dynamics.login_success_title') }}</h3>
                    </div>

                    <table class="w-full text-sm">
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.worker') }}</td>
                            <td class="py-2 font-mono text-slate-700">{{ $loginResult['worker'] ?? '—' }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.is_manager') }}</td>
                            <td class="py-2 text-slate-700">
                                {{ $loginResult['is_manager'] ? __('admin.common.active') : __('admin.common.inactive') }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.first_login') }}</td>
                            <td class="py-2 text-slate-700">{{ $loginResult['first_login'] ? 'true' : 'false' }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.session_token_preview') }}</td>
                            <td class="py-2 font-mono text-slate-700">
                                {{ $loginResult['token'] ? substr($loginResult['token'], 0, 8) . '...' : '—' }}</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 text-slate-500">{{ __('admin.dynamics.services_access_list') }}</td>
                            <td class="py-2 text-slate-700">{{ count($loginResult['services_access_list']) }}</td>
                        </tr>
                    </table>
                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.login_success_hint') }}</p>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700">{{ __('admin.dynamics.login_failure_title') }}</h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all">{{ $loginResult['error'] }}</p>
                </div>
            @endif
        @endisset

        <!-- Panel 3: get team members (INDXNaqiEssActionMyTeamSvc/getWorkerTeam) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.team_test_title') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.team_test_description') }}</p>

            <form method="POST" action="{{ route('admin.dynamics.test-team-members') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                    <input type="email" name="team_email" value="{{ old('team_email') }}" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.session_token') }}</label>
                    <input type="text" name="team_token" value="{{ old('team_token') }}" required
                        placeholder="{{ __('admin.dynamics.session_token_hint') }}"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.language') }}</label>
                    <select name="team_lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        <option value="">{{ __('admin.dynamics.use_default_lang') }}
                            ({{ config('dynamics365.default_lang') }})</option>
                        <option value="en-us" @selected(old('team_lang') === 'en-us')>en-us</option>
                        <option value="ar-sa" @selected(old('team_lang') === 'ar-sa')>ar-sa</option>
                    </select>
                </div>
                @error('team_email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    {{ __('admin.dynamics.team_test_button') }}
                </button>
            </form>
        </div>

        @isset($teamResult)
            @if($teamResult['success'])
                <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-emerald-700">{{ __('admin.dynamics.team_success_title') }}</h3>
                        <span
                            class="ms-auto text-xs px-2 py-1 rounded-full {{ $teamResult['is_manager'] ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ $teamResult['is_manager'] ? __('admin.dynamics.is_manager') : __('admin.dynamics.not_manager') }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-2">{{ __('admin.dynamics.my_team') }}
                            ({{ count($teamResult['team']) }})</p>
                        <table class="w-full text-sm">
                            <thead class="text-slate-400 text-xs">
                                <tr>
                                    <th class="text-start font-normal pb-1">{{ __('admin.common.name') }}</th>
                                    <th class="text-start font-normal pb-1">{{ __('admin.dynamics.position') }}</th>
                                    <th class="text-start font-normal pb-1">{{ __('admin.dynamics.personnel_number') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($teamResult['team'] as $member)
                                    <tr>
                                        <td class="py-1.5">{{ $member['name'] ?: '—' }}</td>
                                        <td class="py-1.5">{{ $member['position'] ?: '—' }}</td>
                                        <td class="py-1.5 font-mono">{{ $member['personnel_number'] ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-400 mb-2">{{ __('admin.dynamics.my_managers') }}
                            ({{ count($teamResult['managers']) }})</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-100">
                                @forelse($teamResult['managers'] as $manager)
                                    <tr>
                                        <td class="py-1.5">{{ $manager['name'] ?: '—' }}</td>
                                        <td class="py-1.5">{{ $manager['position'] ?: '—' }}</td>
                                        <td class="py-1.5 font-mono">{{ $manager['personnel_number'] ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700">{{ __('admin.dynamics.team_failure_title') }}</h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all">{{ $teamResult['error'] }}</p>
                </div>
            @endif
        @endisset
    </div>
@endsection
