@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Total Employees</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_employees'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Active Employees</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_employees'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Customers</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_customers'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
        <p class="text-slate-400 text-sm">Unread Notifications</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['unread_notifications'] }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 font-semibold text-slate-700">Recently Added Users</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Phone</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($recentUsers as $user)
            <tr>
                <td class="px-5 py-3">{{ $user->username }}</td>
                <td class="px-5 py-3">{{ $user->phone }}</td>
                <td class="px-5 py-3 capitalize">{{ $user->type }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
