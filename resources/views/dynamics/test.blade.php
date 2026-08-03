@extends('layouts.app')
@section('title', __('admin.dynamics.title'))

@section('content')
    <div class="max-w-5xl space-y-6">

        <div class="bg-indigo-600 rounded-xl p-5 flex items-center justify-between text-white">
            <div>
                <p class="font-semibold">{{ __('admin.dynamics.workspace_title') }}</p>
                <p class="text-sm text-indigo-100">{{ __('admin.dynamics.workspace_cta_description') }}</p>
            </div>
            <a href="{{ route('admin.dynamics.workspace.index') }}" class="bg-white text-indigo-600 text-sm font-medium px-4 py-2 rounded-lg whitespace-nowrap">
                {{ __('admin.dynamics.workspace_cta_button') }} →
            </a>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.dynamics.attendance.index') }}" class="text-sm text-indigo-600 hover:underline">
                {{ __('admin.dynamics.go_to_attendance') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Card 1: app-level connection (Azure AD client credentials) -->
            <div class="space-y-4">
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
                                    <td class="py-2 text-slate-500">{{ __('admin.dynamics.cached_until') }}</td>
                                    <td class="py-2 text-slate-700">{{ $result['cached_until'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $result['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 2: user login test -->
            <div class="space-y-4">
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
                                <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.language') }}</label>
                                <select name="test_lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                                    <option value="">{{ __('admin.dynamics.use_default_lang') }} ({{ config('dynamics365.default_lang') }})</option>
                                    <option value="en-us" @selected(old('test_lang')==='en-us')>en-us</option>
                                    <option value="ar-sa" @selected(old('test_lang')==='ar-sa')>ar-sa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.device_token') }}</label>
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
                                    <td class="py-2 text-slate-700">{{ $loginResult['is_manager'] ? __('admin.common.active') : __('admin.common.inactive') }}</td>
                                </tr>
                                <tr class="border-t border-slate-100">
                                    <td class="py-2 text-slate-500">{{ __('admin.dynamics.session_token_preview') }}</td>
                                    <td class="py-2 font-mono text-slate-700">{{ $loginResult['token'] ? substr($loginResult['token'], 0, 8).'...' : '—' }}</td>
                                </tr>
                            </table>
                            <p class="text-xs text-slate-400">{{ __('admin.dynamics.login_success_hint') }}</p>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $loginResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 3: team members test -->
            <div class="space-y-4">
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
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.session_token') }}</label>
                            <input type="text" name="team_token" value="{{ old('team_token') }}" required
                                   placeholder="{{ __('admin.dynamics.session_token_hint') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                        </div>
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
                                <span class="ms-auto text-xs px-2 py-1 rounded-full {{ $teamResult['is_manager'] ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $teamResult['is_manager'] ? __('admin.dynamics.is_manager') : __('admin.dynamics.not_manager') }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-400 mb-2">{{ __('admin.dynamics.my_team') }} ({{ count($teamResult['team']) }})</p>
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($teamResult['team'] as $member)
                                            <tr>
                                                <td class="py-1.5">{{ $member['name'] ?: '—' }}</td>
                                                <td class="py-1.5 text-slate-500">{{ $member['position'] ?: '—' }}</td>
                                                <td class="py-1.5 font-mono">{{ $member['personnel_number'] ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $teamResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 4: home page data (login + combined dashboard fetch) -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.home_test_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.home_test_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-home-page') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="home_email" value="{{ old('home_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="home_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.home_test_button') }}
                        </button>
                    </form>
                </div>

                @isset($homeResult)
                    @if($homeResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $homeResult['name'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $homeResult['gender'] }}</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.shift') }}</p>
                                    <p class="text-sm font-medium text-slate-700">{{ $homeResult['shift_start_time'] }} – {{ $homeResult['shift_end_time'] }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-100 p-3">
                                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.clock_in') }}</p>
                                    <p class="text-sm font-medium {{ $homeResult['can_clock_in'] ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $homeResult['clock_in_time'] ?: __('admin.dynamics.not_yet') }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-slate-100 p-3">
                                    <p class="text-xs text-slate-400">{{ __('admin.dynamics.clock_out') }}</p>
                                    <p class="text-sm font-medium {{ $homeResult['can_clock_out'] ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $homeResult['clock_out_time'] ?: __('admin.dynamics.not_yet') }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-400 mb-2">{{ __('admin.dynamics.leave_balances') }}</p>
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse(array_merge($homeResult['annual_leave'], $homeResult['sick_leave']) as $leave)
                                            <tr>
                                                <td class="py-1.5">{{ $leave['leave_type'] }}</td>
                                                <td class="py-1.5 font-medium text-indigo-600">{{ $leave['available_balance'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $homeResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 5: all requests (flattened, not grouped by type) -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.requests_test_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.requests_test_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-all-requests') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="requests_email" value="{{ old('requests_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.session_token') }}</label>
                            <input type="text" name="requests_token" value="{{ old('requests_token') }}" required
                                   placeholder="{{ __('admin.dynamics.session_token_hint') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                        </div>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.requests_test_button') }}
                        </button>
                    </form>
                </div>

                @isset($requestsResult)
                    @if($requestsResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6">
                            <p class="text-xs font-semibold uppercase text-slate-400 mb-2">
                                {{ __('admin.dynamics.all_requests') }} ({{ count($requestsResult['requests']) }})
                            </p>
                            <table class="w-full text-sm">
                                <thead class="text-slate-400 text-xs">
                                    <tr>
                                        <th class="text-start font-normal pb-1">{{ __('admin.dynamics.request_id') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.dynamics.category') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.common.status') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.dynamics.creation_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($requestsResult['requests'] as $req)
                                        <tr>
                                            <td class="py-1.5 font-mono text-xs">{{ $req['request_id'] ?: '—' }}</td>
                                            <td class="py-1.5">{{ $req['category'] }}</td>
                                            <td class="py-1.5">{{ $req['status'] ?: '—' }}</td>
                                            <td class="py-1.5 text-xs text-slate-500">{{ $req['creation_date'] ? \Illuminate\Support\Carbon::parse($req['creation_date'])->format('Y-m-d') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $requestsResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

        </div>
    </div>
@endsection
