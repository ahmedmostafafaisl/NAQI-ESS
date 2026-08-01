@extends('layouts.app')
@section('title', __('admin.notifications.send'))

@section('content')
    <div class="max-w-4xl mx-auto">

        <!-- Send To tabs, centered at the top -->
        <div class="flex flex-col items-center mb-6">
            <label class="block text-sm font-medium text-slate-600 mb-2">{{ __('admin.notifications.send_mode') }}</label>
            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1" role="tablist">
                <button type="button" id="tab-audience" role="tab" aria-selected="true"
                    class="tab-btn px-6 py-2 rounded-md text-sm font-medium transition bg-white text-indigo-600 shadow-sm">
                    {{ __('admin.notifications.mode_audience') }}
                </button>
                <button type="button" id="tab-tokens" role="tab" aria-selected="false"
                    class="tab-btn px-6 py-2 rounded-md text-sm font-medium transition text-slate-500 hover:text-slate-700">
                    {{ __('admin.notifications.mode_tokens') }}
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-6">
            @csrf
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}
                </div>
            @endif

            <input type="hidden" name="send_mode" id="send_mode" value="audience">

            <!-- Two panels, side by side. The inactive one is visually + functionally disabled. -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div id="panel-audience"
                    class="form-panel bg-white rounded-xl shadow-sm p-6 space-y-4 border-2 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-slate-700">{{ __('admin.notifications.mode_audience') }}</h3>
                        <span
                            class="panel-badge text-xs font-medium px-2 py-1 rounded-full bg-indigo-100 text-indigo-600">{{ __('admin.common.active') }}</span>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.audience') }}</label>
                        <select name="audience" id="audience"
                            class="panel-field w-full rounded-lg border border-slate-300 px-4 py-2">
                            <option value="all">{{ __('admin.notifications.audience_all') }}</option>
                            <option value="employees">{{ __('admin.notifications.audience_employees') }}</option>
                            <option value="customers">{{ __('admin.notifications.audience_customers') }}</option>
                            <option value="specific">{{ __('admin.notifications.audience_specific') }}</option>
                        </select>
                    </div>

                    <div id="specific-users" class="hidden">
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.select_users') }}</label>
                        <select name="user_ids[]" multiple
                            class="panel-field w-full rounded-lg border border-slate-300 px-4 py-2 h-40">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="panel-tokens"
                    class="form-panel bg-white rounded-xl shadow-sm p-6 space-y-4 border-2 border-transparent opacity-50">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-slate-700">{{ __('admin.notifications.mode_tokens') }}</h3>
                        <span
                            class="panel-badge text-xs font-medium px-2 py-1 rounded-full bg-slate-100 text-slate-500">{{ __('admin.common.inactive') }}</span>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.tokens_label') }}</label>
                        <textarea name="tokens" id="tokens" rows="8" disabled
                            placeholder="{{ __('admin.notifications.tokens_placeholder') }}"
                            class="panel-field w-full rounded-lg border border-slate-300 px-4 py-2 font-mono text-xs"></textarea>
                        <p class="text-xs text-slate-400 mt-1">{{ __('admin.notifications.tokens_hint') }}</p>
                    </div>
                </div>
            </div>

            <!-- Shared fields -->
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.title_field') }}</label>
                    <input type="text" name="title" required class="w-full rounded-lg border border-slate-300 px-4 py-2">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.message') }}</label>
                    <textarea name="body" rows="4" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2"></textarea>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.notifications.category') }}</label>
                    <input type="text" name="category" placeholder="{{ __('admin.notifications.category_placeholder') }}"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2">
                </div>

                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">{{ __('admin.notifications.send') }}</button>
            </div>
        </form>
    </div>

    <script>
        const sendModeInput = document.getElementById('send_mode');
        const tabAudience = document.getElementById('tab-audience');
        const tabTokens = document.getElementById('tab-tokens');
        const panelAudience = document.getElementById('panel-audience');
        const panelTokens = document.getElementById('panel-tokens');
        const audienceSelect = document.getElementById('audience');
        const specificUsers = document.getElementById('specific-users');

        const tabActive = ['bg-white', 'text-indigo-600', 'shadow-sm'];
        const tabInactive = ['text-slate-500', 'hover:text-slate-700'];

        function setPanelState(panel, isActive) {
            panel.classList.toggle('border-indigo-500', isActive);
            panel.classList.toggle('border-transparent', !isActive);
            panel.classList.toggle('opacity-50', !isActive);

            const badge = panel.querySelector('.panel-badge');
            badge.textContent = isActive
                ? @json(__('admin.common.active'))
                : @json(__('admin.common.inactive'));
            badge.classList.toggle('bg-indigo-100', isActive);
            badge.classList.toggle('text-indigo-600', isActive);
            badge.classList.toggle('bg-slate-100', !isActive);
            badge.classList.toggle('text-slate-500', !isActive);

            panel.querySelectorAll('.panel-field').forEach(field => {
                field.disabled = !isActive;
            });

            // A disabled panel's fields shouldn't count toward "specific users" visibility logic
            if (!isActive) {
                panel.querySelectorAll('select, textarea, input').forEach(el => el.disabled = true);
            }
        }

        function activateTab(mode) {
            sendModeInput.value = mode;
            const isTokens = mode === 'tokens';

            setPanelState(panelAudience, !isTokens);
            setPanelState(panelTokens, isTokens);

            [tabAudience, tabTokens].forEach(btn => btn.classList.remove(...tabActive, ...tabInactive));
            (isTokens ? tabTokens : tabAudience).classList.add(...tabActive);
            (isTokens ? tabAudience : tabTokens).classList.add(...tabInactive);

            tabTokens.setAttribute('aria-selected', isTokens ? 'true' : 'false');
            tabAudience.setAttribute('aria-selected', isTokens ? 'false' : 'true');

            // Re-apply the "specific users" sub-toggle whenever audience panel becomes active again
            if (!isTokens) {
                specificUsers.classList.toggle('hidden', audienceSelect.value !== 'specific');
            }
        }

        tabAudience.addEventListener('click', () => activateTab('audience'));
        tabTokens.addEventListener('click', () => activateTab('tokens'));

        audienceSelect.addEventListener('change', function (e) {
            specificUsers.classList.toggle('hidden', e.target.value !== 'specific');
        });
    </script>
@endsection
