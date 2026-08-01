@extends('layouts.app')
@section('title', __('admin.dynamics.attendance_title'))

@section('content')
    <div class="max-w-3xl space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-1">{{ __('admin.dynamics.attendance_title') }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ __('admin.dynamics.attendance_description') }}</p>

            <form method="POST" action="{{ route('admin.dynamics.attendance.calendar') }}" class="grid grid-cols-2 gap-3">
                @csrf
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
                    <input type="email" name="email" value="{{ old('email', request('email')) }}" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div class="col-span-2">
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.session_token') }}</label>
                    <input type="text" name="token" value="{{ old('token', request('token')) }}" required
                        placeholder="{{ __('admin.dynamics.session_token_hint') }}"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.month') }}</label>
                    <input type="number" name="month" min="1" max="12" value="{{ old('month', $month ?? now()->month) }}"
                        required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.year') }}</label>
                    <input type="number" name="year" min="2000" max="2100" value="{{ old('year', $year ?? now()->year) }}"
                        required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                </div>
                <div class="col-span-2">
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.dynamics.team_worker_personnel_number') }}</label>
                    <input type="text" name="team_worker_personnel_number"
                        value="{{ old('team_worker_personnel_number', request('team_worker_personnel_number')) }}"
                        placeholder="{{ __('admin.dynamics.team_worker_personnel_number_hint') }}"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-mono">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.language') }}</label>
                    <select name="lang" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                        <option value="">{{ __('admin.dynamics.use_default_lang') }}
                            ({{ config('dynamics365.default_lang') }})</option>
                        <option value="en-us" @selected(old('lang') === 'en-us')>en-us</option>
                        <option value="ar-sa" @selected(old('lang') === 'ar-sa')>ar-sa</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">
                        {{ __('admin.dynamics.attendance_load_button') }}
                    </button>
                </div>
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
                    <h3 class="font-semibold text-slate-700 mb-4">{{ __('admin.dynamics.month') }} {{ $month }} / {{ $year }}</h3>

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
                            <button type="button" class="day-cell border rounded-lg py-2.5 text-sm font-medium transition {{ $bg }}"
                                data-day="{{ $d['day'] }}">
                                {{ $d['day'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4 mt-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5"><span
                                class="w-3 h-3 rounded bg-slate-100 border border-slate-200"></span>
                            {{ __('admin.dynamics.off_day') }}</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-3 h-3 rounded bg-amber-50 border border-amber-300"></span>
                            {{ __('admin.dynamics.holiday') }}</span>
                    </div>
                </div>

                <div id="day-detail" class="hidden bg-white rounded-xl shadow-sm p-6"></div>
            @else
                <div class="bg-white rounded-xl shadow-sm border-2 border-red-500 p-6 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-red-700">{{ __('admin.dynamics.attendance_failure_title') }}</h3>
                    </div>
                    <p class="text-sm text-red-600 font-mono break-all">{{ $calendarResult['error'] }}</p>
                </div>
            @endif
        @endisset
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
                const response = await fetch('{{ route('admin.dynamics.attendance.day') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
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
