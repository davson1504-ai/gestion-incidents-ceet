<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">

        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            ⚡ CEET
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}"
                       href="{{ route('dashboard') }}">
                        📊 Dashboard
                    </a>
                </li>

                {{-- Incidents --}}
                @can('incidents.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('incidents.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('incidents.index') }}">
                        🚨 Incidents
                    </a>
                </li>
                @endcan

                {{-- Historique --}}
                @role('Administrateur|Superviseur')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('historique.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('historique.index') }}">
                        📋 Historique
                    </a>
                </li>
                @endrole

                {{-- Catalogues : 3 liens directs, aucun JS nécessaire --}}
                @can('catalogues.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('catalogues.departements.index') }}">
                        🏗️ Départs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalogues.types.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('catalogues.types.index') }}">
                        🏷️ Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active fw-semibold' : '' }}"
                       href="{{ route('catalogues.causes.index') }}">
                        🔍 Causes
                    </a>
                </li>
                @endcan

            </ul>

            {{-- Compte utilisateur --}}
            @auth
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <span class="text-white-50 small">
                        👤 {{ Auth::user()->name }}
                        @foreach(Auth::user()->getRoleNames() as $role)
                            <span class="badge bg-secondary ms-1">{{ $role }}</span>
                        @endforeach
                    </span>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                       href="{{ route('profile.edit') }}">
                        ⚙️ Profil
                    </a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            🚪 Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>