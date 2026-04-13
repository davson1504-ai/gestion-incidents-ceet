@php
    use Illuminate\Support\Str;

    $catalogueOpen = request()->routeIs('catalogues.*');
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Utilisateur';
    $userInitials = strtoupper(Str::substr($currentUser?->name ?? 'CEET', 0, 2));
@endphp

<style>
    .ceet-sidebar {
        width: var(--ceet-sidebar-width);
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1030;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e5e7eb;
        background: #f8f9fb;
    }

    .ceet-sidebar-brand {
        min-height: var(--ceet-topbar-height);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        color: #ef2433;
        text-decoration: none;
    }

    .ceet-sidebar-brand:hover {
        color: #ef2433;
    }

    .ceet-sidebar-brand-badge {
        width: 30px;
        height: 30px;
        border-radius: 0.5rem;
        background: #ef2433;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .ceet-sidebar-brand-text {
        font-size: 1.05rem;
        font-weight: 700;
    }

    .ceet-sidebar-menu {
        padding: 1rem;
        display: grid;
        gap: 0.3rem;
    }

    .ceet-nav-link {
        border-radius: 0.65rem;
        border: 1px solid transparent;
        color: #1f2937;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.72rem 0.85rem;
        font-weight: 500;
        background: transparent;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .ceet-nav-link:hover {
        background: #f1f5f9;
        color: #111827;
    }

    .ceet-nav-link.active {
        background: #eceff3;
        color: #111827;
        font-weight: 700;
    }

    .ceet-nav-icon {
        width: 18px;
        height: 18px;
        color: currentColor;
        flex-shrink: 0;
    }

    /* Keep the parent Catalogue trigger visually stable across all child routes. */
    .ceet-catalogue-toggle.is-catalogue-current,
    .ceet-catalogue-toggle[aria-expanded="true"] {
        background: #fff5f5;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .ceet-catalogue-toggle .ceet-chevron {
        margin-left: auto;
        transition: transform 0.18s ease;
    }

    .ceet-catalogue-toggle[aria-expanded="true"] .ceet-chevron {
        transform: rotate(180deg);
    }

    .ceet-catalogue-menu {
        padding-top: 0.15rem;
        display: grid;
        gap: 0.2rem;
    }

    .ceet-catalogue-menu .ceet-nav-link {
        padding-left: 3rem;
        font-size: 0.92rem;
    }

    .ceet-sidebar-footer {
        margin-top: auto;
        border-top: 1px solid #e5e7eb;
        padding: 1rem;
    }

    .ceet-sidebar-action {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        color: #111827;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        padding: 0.72rem 0.95rem;
    }

    .ceet-sidebar-action:hover {
        background: #f8fafc;
        color: #111827;
    }

    .ceet-topbar {
        position: fixed;
        top: 0;
        right: 0;
        left: var(--ceet-sidebar-width);
        z-index: 1025;
        min-height: var(--ceet-topbar-height);
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.9rem;
    }

    .ceet-topbar-icon-btn {
        border: 1px solid transparent;
        border-radius: 0.5rem;
        background: #fff;
        color: #111827;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .ceet-topbar-icon-btn:hover {
        border-color: #e5e7eb;
        background: #f8fafc;
        color: #111827;
    }

    .ceet-user-chip {
        border-left: 1px solid #e5e7eb;
        padding-left: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .ceet-user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(140deg, #f8d64f 0%, #f5a623 100%);
        color: #3b2f18;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .ceet-mobile-topbar {
        position: fixed;
        inset: 0 0 auto 0;
        z-index: 1040;
        min-height: var(--ceet-topbar-height);
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .ceet-mobile-brand {
        color: #ef2433;
        text-decoration: none;
        font-weight: 700;
    }

    .ceet-mobile-brand:hover {
        color: #ef2433;
    }

    .ceet-mobile-offcanvas {
        background: #f8f9fb;
    }

    .ceet-mobile-offcanvas .offcanvas-header {
        min-height: var(--ceet-topbar-height);
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .ceet-mobile-offcanvas .offcanvas-title {
        color: #ef2433;
        font-weight: 700;
    }

    .ceet-mobile-menu {
        display: grid;
        gap: 0.3rem;
    }

    .ceet-mobile-menu .ceet-nav-link {
        background: transparent;
    }

    @media (max-width: 991.98px) {
        .ceet-topbar {
            display: none;
        }

        .ceet-mobile-topbar {
            display: flex;
        }
    }
</style>

<aside class="ceet-sidebar d-none d-lg-flex">
    <a class="ceet-sidebar-brand" href="{{ route('dashboard') }}">
        <span class="ceet-sidebar-brand-badge">~</span>
        <span class="ceet-sidebar-brand-text">CEET Gestion des Incidents</span>
    </a>

    <nav class="ceet-sidebar-menu">
        <a class="ceet-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
            <span>Tableau de bord</span>
        </a>

        @can('incidents.view')
            <a class="ceet-nav-link {{ request()->routeIs('incidents.index', 'incidents.show', 'incidents.create', 'incidents.edit') ? 'active' : '' }}" href="{{ route('incidents.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Incidents</span>
            </a>
            <a class="ceet-nav-link {{ request()->routeIs('incidents.en-cours') ? 'active' : '' }}" href="{{ route('incidents.en-cours') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Incidents en cours</span>
            </a>
            <a class="ceet-nav-link {{ request()->routeIs('incidents.mine') ? 'active' : '' }}" href="{{ route('incidents.mine') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 12A4 4 0 1 0 12 4A4 4 0 0 0 12 12ZM5 20A7 7 0 0 1 19 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Mes incidents</span>
            </a>
        @endcan

        @can('catalogues.view')
            <a @class([
                'ceet-nav-link',
                'ceet-catalogue-toggle',
                'active fw-semibold is-catalogue-current' => $catalogueOpen,
            ])
               data-bs-toggle="collapse"
               href="#desktopCatalogueMenu"
               role="button"
               aria-expanded="{{ $catalogueOpen ? 'true' : 'false' }}"
               aria-controls="desktopCatalogueMenu">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Catalogue</span>
                <svg class="ceet-nav-icon ceet-chevron" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <div class="collapse {{ $catalogueOpen ? 'show' : '' }}" id="desktopCatalogueMenu">
                <div class="ceet-catalogue-menu">
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}" href="{{ route('catalogues.departements.index') }}">Departements</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}" href="{{ route('catalogues.types.index') }}">Types d'incidents</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}" href="{{ route('catalogues.causes.index') }}">Causes</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}" href="{{ route('catalogues.statuts.index') }}">Statuts</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}" href="{{ route('catalogues.priorites.index') }}">Priorites</a>
                </div>
            </div>
        @endcan

        @can('incidents.view')
            <a class="ceet-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M5 19V5M10 19V9M15 19V13M20 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span>Reporting</span>
            </a>
        @endcan

        @role('Administrateur|Superviseur')
            <a class="ceet-nav-link {{ request()->routeIs('historique.*') ? 'active' : '' }}" href="{{ route('historique.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M3 12A9 9 0 1 0 6 5.3M3 4V9H8M12 7V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Audit</span>
            </a>
        @endrole

        @can('users.view')
            <a class="ceet-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M16 19V17A4 4 0 0 0 12 13H8A4 4 0 0 0 4 17V19M20 19V17A4 4 0 0 0 17 13.1M12 5A3 3 0 1 1 12 11A3 3 0 0 1 12 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Utilisateurs</span>
            </a>
        @endcan

        <a class="ceet-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
            <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 0 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 0 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.38A1.65 1.65 0 0 0 7.1 19.71L7.04 19.77A2 2 0 1 1 4.21 16.94L4.27 16.88A1.65 1.65 0 0 0 4.6 15.06A1.65 1.65 0 0 0 3.07 13.98H3A2 2 0 0 1 3 9.98H3.09A1.65 1.65 0 0 0 4.62 8.9A1.65 1.65 0 0 0 4.29 7.08L4.23 7.02A2 2 0 1 1 7.06 4.19L7.12 4.25A1.65 1.65 0 0 0 8.94 4.58H9A1.65 1.65 0 0 0 10.06 3.05V3A2 2 0 1 1 14.06 3V3.09A1.65 1.65 0 0 0 15.14 4.62A1.65 1.65 0 0 0 16.96 4.29L17.02 4.23A2 2 0 1 1 19.85 7.06L19.79 7.12A1.65 1.65 0 0 0 19.46 8.94V9A1.65 1.65 0 0 0 21 10.06H21.09A2 2 0 0 1 21.09 14.06H21A1.65 1.65 0 0 0 19.47 15.14Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Reglages</span>
        </a>
    </nav>

    @can('incidents.create')
        <div class="ceet-sidebar-footer">
            <a href="{{ route('incidents.create') }}" class="ceet-sidebar-action">
                <span>+</span>
                <span>Nouvel Incident</span>
            </a>
        </div>
    @endcan
</aside>

<header class="ceet-topbar d-none d-lg-flex">
    <button class="ceet-topbar-icon-btn" type="button" aria-label="Notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 17H20L18.6 15.6C18.2 15.2 18 14.7 18 14.2V10.5C18 7.4 15.9 4.8 13 4V3.5A1.5 1.5 0 0 0 10 3.5V4C7.1 4.8 5 7.4 5 10.5V14.2C5 14.7 4.8 15.2 4.4 15.6L3 17H8M9 17C9 18.7 10.3 20 12 20C13.7 20 15 18.7 15 17H9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    @auth
        <div class="ceet-user-chip">
            <div class="text-end">
                <div class="fw-bold lh-1">{{ $currentUser?->name ?? 'Utilisateur CEET' }}</div>
                <small class="text-muted">{{ $roleName }}</small>
            </div>
            <span class="ceet-user-avatar">{{ $userInitials }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="ceet-topbar-icon-btn" aria-label="Se deconnecter">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9M16 17L21 12L16 7M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    @endauth
</header>

<header class="ceet-mobile-topbar">
    <button class="ceet-topbar-icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Afficher la navigation">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
    <a class="ceet-mobile-brand" href="{{ route('dashboard') }}">CEET Incidents</a>
    <button class="ceet-topbar-icon-btn" type="button" aria-label="Notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 17H20L18.6 15.6C18.2 15.2 18 14.7 18 14.2V10.5C18 7.4 15.9 4.8 13 4V3.5A1.5 1.5 0 0 0 10 3.5V4C7.1 4.8 5 7.4 5 10.5V14.2C5 14.7 4.8 15.2 4.4 15.6L3 17H8M9 17C9 18.7 10.3 20 12 20C13.7 20 15 18.7 15 17H9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
</header>

<div class="offcanvas offcanvas-start ceet-mobile-offcanvas d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">CEET Incidents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <nav class="ceet-mobile-menu">
            <a class="ceet-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
                <span>Tableau de bord</span>
            </a>

            @can('incidents.view')
                <a class="ceet-nav-link {{ request()->routeIs('incidents.index', 'incidents.show', 'incidents.create', 'incidents.edit') ? 'active' : '' }}" href="{{ route('incidents.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Incidents</span>
                </a>
                <a class="ceet-nav-link {{ request()->routeIs('incidents.en-cours') ? 'active' : '' }}" href="{{ route('incidents.en-cours') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Incidents en cours</span>
                </a>
                <a class="ceet-nav-link {{ request()->routeIs('incidents.mine') ? 'active' : '' }}" href="{{ route('incidents.mine') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 12A4 4 0 1 0 12 4A4 4 0 0 0 12 12ZM5 20A7 7 0 0 1 19 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Mes incidents</span>
                </a>
            @endcan

            @can('catalogues.view')
                <a @class([
                    'ceet-nav-link',
                    'ceet-catalogue-toggle',
                    'active fw-semibold is-catalogue-current' => $catalogueOpen,
                ])
                   data-bs-toggle="collapse"
                   href="#mobileCatalogueMenu"
                   role="button"
                   aria-expanded="{{ $catalogueOpen ? 'true' : 'false' }}"
                   aria-controls="mobileCatalogueMenu">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Catalogue</span>
                    <svg class="ceet-nav-icon ceet-chevron" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>

                <div class="collapse {{ $catalogueOpen ? 'show' : '' }}" id="mobileCatalogueMenu">
                    <div class="ceet-catalogue-menu">
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}" href="{{ route('catalogues.departements.index') }}">Departements</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}" href="{{ route('catalogues.types.index') }}">Types d'incidents</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}" href="{{ route('catalogues.causes.index') }}">Causes</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}" href="{{ route('catalogues.statuts.index') }}">Statuts</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}" href="{{ route('catalogues.priorites.index') }}">Priorites</a>
                    </div>
                </div>
            @endcan

            @can('incidents.view')
                <a class="ceet-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M5 19V5M10 19V9M15 19V13M20 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Reporting</span>
                </a>
            @endcan

            @role('Administrateur|Superviseur')
                <a class="ceet-nav-link {{ request()->routeIs('historique.*') ? 'active' : '' }}" href="{{ route('historique.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M3 12A9 9 0 1 0 6 5.3M3 4V9H8M12 7V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Audit</span>
                </a>
            @endrole

            @can('users.view')
                <a class="ceet-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M16 19V17A4 4 0 0 0 12 13H8A4 4 0 0 0 4 17V19M20 19V17A4 4 0 0 0 17 13.1M12 5A3 3 0 1 1 12 11A3 3 0 0 1 12 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Utilisateurs</span>
                </a>
            @endcan

            <a class="ceet-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none"><path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 0 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 0 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.38A1.65 1.65 0 0 0 7.1 19.71L7.04 19.77A2 2 0 1 1 4.21 16.94L4.27 16.88A1.65 1.65 0 0 0 4.6 15.06A1.65 1.65 0 0 0 3.07 13.98H3A2 2 0 0 1 3 9.98H3.09A1.65 1.65 0 0 0 4.62 8.9A1.65 1.65 0 0 0 4.29 7.08L4.23 7.02A2 2 0 1 1 7.06 4.19L7.12 4.25A1.65 1.65 0 0 0 8.94 4.58H9A1.65 1.65 0 0 0 10.06 3.05V3A2 2 0 1 1 14.06 3V3.09A1.65 1.65 0 0 0 15.14 4.62A1.65 1.65 0 0 0 16.96 4.29L17.02 4.23A2 2 0 1 1 19.85 7.06L19.79 7.12A1.65 1.65 0 0 0 19.46 8.94V9A1.65 1.65 0 0 0 21 10.06H21.09A2 2 0 0 1 21.09 14.06H21A1.65 1.65 0 0 0 19.47 15.14Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Reglages</span>
            </a>
        </nav>

        @can('incidents.create')
            <div class="mt-auto pt-3">
                <a href="{{ route('incidents.create') }}" class="ceet-sidebar-action">
                    <span>+</span>
                    <span>Nouvel Incident</span>
                </a>
            </div>
        @endcan
    </div>
</div>
