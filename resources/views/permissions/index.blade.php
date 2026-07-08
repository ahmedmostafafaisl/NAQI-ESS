@extends('layouts.app')
@section('title', 'Permissions')

@section('content')
<form method="POST" action="{{ route('admin.permissions.store') }}" class="bg-white rounded-xl shadow-sm p-5 mb-6 flex gap-3">
    @csrf
    <input type="text" name="name" placeholder="e.g. leaves.approve" required
           class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm">
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm">Add Permission</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Permission</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($permissions as $permission)
            <tr>
                <td class="px-5 py-3">{{ $permission->name }}</td>
                <td class="px-5 py-3 text-right">
                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline" onsubmit="return confirm('Delete this permission?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $permissions->links() }}</div>
@endsection
