{{--
    CEET — Sidebar commune (app-sidebar)
    Affiche le bon menu selon le rôle connecté.
    Les données (user, rôle, initiales) sont calculées ici.
--}}
@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $user       = auth()->user();
    $fullName   = trim((string) ($user?->name ?? 'Utilisateur'));
    $roleName   = ($user && method_exists($user, 'getRoleNames'))
                    ? (string) ($user->getRoleNames()->first() ?? 'Utilisateur')
                    : 'Utilisateur';

    $parts    = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($parts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'CE';

    // Helpers
    $safeRoute = fn (string $name, array $params = []) =>
        RouteFacade::has($name) ? route($name, $params) : '#';

    $isActive = fn (string $name) =>
        RouteFacade::is($name) || request()->routeIs($name);

    // Rôles
    $isAdmin      = $user?->isAdmin() ?? false;
    $isSupervisor = $user?->isSuperviseur() ?? false;
    $isOperator   = $user?->isOperateur() ?? false;
@endphp

<aside class="ceet-sidebar" id="ceet-sidebar" aria-label="Navigation principale">

    {{-- Brand --}}
    <div class="ceet-sidebar-brand">
        <img
            src="{{ asset('images/logo-ceet.png') }}"
            alt="Logo CEET"
            class="ceet-sidebar-logo"
            onerror="this.style.display='none'"
        >
        <div class="ceet-sidebar-brand-text">
            <strong>CEET Incidents</strong>
            <span>Gestion des incidents électriques</span>
        </div>
    </div>

    {{-- Navigation principale --}}
    <nav class="ceet-sidebar-nav" aria-label="Menu principal">

        {{-- ── Tableau de bord ───────────────────────────────── --}}
        <a href="{{ $safeRoute('dashboard') }}"
           class="ceet-sidebar-link {{ $isActive('dashboard') ? 'is-active' : '' }}"
           @if($isActive('dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
            <span class="ceet-sidebar-link-label">Tableau de bord</span>
        </a>

        {{-- ── Menu Admin ────────────────────────────────────── --}}
        @if($isAdmin)

            <a href="{{ $safeRoute('incidents.index') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.*') ? 'is-active' : '' }}"
               @if($isActive('incidents.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">bolt</span>
                <span class="ceet-sidebar-link-label">Tous les incidents</span>
            </a>

            <a href="{{ $safeRoute('users.index') }}"
               class="ceet-sidebar-link {{ $isActive('users.*') ? 'is-active' : '' }}"
               @if($isActive('users.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">group</span>
                <span class="ceet-sidebar-link-label">Utilisateurs</span>
            </a>

            <a href="{{ $safeRoute('system.status') }}"
               class="ceet-sidebar-link {{ $isActive('system.status') ? 'is-active' : '' }}"
               @if($isActive('system.status')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">monitor_heart</span>
                <span class="ceet-sidebar-link-label">Statut système</span>
            </a>

            <a href="{{ $safeRoute('catalogues.index') }}"
               class="ceet-sidebar-link {{ $isActive('catalogues.*') ? 'is-active' : '' }}"
               @if($isActive('catalogues.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                <span class="ceet-sidebar-link-label">Catalogues</span>
            </a>

            <a href="{{ $safeRoute('reports.index') }}"
               class="ceet-sidebar-link {{ $isActive('reports.*') ? 'is-active' : '' }}"
               @if($isActive('reports.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
                <span class="ceet-sidebar-link-label">Rapports</span>
            </a>

            <a href="{{ $safeRoute('historique.index') }}"
               class="ceet-sidebar-link {{ $isActive('historique.*') ? 'is-active' : '' }}"
               @if($isActive('historique.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">history</span>
                <span class="ceet-sidebar-link-label">Historique</span>
            </a>

        {{-- ── Menu Superviseur ──────────────────────────────── --}}
        @elseif($isSupervisor)

            <a href="{{ $safeRoute('incidents.index') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.index') || $isActive('incidents.show') || $isActive('incidents.edit') ? 'is-active' : '' }}"
               @if($isActive('incidents.index')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">bolt</span>
                <span class="ceet-sidebar-link-label">Tous les incidents</span>
            </a>

            <a href="{{ $safeRoute('incidents.en-cours') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.en-cours') ? 'is-active' : '' }}"
               @if($isActive('incidents.en-cours')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">work_history</span>
                <span class="ceet-sidebar-link-label">Suivi en cours</span>
            </a>

            <a href="{{ $safeRoute('incidents.create') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.create') ? 'is-active' : '' }}"
               @if($isActive('incidents.create')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
                <span class="ceet-sidebar-link-label">Déclarer un incident</span>
            </a>

            <a href="{{ $safeRoute('reports.index') }}"
               class="ceet-sidebar-link {{ $isActive('reports.*') ? 'is-active' : '' }}"
               @if($isActive('reports.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
                <span class="ceet-sidebar-link-label">Rapports</span>
            </a>

        {{-- ── Menu Opérateur ────────────────────────────────── --}}
        @elseif($isOperator)

            <a href="{{ $safeRoute('incidents.mine') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.mine') ? 'is-active' : '' }}"
               @if($isActive('incidents.mine')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">assignment_ind</span>
                <span class="ceet-sidebar-link-label">Mes incidents</span>
            </a>

            <a href="{{ $safeRoute('incidents.en-cours') }}"
               class="ceet-sidebar-link {{ $isActive('incidents.en-cours') ? 'is-active' : '' }}"
               @if($isActive('incidents.en-cours')) aria-current="page" @endif>
                <span class="material-symbols-outlined" aria-hidden="true">work_history</span>
                <span class="ceet-sidebar-link-label">Suivi en cours</span>
            </a>

        @endif

        {{-- Profil : commun à tous les rôles --}}
        <hr class="ceet-sidebar-separator">

        <a href="{{ $safeRoute('profile.edit') }}"
           class="ceet-sidebar-link {{ $isActive('profile.*') ? 'is-active' : '' }}"
           @if($isActive('profile.*')) aria-current="page" @endif>
            <span class="material-symbols-outlined" aria-hidden="true">manage_accounts</span>
            <span class="ceet-sidebar-link-label">Mon profil</span>
        </a>

    </nav>

    {{-- Bloc utilisateur + déconnexion --}}
    <div class="ceet-sidebar-footer">
        <div class="ceet-sidebar-user-info">
            <span class="ceet-sidebar-avatar" aria-hidden="true">{{ $initials }}</span>
            <div class="ceet-sidebar-user-details">
                <span class="ceet-sidebar-user-name">{{ $fullName }}</span>
                <span class="ceet-sidebar-user-role">{{ $roleName }}</span>
            </div>
        </div>

        <form method="POST"
              action="{{ RouteFacade::has('logout') ? route('logout') : '#' }}"
              id="ceet-logout-form">
            @csrf
            <button type="submit" class="ceet-sidebar-logout">
                <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                Se déconnecter
            </button>
        </form>
    </div>

</aside>
