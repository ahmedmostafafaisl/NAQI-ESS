@extends('layouts.app')
@section('title', __('admin.roles.title'))

@section('content')
    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.roles.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+
            {{ __('admin.roles.add') }}</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start">{{ __('admin.roles.title') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.roles.permissions') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($roles as $role)
                    <tr>
                        <td class="px-5 py-3 font-medium">{{ $role->name }}</td>
                        <td class="px-5 py-3">{{ __('admin.roles.permissions_count', ['count' => $role->permissions_count]) }}
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-2">
                                <x-action-button variant="edit" :href="route('admin.roles.edit', $role)">{{ __('admin.common.edit') }}</x-action-button>
                                @unless(in_array($role->name, ['admin', 'super-admin']))
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.common.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <x-action-button variant="delete"
                                            type="submit">{{ __('admin.common.delete') }}</x-action-button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $roles->links() }}</div>
@endsection
