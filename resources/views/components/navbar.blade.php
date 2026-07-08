<header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
        <div class="flex items-center gap-2 bg-slate-100 rounded-full pl-1 pr-3 py-1 rtl:pl-3 rtl:pr-1">
            <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">
                {{ mb_substr(auth()->user()->username ?? 'U', 0, 1) }}
            </span>
            <div class="leading-tight">
                <p class="text-sm font-medium text-slate-800">{{ auth()->user()->username }}</p>
                <p class="text-[11px] text-slate-400">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
            </div>
        </div>
        <h1 class="hidden md:block text-base font-semibold text-slate-700 border-s ps-3 border-slate-200">@yield('title', __('admin.dashboard.title'))</h1>
    </div>

    <div class="flex items-center gap-3">
        <!-- Language switcher -->
        <div class="relative group">
            <button type="button" class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 transition rounded-full px-3 py-1.5 text-sm font-medium text-slate-600">
                🌐 {{ strtoupper(app()->getLocale()) }}
            </button>
            <div class="absolute end-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-slate-100 py-1 hidden group-hover:block z-20">
                <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ app()->getLocale() === 'en' ? 'text-indigo-600 font-medium' : 'text-slate-600' }}">
                    {{ __('admin.common.english') }}
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ app()->getLocale() === 'ar' ? 'text-indigo-600 font-medium' : 'text-slate-600' }}">
                    {{ __('admin.common.arabic') }}
                </a>
            </div>
        </div>

        <a href="{{ route('admin.notifications.index') }}"
           class="relative flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 transition rounded-full px-3 py-1.5 text-sm font-medium text-slate-600">
            🔔 {{ __('admin.topbar.notifications') }}
            @if(auth()->user()->unreadNotifications()->count())
                <span class="absolute -top-1.5 -end-1.5 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">
                    {{ auth()->user()->unreadNotifications()->count() }}
                </span>
            @endif
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="bg-slate-900 hover:bg-slate-800 transition text-white rounded-full px-4 py-1.5 text-sm font-medium">
                {{ __('admin.topbar.logout') }}
            </button>
        </form>
    </div>
</header>
