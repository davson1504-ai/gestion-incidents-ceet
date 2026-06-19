@props([
    'type' => null,
    'message' => null,
])

@php
    $messages = collect();

    if ($message) {
        $messages->push(['type' => $type ?: 'info', 'text' => $message]);
    }

    foreach (['success', 'status', 'info', 'warning', 'error', 'danger'] as $sessionKey) {
        if (session()->has($sessionKey)) {
            $messages->push([
                'type' => in_array($sessionKey, ['error', 'danger'], true) ? 'danger' : $sessionKey,
                'text' => session($sessionKey),
            ]);
        }
    }

    if ($errors->any()) {
        $messages->push([
            'type' => 'danger',
            'text' => $errors->first(),
        ]);
    }
@endphp

@if ($messages->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'ceet-flash-stack']) }}>
        @foreach ($messages as $entry)
            @php
                $resolvedType = $entry['type'] === 'status' ? 'info' : $entry['type'];
                $icon = match ($resolvedType) {
                    'success' => 'check_circle',
                    'warning' => 'warning',
                    'danger', 'error' => 'error',
                    default => 'info',
                };
            @endphp

            <div class="ceet-alert ceet-alert-{{ $resolvedType }}" role="alert">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
                <span>{{ $entry['text'] }}</span>
            </div>
        @endforeach
    </div>
@endif
