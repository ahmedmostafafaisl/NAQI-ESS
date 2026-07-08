@php $u = $user ?? null; @endphp

@if ($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        {{ $errors->first() }}
    </div>
@endif

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.users.username') }}</label>
    <input type="text" name="username" value="{{ old('username', $u->username ?? '') }}" required
           class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $u->email ?? '') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.phone') }}</label>
        <input type="text" name="phone" value="{{ old('phone', $u->phone ?? '') }}" required
               class="w-full rounded-lg border border-slate-300 px-4 py-2">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">
        {{ __('admin.common.password') }} {{ $u ? '('.__('admin.users.password_hint').')' : '' }}
    </label>
    <input type="password" name="password" {{ $u ? '' : 'required' }}
           class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>
<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.type') }}</label>
        <select name="type" class="w-full rounded-lg border border-slate-300 px-4 py-2">
            <option value="employee" @selected(old('type', $u->type ?? '')==='employee')>{{ __('admin.users.employee') }}</option>
            <option value="customer" @selected(old('type', $u->type ?? '')==='customer')>{{ __('admin.users.customer') }}</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.status') }}</label>
        <select name="status" class="w-full rounded-lg border border-slate-300 px-4 py-2">
            <option value="active" @selected(old('status', $u->status ?? 'active')==='active')>{{ __('admin.common.active') }}</option>
            <option value="inactive" @selected(old('status', $u->status ?? '')==='inactive')>{{ __('admin.common.inactive') }}</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.common.role') }}</label>
        <select name="role" class="w-full rounded-lg border border-slate-300 px-4 py-2">
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $u?->getRoleNames()->first() ?? '')===$role)>{{ $role }}</option>
            @endforeach
        </select>
    </div>
</div>
