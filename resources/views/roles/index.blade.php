@extends('layouts.app')
@section('title', 'Roles')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.roles.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Add Role</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Permissions</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($roles as $role)
            <tr>
                <td class="px-5 py-3 font-medium">{{ $role->name }}</td>
                <td class="px-5 py-3">{{ $role->permissions_count }} permissions</td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-indigo-600 hover:underline">Edit</a>
                    @unless(in_array($role->name, ['admin', 'super-admin']))
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Delete this role?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $roles->links() }}</div>
@endsection
