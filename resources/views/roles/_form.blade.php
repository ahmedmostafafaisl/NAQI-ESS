@if ($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        {{ $errors->first() }}
    </div>
@endif

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.roles.name') }}</label>
    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
           class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-2">{{ __('admin.roles.permissions') }}</label>
    <div class="space-y-4">
        @foreach($permissions as $group => $items)
        <div>
            <p class="text-xs font-semibold uppercase text-slate-400 mb-1">{{ $group }}</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach($items as $permission)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                        @checked(in_array($permission->name, old('permissions', $rolePermissions ?? [])))>
                    {{ $permission->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
