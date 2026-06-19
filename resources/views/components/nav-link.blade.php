@props([
    'active' => false,
    'href' => '#',
])

@php
    $classes = $active
        ? 'nav-link ceet-nav-link active fw-semibold'
        : 'nav-link ceet-nav-link';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="page" @endif>
    {{ $slot }}
</a>
