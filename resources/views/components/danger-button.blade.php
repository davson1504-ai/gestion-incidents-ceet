@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-danger ceet-btn ceet-btn-danger']) }}>
    {{ $slot }}
</button>
