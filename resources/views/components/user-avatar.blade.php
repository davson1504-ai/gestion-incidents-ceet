{{--
    CEET — Avatar utilisateur avec initiales
    
    Utilisation :
    <x-user-avatar :user="$user" />
    <x-user-avatar name="Jean Dupont" size="lg" />
--}}
@props([
    'user' => null,
    'name' => null,
    'size' => 'md',   // sm | md | lg
])

@php
    $displayName = $name ?? ($user?->name ?? 'Utilisateur');
    $parts = preg_split('/\s+/', trim($displayName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($parts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'CE';

    $sizeClass = match($size) {
        'sm' => 'is-sm',
        'lg' => 'is-lg',
        default => '',
    };
@endphp

<span class="ceet-user-avatar {{ $sizeClass }}" aria-label="{{ $displayName }}" title="{{ $displayName }}">
    {{ $initials }}
</span>
