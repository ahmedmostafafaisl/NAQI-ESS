@extends('layouts.app')
@section('title', 'Add User')

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-4">
    @csrf
    @include('users._form', ['user' => null])
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">Create User</button>
</form>
@endsection
