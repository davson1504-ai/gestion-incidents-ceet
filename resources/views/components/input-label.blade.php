@props([
    'value' => null,
    'for' => null,
])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'form-label ceet-input-label']) }}>
    {{ $value ?? $slot }}
</label>
