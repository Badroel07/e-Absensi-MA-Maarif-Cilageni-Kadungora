@props([
    'height' => 'h-24',
    'rounded' => 'rounded-xl',
    'dark' => false,
])

@php
    $base = $dark ? 'skeleton-dark' : 'skeleton';
@endphp

<span
    {{ $attributes->merge(['class' => "$base $height $rounded block w-full"]) }}
    aria-hidden="true"
></span>
