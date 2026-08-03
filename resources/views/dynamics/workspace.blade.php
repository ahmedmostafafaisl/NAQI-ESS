@extends('layouts.app')
@section('title', __('admin.dynamics.workspace_title'))

@section('content')
<div class="max-w-3xl space-y-6">

    @if($step === 'login')
        @isset($sessionExpiredError)
            <div class="bg-white rounded-xl shadow-sm border-2 border-amber-400 p-4 text-sm text-amber-700">
                {{ __('admin.dynamics.session_expired') }}: {{ $sessionExpiredError }}
            </div>
        @endisset

        <div class="bg-white rounded-xl shadow-sm p-6 max-w-md mx-auto">
            <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.workspace_login_title') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.workspace_login_description') }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.dynamics.workspace.login') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.password') }}</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium">
                    {{ __('admin.auth.sign_in') }}
                </button>
            </form>
        </div>
    @endif

    @if($step === 'team')
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-700">{{ __('admin.dynamics.my_team') }}</h3>
                <p class="text-sm text-slate-500">{{ $context['email'] }}</p>
            </div>
            <form method="POST" action="{{ route('admin.dynamics.workspace.logout') }}">
                @csrf
                <button class="text-sm text-slate-500 hover:text-red-600">{{ __('admin.nav.logout') }}</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm divide-y divide-slate-100">
            @forelse($teamResult['team'] as $member)
                <form method="POST" action="{{ route('admin.dynamics.workspace.select-member') }}">
                    @csrf
                    <input type="hidden" name="name" value="{{ $member['name'] }}">
                    <input type="hidden" name="position" value="{{ $member['position'] }}">
                    <input type="hidden" name="personnel_number" value="{{ $member['personnel_number'] }}">
                    <button type="submit" class="w-full flex items-center justify-between px-5 py-3.5 text-start hover:bg-slate-50 transition">
                        <div>
                            <p class="font-medium text-slate-800">{{ $member['name'] ?: __('admin.dynamics.unnamed_position') }}</p>
                            <p class="text-xs text-slate-500">{{ $member['position'] ?: '—' }} · {{ $member['personnel_number'] ?: '—' }}</p>
                        </div>
                        <span class="text-indigo-600 text-sm">{{ __('admin.dynamics.view_calendar') }} →</span>
                    </button>
                </form>
            @empty
                <div class="px-5 py-8 text-center text-slate-400">{{ __('admin.common.no_results') }}</div>
            @endforelse
        </div>

        @if(count($teamResult['managers']))
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-xs font-semibold uppercase text-slate-400 mb-2">{{ __('admin.dynamics.my_managers') }}</p>
                @foreach($teamResult['managers'] as $manager)
                    <p class="text-sm text-slate-700">{{ $manager['name'] }} — {{ $manager['position'] }}</p>
                @endforeach
            </div>
        @endif
    @endif

    @if($step === 'calendar')
        <div class="flex items-center justify-between">
            <div>
                <button type="button" onclick="document.getElementById('back-to-team-form').submit()" class="text-sm text-indigo-600 hover:underline mb-1">
                    ← {{ __('admin.dynamics.back_to_team') }}
                </button>
                <h3 class="font-semibold text-slate-700">{{ $selectedMember['name'] ?: __('admin.dynamics.unnamed_position') }}</h3>
                <p class="text-sm text-slate-500">{{ $selectedMember['position'] }} · {{ $selectedMember['personnel_number'] }}</p>
            </div>
            <form method="POST" action="{{ route('admin.dynamics.workspace.logout') }}">
                @csrf
                <button class="text-sm text-slate-500 hover:text-red-600">{{ __('admin.nav.logout') }}</button>
            </form>
        </div>
        <form id="back-to-team-form" method="POST" action="{{ route('admin.dynamics.workspace.back-to-team') }}" class="hidden">@csrf</form>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.dynamics.workspace.calendar') }}" class="flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.month') }}</label>
                    <input type="number" name="month" min="1" max="12" value="{{ $month }}" required
                           class="w-28 rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.year') }}</label>
                    <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" required
                           class="w-28 rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                    {{ __('admin.dynamics.attendance_load_button') }}
                </button>
            </form>
        </div>

        @isset($calendarResult)
            @if($calendarResult['success'])
                @php
                    $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $days = $calendarResult['days'];
                    $leadingBlanks = 0;
                    if (count($days)) {
                        $firstDayIndex = array_search($days[0]['day_name'], $weekdays);
                        $leadingBlanks = $firstDayIndex !== false ? $firstDayIndex : 0;
                    }
                @endphp

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="grid grid-cols-7 gap-2 mb-2 text-center text-xs font-semibold text-slate-400">
                        @foreach($weekdays as $w)
                            <div>{{ substr($w, 0, 3) }}</div>
                        @endforeach
                    </div>

                    <div id="calendar-grid" class="grid grid-cols-7 gap-2" data-year="{{ $year }}" data-month="{{ $month }}">
                        @for($i = 0; $i < $leadingBlanks; $i++)
                            <div></div>
                        @endfor

                        @foreach($days as $d)
                            @php
                                $bg = $d['is_holiday'] ? 'bg-amber-50 border-amber-300 text-amber-700'
                                    : ($d['is_off_day'] ? 'bg-slate-100 border-slate-200 text-slate-400'
                                    : 'bg-white border-slate-200 text-slate-700 hover:border-indigo-400 hover:bg-indigo-50');
                            @endphp
                            <button type="button"
                                    class="day-cell border rounded-lg py-2.5 text-sm font-medium transition {{ $bg }}"
                                    data-day="{{ $d['day'] }}">
                                {{ $d['day'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4 mt-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-100 border border-slate-200"></span> {{ __('admin.dynamics.off_day') }}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-50 border border-amber-300"></span> {{ __('admin.dynamics.holiday') }}</span>
                    </div>
                </div>

                <div id="day-detail" class="hidden bg-white rounded-xl shadow-sm p-6"></div>
            @else
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <p class="text-sm text-red-600 font-mono break-all">{{ $calendarResult['error'] }}</p>
                </div>
            @endif
        @endisset
    @endif
</div>

<script>
document.addEventListener('click', async function (e) {
    const cell = e.target.closest('.day-cell');
    if (!cell) return;

    const grid = document.getElementById('calendar-grid');
    const year = grid.dataset.year;
    const month = String(grid.dataset.month).padStart(2, '0');
    const day = String(cell.dataset.day).padStart(2, '0');
    const punchDate = `${year}-${month}-${day}`;

    const panel = document.getElementById('day-detail');
    panel.classList.remove('hidden');
    panel.innerHTML = '<p class="text-sm text-slate-400">{{ __('admin.dynamics.loading') }}</p>';

    try {
        const response = await fetch('{{ route('admin.dynamics.workspace.day') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ punch_date: punchDate }),
        });
        const json = await response.json();

        if (!json.success) {
            panel.innerHTML = `<p class="text-sm text-red-600">${json.message}</p>`;
            return;
        }

        const d = json.data;
        panel.innerHTML = `
            <h3 class="font-semibold text-slate-700 mb-3">${punchDate}</h3>
            <table class="w-full text-sm">
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.attendance_status') }}</td><td class="py-2 font-medium">${d.attendance_status ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.time_in') }}</td><td class="py-2">${d.time_in ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.time_out') }}</td><td class="py-2">${d.time_out ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.profile_time_in') }}</td><td class="py-2">${d.profile_time_in ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.profile_time_out') }}</td><td class="py-2">${d.profile_time_out ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.worked_hours') }}</td><td class="py-2">${d.worked_hours ?? '—'}</td></tr>
                <tr class="border-t border-slate-100"><td class="py-2 text-slate-500">{{ __('admin.dynamics.difference') }}</td><td class="py-2">${d.difference ?? '—'}</td></tr>
            </table>
        `;
    } catch (err) {
        panel.innerHTML = '<p class="text-sm text-red-600">{{ __('admin.dynamics.loading_failed') }}</p>';
    }
});
</script>
@endsection
