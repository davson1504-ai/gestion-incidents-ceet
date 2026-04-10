<style>
    .ceet-sidebar {
        width: var(--ceet-sidebar-width);
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1030;
    }

    .ceet-sidebar .nav-link {
        color: rgba(255, 255, 255, 0.9);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
    }

    .ceet-sidebar .nav-link:hover {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.08);
    }

    .ceet-sidebar .nav-link.active {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.16);
    }

    .ceet-sidebar .submenu-link {
        font-size: 0.9rem;
        margin-left: 0.35rem;
    }

    .ceet-mobile-topbar {
        position: sticky;
        top: 0;
        z-index: 1025;
    }

</style>

<aside class="ceet-sidebar navbar-ceet text-white shadow-sm d-none d-lg-flex flex-column vh-100">
    <div class="p-3 border-bottom border-light border-opacity-25">
        <a class="navbar-brand fw-semibold mb-0" href="{{ route('dashboard') }}">CEET Incidents</a>
    </div>

    <div class="flex-grow-1 overflow-auto p-3">
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                    Dashboard
                </a>
            </li>

            @can('incidents.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('incidents.*') ? 'active fw-semibold' : '' }}" href="{{ route('incidents.index') }}">
                        Incidents
                    </a>
                </li>
            @endcan

            @role('Administrateur|Superviseur')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('historique.*') ? 'active fw-semibold' : '' }}" href="{{ route('historique.index') }}">
                        Historique
                    </a>
                </li>
            @endrole

            @can('catalogues.view')
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold' : '' }}" data-bs-toggle="collapse" href="#desktopCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="desktopCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="desktopCatalogueMenu">
                        <ul class="nav flex-column mt-1 gap-1">
                            <li><a class="nav-link submenu-link {{ request()->routeIs('catalogues.departements.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.departements.index') }}">Départements</a></li>
                            <li><a class="nav-link submenu-link {{ request()->routeIs('catalogues.types.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.types.index') }}">Types d'incidents</a></li>
                            <li><a class="nav-link submenu-link {{ request()->routeIs('catalogues.causes.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.causes.index') }}">Causes</a></li>
                            <li><a class="nav-link submenu-link {{ request()->routeIs('catalogues.statuts.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.statuts.index') }}">Statuts</a></li>
                            <li><a class="nav-link submenu-link {{ request()->routeIs('catalogues.priorites.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.priorites.index') }}">Priorités</a></li>
                        </ul>
                    </div>
                </li>
            @endcan

            @can('users.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}" href="{{ route('users.index') }}">
                        Utilisateurs
                    </a>
                </li>
            @endcan
        </ul>
    </div>

    @auth
        <div class="p-3 border-top border-light border-opacity-25">
            <div class="small text-white-50 mb-2 text-truncate">{{ auth()->user()->name }}</div>
            <div class="d-grid gap-2">
                <a class="btn btn-sm btn-outline-light" href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger w-100">Deconnexion</button>
                </form>
            </div>
        </div>
    @endauth
</aside>

<nav class="navbar navbar-expand-lg navbar-dark navbar-ceet shadow-sm d-lg-none ceet-mobile-topbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">CEET Incidents</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header navbar-ceet text-white">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">CEET Incidents</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
            @can('incidents.view')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('incidents.*') ? 'active fw-semibold' : '' }}" href="{{ route('incidents.index') }}">Incidents</a></li>
            @endcan
            @role('Administrateur|Superviseur')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('historique.*') ? 'active fw-semibold' : '' }}" href="{{ route('historique.index') }}">Historique</a></li>
            @endrole
            @can('catalogues.view')
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold' : '' }}" data-bs-toggle="collapse" href="#mobileCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="mobileCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="mobileCatalogueMenu">
                        <ul class="nav flex-column mt-1 gap-1 ms-2">
                            <li><a class="nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.departements.index') }}">Départements</a></li>
                            <li><a class="nav-link {{ request()->routeIs('catalogues.types.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.types.index') }}">Types d'incidents</a></li>
                            <li><a class="nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.causes.index') }}">Causes</a></li>
                            <li><a class="nav-link {{ request()->routeIs('catalogues.statuts.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.statuts.index') }}">Statuts</a></li>
                            <li><a class="nav-link {{ request()->routeIs('catalogues.priorites.*') ? 'active fw-semibold' : '' }}" href="{{ route('catalogues.priorites.index') }}">Priorités</a></li>
                        </ul>
                    </div>
                </li>
            @endcan
            @can('users.view')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}" href="{{ route('users.index') }}">Utilisateurs</a></li>
            @endcan
        </ul>

        @auth
            <hr class="my-3">
            <div class="small text-muted mb-2">{{ auth()->user()->name }}</div>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm w-100">Deconnexion</button>
                </form>
            </div>
        @endauth
    </div>
</div>
