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
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="requests_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
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
                                        <tr class="request-row cursor-pointer hover:bg-slate-50"
                                            data-request-id="{{ $req['request_id'] }}"
                                            data-request-type="{{ $req['details']['RequestTypeId'] ?? '' }}"
                                            data-worker-rec-id="{{ $req['details']['WorkerRecId'] ?? '' }}">
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

                        <div id="request-detail-panel" class="hidden bg-white rounded-xl shadow-sm p-6"></div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $requestsResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 6: workers directory (filtered by starting letter) -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.directory_test_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.directory_test_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-workers-directory') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="directory_email" value="{{ old('directory_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="directory_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.starting_letter') }}</label>
                            <input type="text" name="directory_letter" value="{{ old('directory_letter') }}" maxlength="1" required
                                   placeholder="A" class="w-24 rounded-lg border border-slate-300 px-4 py-2 text-sm uppercase">
                        </div>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.directory_test_button') }}
                        </button>
                    </form>
                </div>

                @isset($directoryResult)
                    @if($directoryResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6">
                            <p class="text-xs font-semibold uppercase text-slate-400 mb-2">
                                {{ __('admin.dynamics.directory_results') }} ({{ count($directoryResult['directory']) }})
                            </p>
                            <table class="w-full text-sm">
                                <thead class="text-slate-400 text-xs">
                                    <tr>
                                        <th class="text-start font-normal pb-1">{{ __('admin.common.name') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.dynamics.position') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.common.phone') }}</th>
                                        <th class="text-start font-normal pb-1">{{ __('admin.common.email') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($directoryResult['directory'] as $worker)
                                        <tr>
                                            <td class="py-1.5">{{ $worker['name'] ?: '—' }}</td>
                                            <td class="py-1.5 text-slate-500">{{ $worker['position'] ?: '—' }}</td>
                                            <td class="py-1.5">{{ $worker['phone'] ?: '—' }}</td>
                                            <td class="py-1.5">{{ $worker['email'] ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-2 text-slate-400">{{ __('admin.common.no_results') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $directoryResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 7: vacation type lookup -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.vacation_types_test_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.vacation_types_test_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-vacation-types') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="vacation_types_email" value="{{ old('vacation_types_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="vacation_types_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.vacation_types_test_button') }}
                        </button>
                    </form>
                </div>

                @isset($vacationTypesResult)
                    @if($vacationTypesResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6">
                            <p class="text-xs font-semibold uppercase text-slate-400 mb-2">
                                {{ __('admin.dynamics.vacation_types_results') }} ({{ count($vacationTypesResult['vacation_types']) }})
                            </p>
                            <p class="text-xs text-slate-400 mb-3">{{ __('admin.dynamics.unknown_shape_note') }}</p>

                            @if(count($vacationTypesResult['vacation_types']))
                                @php $columns = array_keys(array_diff_key($vacationTypesResult['vacation_types'][0], ['$id' => null])); @endphp
                                <table class="w-full text-sm">
                                    <thead class="text-slate-400 text-xs">
                                        <tr>
                                            @foreach($columns as $col)
                                                <th class="text-start font-normal pb-1">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($vacationTypesResult['vacation_types'] as $entry)
                                            <tr>
                                                @foreach($columns as $col)
                                                    <td class="py-1.5">{{ is_array($entry[$col] ?? null) ? json_encode($entry[$col]) : ($entry[$col] ?? '—') }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-sm text-slate-400">{{ __('admin.common.no_results') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $vacationTypesResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 8: create vacation request -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.create_vacation_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.create_vacation_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-create-vacation') }}" class="space-y-3" id="create-vacation-form">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="cv_email" id="cv_email" value="{{ old('cv_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="cv_password" id="cv_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.vacation_type_id') }}</label>
                            <div class="flex gap-2">
                                <select name="cv_vacation_type" id="cv_vacation_type_select" required disabled
                                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm bg-slate-50">
                                    <option value="">{{ __('admin.dynamics.load_types_first') }}</option>
                                </select>
                                <button type="button" id="load-vacation-types-btn"
                                        class="whitespace-nowrap bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg text-xs">
                                    {{ __('admin.dynamics.load_types_button') }}
                                </button>
                            </div>
                            <p id="load-vacation-types-status" class="text-xs text-slate-400 mt-1"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.from_date') }}</label>
                                <input type="date" name="cv_from_date" value="{{ old('cv_from_date') }}" required
                                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.to_date') }}</label>
                                <input type="date" name="cv_to_date" value="{{ old('cv_to_date') }}" required
                                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.reason') }}</label>
                            <textarea name="cv_reason" rows="2" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">{{ old('cv_reason') }}</textarea>
                        </div>
                        @error('cv_to_date')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.create_vacation_button') }}
                        </button>
                    </form>
                </div>

                @isset($createVacationResult)
                    @if($createVacationResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <h3 class="font-semibold text-emerald-700">{{ __('admin.dynamics.create_vacation_success') }}</h3>
                            </div>
                            <p class="text-xs text-slate-400">{{ __('admin.dynamics.unknown_shape_note') }}</p>
                            @if(count((array) $createVacationResult['details']))
                                <table class="w-full text-sm">
                                    @foreach((array) $createVacationResult['details'] as $key => $value)
                                        @if($key !== '$id')
                                            <tr class="border-t border-slate-100">
                                                <td class="py-1.5 text-slate-500 text-xs w-1/3">{{ $key }}</td>
                                                <td class="py-1.5 break-all">{{ is_array($value) ? json_encode($value) : ($value ?: '—') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $createVacationResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 9: cancel vacation request -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.cancel_vacation_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.cancel_vacation_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-cancel-vacation') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="cancel_email" value="{{ old('cancel_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="cancel_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.request_id') }}</label>
                            <input type="text" name="cancel_request_id" value="{{ old('cancel_request_id') }}" required
                                   placeholder="{{ __('admin.dynamics.request_id_hint') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                        </div>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.cancel_vacation_button') }}
                        </button>
                    </form>
                </div>

                @isset($cancelVacationResult)
                    @if($cancelVacationResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <h3 class="font-semibold text-emerald-700">{{ __('admin.dynamics.cancel_vacation_success') }}</h3>
                            </div>
                            <p class="text-xs text-slate-400">{{ __('admin.dynamics.unknown_shape_note') }}</p>
                            @if(count((array) $cancelVacationResult['details']))
                                <table class="w-full text-sm">
                                    @foreach((array) $cancelVacationResult['details'] as $key => $value)
                                        @if($key !== '$id')
                                            <tr class="border-t border-slate-100">
                                                <td class="py-1.5 text-slate-500 text-xs w-1/3">{{ $key }}</td>
                                                <td class="py-1.5 break-all">{{ is_array($value) ? json_encode($value) : ($value ?: '—') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $cancelVacationResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

            <!-- Card 10: excuse type lookup -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.excuse_types_test_title') }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.excuse_types_test_description') }}</p>

                    <form method="POST" action="{{ route('admin.dynamics.test-excuse-types') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                            <input type="email" name="excuse_types_email" value="{{ old('excuse_types_email') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                            <input type="password" name="excuse_types_password" required
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        </div>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                            {{ __('admin.dynamics.excuse_types_test_button') }}
                        </button>
                    </form>
                </div>

                @isset($excuseTypesResult)
                    @if($excuseTypesResult['success'])
                        <div class="bg-white rounded-xl shadow-sm border-2 border-emerald-500 p-6">
                            <p class="text-xs font-semibold uppercase text-slate-400 mb-2">
                                {{ __('admin.dynamics.excuse_types_results') }} ({{ count($excuseTypesResult['excuse_types']) }})
                            </p>
                            <p class="text-xs text-slate-400 mb-3">{{ __('admin.dynamics.unknown_shape_note') }}</p>

                            @if(count($excuseTypesResult['excuse_types']))
                                @php $columns = array_keys(array_diff_key($excuseTypesResult['excuse_types'][0], ['$id' => null])); @endphp
                                <table class="w-full text-sm">
                                    <thead class="text-slate-400 text-xs">
                                        <tr>
                                            @foreach($columns as $col)
                                                <th class="text-start font-normal pb-1">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($excuseTypesResult['excuse_types'] as $entry)
                                            <tr>
                                                @foreach($columns as $col)
                                                    <td class="py-1.5">{{ is_array($entry[$col] ?? null) ? json_encode($entry[$col]) : ($entry[$col] ?? '—') }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-sm text-slate-400">{{ __('admin.common.no_results') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                            <p class="text-sm text-red-600 font-mono break-all">{{ $excuseTypesResult['error'] }}</p>
                        </div>
                    @endif
                @endisset
            </div>

        </div>
    </div>

    <script>
    document.addEventListener('click', async function (e) {
        const row = e.target.closest('.request-row');
        if (!row) return;

        const panel = document.getElementById('request-detail-panel');
        panel.classList.remove('hidden');
        panel.innerHTML = '<p class="text-sm text-slate-400">{{ __('admin.dynamics.loading') }}</p>';

        try {
            const response = await fetch('{{ route('admin.dynamics.request-detail') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    request_id: row.dataset.requestId,
                    request_type: row.dataset.requestType,
                    worker_rec_id: row.dataset.workerRecId,
                }),
            });
            const json = await response.json();

            if (!json.success) {
                panel.innerHTML = `<p class="text-sm text-red-600">${json.message}</p>`;
                return;
            }

            // Response shape from Dynamics isn't fixed/known ahead of time,
            // so render whatever comes back as a generic key/value table
            // rather than assuming specific field names.
            const rows = Object.entries(json.data)
                .filter(([key]) => key !== '$id')
                .map(([key, value]) => `
                    <tr class="border-t border-slate-100">
                        <td class="py-1.5 text-slate-500 text-xs align-top w-1/3">${key}</td>
                        <td class="py-1.5 text-sm break-all">${value === null || value === '' ? '—' : (typeof value === 'object' ? JSON.stringify(value) : value)}</td>
                    </tr>
                `).join('');

            panel.innerHTML = `
                <h3 class="font-semibold text-slate-700 mb-3">{{ __('admin.dynamics.request_detail_title') }}</h3>
                <table class="w-full">${rows}</table>
            `;
        } catch (err) {
            panel.innerHTML = '<p class="text-sm text-red-600">{{ __('admin.dynamics.loading_failed') }}</p>';
        }
    });

    // Populate the vacation-type dropdown on the Create Vacation card.
    document.getElementById('load-vacation-types-btn')?.addEventListener('click', async function () {
        const email = document.getElementById('cv_email').value;
        const password = document.getElementById('cv_password').value;
        const select = document.getElementById('cv_vacation_type_select');
        const status = document.getElementById('load-vacation-types-status');

        if (!email || !password) {
            status.textContent = @json(__('admin.dynamics.load_types_need_credentials'));
            status.className = 'text-xs text-red-600 mt-1';
            return;
        }

        status.textContent = @json(__('admin.dynamics.loading'));
        status.className = 'text-xs text-slate-400 mt-1';
        select.disabled = true;
        select.innerHTML = `<option value="">${@json(__('admin.dynamics.loading'))}</option>`;

        try {
            const response = await fetch('{{ route('admin.dynamics.vacation-types-lookup-ajax') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ email, password }),
            });
            const json = await response.json();

            if (!json.success || !Array.isArray(json.data) || json.data.length === 0) {
                select.innerHTML = `<option value="">${@json(__('admin.common.no_results'))}</option>`;
                status.textContent = json.message || @json(__('admin.common.no_results'));
                status.className = 'text-xs text-red-600 mt-1';
                return;
            }

            // Field names for this endpoint aren't confirmed yet, so guess
            // which key looks like an ID and which looks like a display
            // label. Once a real response is confirmed, replace this with
            // the actual field names and delete the heuristic.
            const pickIdKey = (obj) => {
                const keys = Object.keys(obj).filter(k => k !== '$id');
                return keys.find(k => /id$/i.test(k)) || keys[0] || '$id';
            };
            const pickLabelKey = (obj, idKey) => {
                const keys = Object.keys(obj).filter(k => k !== '$id' && k !== idKey);
                return keys.find(k => /name|description|type|label/i.test(k)) || keys[0] || idKey;
            };

            select.innerHTML = '';
            json.data.forEach(item => {
                const idKey = pickIdKey(item);
                const labelKey = pickLabelKey(item, idKey);
                const option = document.createElement('option');
                option.value = item[idKey];
                option.textContent = item[labelKey] || item[idKey];
                select.appendChild(option);
            });
            select.disabled = false;
            status.textContent = @json(__('admin.dynamics.load_types_success'));
            status.className = 'text-xs text-emerald-600 mt-1';
        } catch (err) {
            select.innerHTML = `<option value="">${@json(__('admin.dynamics.loading_failed'))}</option>`;
            status.textContent = @json(__('admin.dynamics.loading_failed'));
            status.className = 'text-xs text-red-600 mt-1';
        }
    });
    </script>
@endsection
