@props([
    'active' => false,
    'href' => '#',
])

@php
    $classes = $active
        ? 'dropdown-item ceet-responsive-nav-link active fw-semibold'
        : 'dropdown-item ceet-responsive-nav-link';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="page" @endif>
    {{ $slot }}
</a>
