@props([
    'type' => 'submit',
    'variant' => 'primary', // primary | secondary | danger
    'icon' => null,
    'label' => null,
    'loadingLabel' => 'Memproses...',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold text-xs rounded-xl transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 disabled:opacity-70 disabled:cursor-not-allowed';
    $variants = [
        'primary' => 'px-5 py-2.5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-sm focus-visible:ring-maarif-600',
        'secondary' => 'px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white focus-visible:ring-slate-700',
        'danger' => 'px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white focus-visible:ring-rose-500',
    ];
    $classes = ($variants[$variant] ?? $variants['primary']) . ' ' . $baseClasses;
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    x-bind:disabled="loading"
    x-bind:class="loading ? 'btn-loading' : ''"
>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="w-4 h-4" x-show="!loading"></i>
    @endif
    <span x-show="!loading">{{ $label ?? $slot }}</span>
    <span x-show="loading" x-cloak>{{ $loadingLabel }}</span>
</button>
