@props([
    'width' => 'w-full',
    'height' => 'h-3',
    'rounded' => 'rounded',
    'dark' => false,
])

@php
    $base = $dark ? 'skeleton-dark' : 'skeleton';
@endphp

<span
    {{ $attributes->except(['class'])->merge(['class' => "$base $width $height $rounded block"]) }}
    aria-hidden="true"
></span>
