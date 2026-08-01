<aside class="w-64 shrink-0 bg-slate-900 text-slate-300 flex flex-col">
    <div class="px-6 py-5 flex items-center gap-3 border-b border-slate-800">
        <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
            {{ mb_substr(__('admin.app_name'), 0, 1) }}
        </div>
        <div>
            <p class="text-white font-bold leading-none">{{ __('admin.app_name') }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('admin.erp_label') }}</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-6 text-sm overflow-y-auto">
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {{ __('admin.nav.main') }}</p>
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                <span class="text-base">🏠</span>
                <span>{{ __('admin.nav.dashboard') }}</span>
            </a>
        </div>

        @if(auth()->user()->can('users.view') || auth()->user()->can('roles.manage') || auth()->user()->can('permissions.manage') || auth()->user()->can('settings.view'))
            <div>
                <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('admin.nav.administration') }}</p>
                <div class="space-y-1">
                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                            <span class="text-base">👥</span>
                            <span>{{ __('admin.nav.users') }}</span>
                        </a>
                    @endcan
                    @can('roles.manage')
                        <a href="{{ route('admin.roles.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                            <span class="text-base">🛡️</span>
                            <span>{{ __('admin.nav.roles') }}</span>
                        </a>
                    @endcan
                    @can('permissions.manage')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                            <span class="text-base">🔑</span>
                            <span>{{ __('admin.nav.permissions') }}</span>
                        </a>
                    @endcan
                    @can('settings.view')
                        <a href="{{ route('admin.settings.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                            <span class="text-base">⚙️</span>
                            <span>{{ __('admin.nav.settings') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {{ __('admin.nav.engagement') }}</p>
            <a href="{{ route('admin.notifications.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.notifications.*') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800/70 hover:text-white' }}">
                <span class="text-base">🔔</span>
                <span>{{ __('admin.nav.notifications') }}</span>
            </a>
        </div>
    </nav>

    <div class="px-3 py-4 border-t border-slate-800">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800/70 hover:text-white text-sm transition">
                <span class="text-base">🚪</span>
                <span>{{ __('admin.nav.logout') }}</span>
            </button>
        </form>
    </div>
</aside>
