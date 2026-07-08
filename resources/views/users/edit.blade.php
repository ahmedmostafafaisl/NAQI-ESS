@extends('layouts.app')
@section('title', __('admin.users.edit'))

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')
    @include('users._form')
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">{{ __('admin.users.update') }}</button>
</form>
@endsection
