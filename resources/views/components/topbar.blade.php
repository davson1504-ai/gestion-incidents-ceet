@props([
    'title' => null,
    'subtitle' => null,
    'topbarTitle' => null,
    'isAdmin' => false,
    'isOperator' => false,
    'currentUser' => null,
    'roleName' => null,
    'userInitials' => null,
    'navItems' => collect(),
    'iconPath' => [],
    'catalogueOpen' => false,
])

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $resolvedTitle = $title ?? $topbarTitle;
    $user = $currentUser ?? auth()->user();
    $displayName = $user?->name ?? 'Utilisateur';
    $initials = $userInitials ?: Str::upper(Str::substr($displayName, 0, 2));
@endphp

<header {{ $attributes->merge(['class' => 'ceet-topbar']) }}>
    <div class="ceet-topbar-title">
        @if($resolvedTitle)
            <strong>{{ $resolvedTitle }}</strong>
        @endif

        @if($subtitle)
            <span>{{ $subtitle }}</span>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="ceet-topbar-actions">
            {{ $actions }}
        </div>
    @else
        <div class="ceet-topbar-actions">
            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="ceet-topbar-icon-btn" aria-label="Notifications" data-ceet-notification-trigger>
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                <span class="ceet-topbar-notification-dot"></span>
            </a>

            <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="ceet-topbar-icon-btn" aria-label="Profil" data-ceet-link>
                <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
            </a>

            <div class="ceet-topbar-divider"></div>

            <div class="ceet-topbar-user">
                <span>{{ $displayName }}</span>
                <div class="ceet-topbar-avatar">{{ $initials }}</div>
            </div>
        </div>
    @endisset
</header>
