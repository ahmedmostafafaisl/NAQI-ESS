@extends('layouts.app')
@section('title', __('admin.roles.add'))

@section('content')
<form method="POST" action="{{ route('admin.roles.store') }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-5">
    @csrf
    @include('roles._form', ['role' => null, 'rolePermissions' => []])
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">{{ __('admin.roles.create') }}</button>
</form>
@endsection
