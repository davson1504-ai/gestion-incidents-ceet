{{--
    CEET — Topbar commune (app-topbar)
    Identique visuellement pour tous les rôles.
    Contient : recherche globale, notifications, aide, utilisateur.
--}}
@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $user     = auth()->user();
    $fullName = trim((string) ($user?->name ?? 'Utilisateur'));

    $parts    = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($parts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'CE';

    $unreadCount = (int) ($user?->unreadNotifications()->count() ?? 0);

    $searchRoute = RouteFacade::has('incidents.index') ? route('incidents.index') : '#';
@endphp

<header class="ceet-topbar" id="ceet-topbar" role="banner">

    {{-- Bouton hamburger (mobile) --}}
    <button
        type="button"
        class="ceet-topbar-menu-btn"
        id="ceet-sidebar-toggle"
        aria-label="Ouvrir le menu de navigation"
        aria-expanded="false"
        aria-controls="ceet-sidebar"
    >
        <span class="material-symbols-outlined" aria-hidden="true">menu</span>
    </button>

    {{-- Barre de recherche globale --}}
    <form action="{{ $searchRoute }}" method="GET" class="ceet-topbar-search" role="search">
        <span class="material-symbols-outlined ceet-topbar-search-icon" aria-hidden="true">search</span>
        <input
            type="search"
            name="q"
            placeholder="Rechercher un incident..."
            autocomplete="off"
            aria-label="Rechercher"
            value="{{ request('q') }}"
        >
    </form>

    {{-- Actions droite --}}
    <div class="ceet-topbar-actions">

        {{-- Notifications --}}
        <button
            type="button"
            class="ceet-topbar-icon-btn"
            data-ceet-notification-trigger
            data-notifications-url="{{ RouteFacade::has('notifications.index') ? route('notifications.index') : '/notifications' }}"
            data-notifications-count-url="{{ RouteFacade::has('notifications.count') ? route('notifications.count') : '/notifications/count' }}"
            data-notifications-read-all-url="{{ RouteFacade::has('notifications.read-all') ? route('notifications.read-all') : '/notifications/read-all' }}"
            data-notifications-read-url-template="{{ url('/notifications/__ID__/read') }}"
            aria-label="Notifications{{ $unreadCount > 0 ? ' (' . $unreadCount . ' non lues)' : '' }}"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            <span
                class="ceet-global-notification-badge"
                data-ceet-notification-count
                aria-live="polite"
                @if($unreadCount < 1) hidden @endif
            >{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        </button>

        {{-- Aide / Profil --}}
        <a
            href="{{ RouteFacade::has('profile.edit') ? route('profile.edit') : '#' }}"
            class="ceet-topbar-icon-btn"
            aria-label="Aide et profil"
        >
            <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
        </a>

        {{-- Séparateur --}}
        <div class="ceet-topbar-divider" aria-hidden="true"></div>

        {{-- Utilisateur --}}
        <div class="ceet-topbar-user">
            <span class="ceet-topbar-user-name">{{ $fullName }}</span>
            <div class="ceet-topbar-avatar" aria-hidden="true">{{ $initials }}</div>
        </div>

    </div>

</header>
