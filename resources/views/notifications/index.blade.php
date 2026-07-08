@extends('layouts.app')
@section('title', __('admin.notifications.title'))

@section('content')
<div class="flex justify-end mb-5">
    @can('notifications.send')
    <a href="{{ route('admin.notifications.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ {{ __('admin.notifications.send') }}</a>
    @endcan
</div>

<div class="bg-white rounded-xl shadow-sm divide-y divide-slate-100">
    @forelse($items as $item)
    <div class="px-5 py-4 flex items-start justify-between {{ $item->read_at ? '' : 'bg-indigo-50/40' }}">
        <div>
            <p class="font-medium text-slate-800">{{ $item->data['title'] ?? '' }}</p>
            <p class="text-sm text-slate-500">{{ $item->data['body'] ?? '' }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $item->created_at->diffForHumans() }}</p>
        </div>
        @unless($item->read_at)
        <form action="{{ route('admin.notifications.read', $item->id) }}" method="POST">
            @csrf
            <button class="text-xs text-indigo-600 hover:underline">{{ __('admin.notifications.mark_read') }}</button>
        </form>
        @endunless
    </div>
    @empty
    <div class="px-5 py-8 text-center text-slate-400">{{ __('admin.notifications.no_notifications') }}</div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
