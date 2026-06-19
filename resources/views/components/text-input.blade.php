@props([
    'disabled' => false,
    'type' => 'text',
])

<input
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'form-control ceet-text-input']) }}
>
