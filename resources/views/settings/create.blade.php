@extends('layouts.app')
@section('title', __('admin.settings.add'))

@section('content')
    <form method="POST" action="{{ route('admin.settings.store') }}"
        class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-4">
        @csrf
        @include('settings._form', ['setting' => null])
        <button
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">{{ __('admin.settings.create') }}</button>
    </form>
@endsection
