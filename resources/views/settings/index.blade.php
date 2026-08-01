@extends('layouts.app')
@section('title', __('admin.settings.title'))

@section('content')
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('admin.common.search') }}..."
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
            <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">{{ __('admin.common.filter') }}</button>
        </form>
        @can('settings.manage')
            <a href="{{ route('admin.settings.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                + {{ __('admin.settings.add') }}
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-start">{{ __('admin.settings.key') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.settings.value') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.settings.type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('admin.settings.visibility') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($settings as $setting)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs">{{ $setting->key }}</td>
                        <td class="px-5 py-3 max-w-xs truncate" title="{{ $setting->value }}">{{ $setting->value ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-600">{{ $setting->type }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($setting->is_public)
                                <span
                                    class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700">{{ __('admin.settings.public') }}</span>
                            @else
                                <span
                                    class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-500">{{ __('admin.settings.private') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-end">
                            <div class="flex items-center justify-end gap-2">
                                @can('settings.manage')
                                    <x-action-button variant="edit" :href="route('admin.settings.edit', $setting)">{{ __('admin.common.edit') }}</x-action-button>
                                    <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.common.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <x-action-button variant="delete"
                                            type="submit">{{ __('admin.common.delete') }}</x-action-button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-400">{{ __('admin.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $settings->links() }}</div>
@endsection
