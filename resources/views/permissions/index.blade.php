@extends('layouts.app')
@section('title', __('admin.permissions.title'))

@section('content')
    <form method="POST" action="{{ route('admin.permissions.store') }}"
        class="bg-white rounded-xl shadow-sm p-5 mb-6 flex gap-3">
        @csrf
        <input type="text" name="name" placeholder="{{ __('admin.permissions.name_placeholder') }}" required
            class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm">
        <button
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm">{{ __('admin.permissions.add') }}</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start">{{ __('admin.permissions.title') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($permissions as $permission)
                    <tr>
                        <td class="px-5 py-3">{{ $permission->name }}</td>
                        <td class="px-5 py-3 text-end">
                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST"
                                class="inline-block" onsubmit="return confirm('{{ __('admin.common.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <x-action-button variant="delete"
                                    type="submit">{{ __('admin.common.delete') }}</x-action-button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $permissions->links() }}</div>
@endsection
