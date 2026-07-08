@extends('layouts.app')
@section('title', __('admin.users.title'))

@section('content')
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('admin.common.search') }}..."
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
            <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('admin.common.all') }}</option>
                <option value="employee" @selected(request('type') === 'employee')>{{ __('admin.users.employee') }}</option>
                <option value="customer" @selected(request('type') === 'customer')>{{ __('admin.users.customer') }}</option>
            </select>
            <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">{{ __('admin.common.filter') }}</button>
        </form>
        @can('users.create')
            <a href="{{ route('admin.users.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                + {{ __('admin.users.add') }}
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.email') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.phone') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.role') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr>
                        <td class="px-5 py-3">{{ $user->username }}</td>
                        <td class="px-5 py-3">{{ $user->email ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $user->phone }}</td>
                        <td class="px-5 py-3">
                            {{ $user->type === 'employee' ? __('admin.users.employee') : __('admin.users.customer') }}</td>
                        <td class="px-5 py-3">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->status === 'active' ? __('admin.common.active') : __('admin.common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-2">
                                @can('users.edit')
                                    <x-action-button variant="edit" :href="route('admin.users.edit', $user)">{{ __('admin.common.edit') }}</x-action-button>
                                @endcan
                                @can('users.delete')
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.common.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <x-action-button variant="delete"
                                            type="submit">{{ __('admin.common.delete') }}</x-action-button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-6 text-center text-slate-400">{{ __('admin.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
