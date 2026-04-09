<nav class="navbar navbar-expand-lg navbar-dark navbar-ceet shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">CEET Incidents</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('catalogues.*') ? 'active fw-semibold' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Catalogues
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('catalogues.departements.index') }}">Departements</a></li>
                            <li><a class="dropdown-item" href="{{ route('catalogues.types.index') }}">Types incidents</a></li>
                            <li><a class="dropdown-item" href="{{ route('catalogues.causes.index') }}">Causes</a></li>
                            <li><a class="dropdown-item" href="{{ route('catalogues.statuts.index') }}">Statuts</a></li>
                            <li><a class="dropdown-item" href="{{ route('catalogues.priorites.index') }}">Priorites</a></li>
                        </ul>
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

            @auth
                <div class="d-flex align-items-center gap-2 text-white-50">
                    <span class="small">{{ auth()->user()->name }}</span>
                    <a class="btn btn-sm btn-outline-light" href="{{ route('profile.edit') }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">Deconnexion</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
