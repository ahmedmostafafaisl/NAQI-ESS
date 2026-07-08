@extends('layouts.app')
@section('title', 'Users')

@section('content')
<div class="flex items-center justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
        <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All types</option>
            <option value="employee" @selected(request('type')==='employee')>Employee</option>
            <option value="customer" @selected(request('type')==='customer')>Customer</option>
        </select>
        <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
    </form>
    @can('users.create')
    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
        + Add User
    </a>
    @endcan
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Email</th>
                <th class="px-5 py-3">Phone</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($users as $user)
            <tr>
                <td class="px-5 py-3">{{ $user->username }}</td>
                <td class="px-5 py-3">{{ $user->email ?? '—' }}</td>
                <td class="px-5 py-3">{{ $user->phone }}</td>
                <td class="px-5 py-3 capitalize">{{ $user->type }}</td>
                <td class="px-5 py-3">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    @can('users.edit')
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">Edit</a>
                    @endcan
                    @can('users.delete')
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-6 text-center text-slate-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
