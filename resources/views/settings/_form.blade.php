@php $s = $setting ?? null; @endphp

@if ($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        {{ $errors->first() }}
    </div>
@endif

@if($s)
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.settings.key') }}</label>
        <input type="text" value="{{ $s->key }}" disabled
            class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-500 px-4 py-2">
        <p class="text-xs text-slate-400 mt-1">{{ __('admin.settings.key_immutable') }}</p>
    </div>
@else
    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.settings.key') }}</label>
        <input type="text" name="key" value="{{ old('key') }}" required placeholder="e.g. maintenance_mode"
            class="w-full rounded-lg border border-slate-300 px-4 py-2 font-mono text-sm">
        <p class="text-xs text-slate-400 mt-1">{{ __('admin.settings.key_hint') }}</p>
    </div>
@endif

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.settings.type') }}</label>
    <select name="type" id="type" class="w-full rounded-lg border border-slate-300 px-4 py-2">
        @foreach(['string', 'integer', 'boolean', 'json'] as $type)
            <option value="{{ $type }}" @selected(old('type', $s->type ?? 'string') === $type)>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.settings.value') }}</label>
    <textarea name="value" rows="3"
        class="w-full rounded-lg border border-slate-300 px-4 py-2 font-mono text-sm">{{ old('value', $s->value ?? '') }}</textarea>
    <p class="text-xs text-slate-400 mt-1">{{ __('admin.settings.value_hint') }}</p>
</div>

<div>
    <label class="block text-sm font-medium text-slate-600 mb-1">{{ __('admin.settings.description') }}</label>
    <input type="text" name="description" value="{{ old('description', $s->description ?? '') }}"
        class="w-full rounded-lg border border-slate-300 px-4 py-2">
</div>

<label class="flex items-center gap-2 text-sm text-slate-600">
    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $s->is_public ?? false))>
    {{ __('admin.settings.is_public_label') }}
</label>
