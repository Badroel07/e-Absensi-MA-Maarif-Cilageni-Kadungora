@props([
    'size' => 'w-10 h-10',
    'dark' => false,
])

@php
    $base = $dark ? 'skeleton-dark' : 'skeleton';
@endphp

<span
    {{ $attributes->merge(['class' => "$base rounded-full $size block shrink-0"]) }}
    aria-hidden="true"
></span>
