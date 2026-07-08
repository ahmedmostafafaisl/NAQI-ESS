@extends('layouts.app')
@section('title', 'Send Notification')

@section('content')
<form method="POST" action="{{ route('admin.notifications.store') }}" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl space-y-4">
    @csrf
    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Audience</label>
        <select name="audience" id="audience" class="w-full rounded-lg border border-slate-300 px-4 py-2">
            <option value="all">All Users</option>
            <option value="employees">Employees Only</option>
            <option value="customers">Customers Only</option>
            <option value="specific">Specific Users</option>
        </select>
    </div>

    <div id="specific-users" class="hidden">
        <label class="block text-sm font-medium text-slate-600 mb-1">Select Users</label>
        <select name="user_ids[]" multiple class="w-full rounded-lg border border-slate-300 px-4 py-2 h-40">
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->phone }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Title</label>
        <input type="text" name="title" required class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Message</label>
        <textarea name="body" rows="4" required class="w-full rounded-lg border border-slate-300 px-4 py-2"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Category</label>
        <input type="text" name="category" placeholder="system, leave, payslip..." class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>

    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm">Send Notification</button>
</form>

<script>
    document.getElementById('audience').addEventListener('change', function (e) {
        document.getElementById('specific-users').classList.toggle('hidden', e.target.value !== 'specific');
    });
</script>
@endsection
