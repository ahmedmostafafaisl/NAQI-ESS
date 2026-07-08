<aside class="w-64 bg-slate-900 text-slate-200 flex flex-col">
    <div class="px-6 py-5 text-xl font-bold text-white border-b border-slate-800">
        Naqi <span class="text-indigo-400">ESS</span>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
            Dashboard
        </a>
        @can('users.view')
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
            Users
        </a>
        @endcan
        @can('roles.manage')
        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
            Roles
        </a>
        @endcan
        @can('permissions.manage')
        <a href="{{ route('admin.permissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
            Permissions
        </a>
        @endcan
        <a href="{{ route('admin.notifications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.notifications.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800' }}">
            Notifications
        </a>
    </nav>
    <div class="px-3 py-4 border-t border-slate-800">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-800 text-sm">Logout</button>
        </form>
    </div>
</aside>
