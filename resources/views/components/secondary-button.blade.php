@props(['type' => 'button'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-outline-secondary ceet-btn ceet-btn-secondary']) }}>
    {{ $slot }}
</button>
