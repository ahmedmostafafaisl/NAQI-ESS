@extends('layouts.app')
@section('title', 'Edit Role')

@section('content')
<form method="POST" action="{{ route('admin.roles.update', $role) }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-5">
    @csrf @method('PUT')
    @include('roles._form')
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">Update Role</button>
</form>
@endsection
