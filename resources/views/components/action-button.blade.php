@props(['variant' => 'view', 'href' => null, 'type' => 'link'])

@php
    $variants = [
        'delete' => 'text-red-600 border-red-300 hover:bg-red-50',
        'edit' => 'text-blue-600 border-blue-300 hover:bg-blue-50',
        'view' => 'text-slate-600 border-slate-300 hover:bg-slate-50',
    ];
    $classes = 'inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold border transition whitespace-nowrap '
        . ($variants[$variant] ?? $variants['view']);
@endphp

@if($type === 'link')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
