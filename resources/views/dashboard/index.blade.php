@extends('layouts.app')
@section('title', __('admin.dashboard.title'))

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">{{ __('admin.dashboard.total_employees') }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_employees'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">{{ __('admin.dashboard.active_employees') }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_employees'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">{{ __('admin.dashboard.total_customers') }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_customers'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">{{ __('admin.dashboard.unread_notifications') }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['unread_notifications'] }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 font-semibold text-slate-700">{{ __('admin.dashboard.recent_users') }}</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-start">
            <tr>
                <th class="px-5 py-3 text-start">{{ __('admin.common.name') }}</th>
                <th class="px-5 py-3 text-start">{{ __('admin.common.phone') }}</th>
                <th class="px-5 py-3 text-start">{{ __('admin.common.type') }}</th>
                <th class="px-5 py-3 text-start">{{ __('admin.common.status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($recentUsers as $user)
            <tr>
                <td class="px-5 py-3">{{ $user->username }}</td>
                <td class="px-5 py-3">{{ $user->phone }}</td>
                <td class="px-5 py-3">{{ $user->type === 'employee' ? __('admin.users.employee') : __('admin.users.customer') }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->status === 'active' ? __('admin.common.active') : __('admin.common.inactive') }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
