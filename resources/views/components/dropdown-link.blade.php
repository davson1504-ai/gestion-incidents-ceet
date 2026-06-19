@props(['href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'dropdown-item ceet-dropdown-link']) }}>
    {{ $slot }}
</a>
