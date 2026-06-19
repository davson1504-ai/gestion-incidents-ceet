@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'ceet-alert ceet-alert-success alert alert-success mb-3']) }} role="status">
        {{ $status }}
    </div>
@endif
