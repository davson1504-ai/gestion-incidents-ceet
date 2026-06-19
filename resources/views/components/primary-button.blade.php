@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-primary ceet-btn ceet-btn-primary']) }}>
    {{ $slot }}
</button>
