@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';
    $from = $users->firstItem() ?? 0;
    $to = $users->lastItem() ?? 0;
    $advancedOpen = filled($filters['role']) || filled($filters['departement_id']) || ($filters['is_active'] !== null && $filters['is_active'] !== '');
    $createModalOpen = collect(['name', 'prenom', 'nom_famille', 'email', 'telephone', 'role', 'departement_id', 'password'])->contains(fn ($field) => $errors->has($field));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Utilisateurs & Roles | CEET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--ceet-red:#ef2433;--ceet-red-dark:#ce1220;--ceet-bg:#f5f6f8;--ceet-border:#e5e7eb;--ceet-muted:#6b7280;--ceet-shadow:0 14px 32px rgba(15,23,42,.04)}
        html,body{margin:0;min-height:100%;background:var(--ceet-bg);color:#1f2937;font-family:"Figtree","Segoe UI",sans-serif}
        .page-shell{min-height:100dvh;display:grid;grid-template-columns:250px 1fr}
        .sidebar {border-right:1px solid var(--ceet-border);background:#f8f9fb;display:flex;flex-direction:column;
            position: sticky;
            top: 0;
            height: 100dvh;
            z-index: 1010;
        }
        .sidebar-brand{min-height:72px;padding:1rem 1.2rem;border-bottom:1px solid var(--ceet-border);display:flex;align-items:center;gap:.6rem;color:var(--ceet-red);font-weight:700}
        .sidebar-brand-badge{width:24px;height:24px;border-radius:.35rem;background:var(--ceet-red);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem}
        .sidebar-menu{padding:.8rem;display:grid;gap:.25rem}
        .sidebar-link{border-radius:.55rem;color:#1f2937;text-decoration:none;padding:.65rem .72rem;font-weight:500;font-size:.92rem}
        .sidebar-link:hover{background:#f1f5f9}.sidebar-link.active{background:#eceff3;font-weight:700}
        .sidebar-link.catalogue-toggle.active,
        .sidebar-link.catalogue-toggle[aria-expanded="true"] {
            background: #fff5f5;
            color: var(--ceet-red);
            font-weight: 700;
            box-shadow: inset 0 0 0 1px #fecaca;
        }

        .catalogue-toggle .catalogue-chevron {
            margin-left: auto;
            transition: transform 0.18s ease;
        }

        .catalogue-toggle[aria-expanded="true"] .catalogue-chevron {
            transform: rotate(180deg);
        }
        .sidebar-bottom{margin-top:auto;border-top:1px solid var(--ceet-border);padding:.8rem}
        .sidebar-create{width:100%;border:1px solid var(--ceet-border);border-radius:.62rem;background:#fff;color:#111827;font-weight:600;text-decoration:none;display:inline-flex;justify-content:center;gap:.5rem;padding:.62rem .8rem;font-size:.9rem}
        .main-area{min-width:0;display:flex;flex-direction:column;min-height:100dvh}
        .topbar {min-height:72px;border-bottom:1px solid var(--ceet-border);background:#fff;display:flex;align-items:center;justify-content:flex-end;gap:.9rem;padding:.8rem 1.2rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .icon-btn{border:1px solid transparent;border-radius:.5rem;background:#fff;color:#111827;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center}
        .icon-btn:hover{border-color:var(--ceet-border);background:#f8fafc}
        .user-chip{border-left:1px solid var(--ceet-border);padding-left:.8rem;display:inline-flex;align-items:center;gap:.7rem}
        .user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(140deg,#f8d64f 0%,#f5a623 100%);color:#3b2f18;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.88rem}
        .content-wrap{padding:1rem 1.3rem;display:grid;gap:.9rem}
        .head-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.9rem;flex-wrap:wrap}
        .head-title{margin:0;font-size:2.05rem;font-weight:800;line-height:1.1}
        .head-subtitle{margin-top:.2rem;color:#6b7280;font-size:.96rem}
        .btn-action{border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:.55rem;font-weight:600;padding:.52rem .84rem;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;font-size:.9rem}
        .btn-action.primary{border-color:var(--ceet-red);background:var(--ceet-red);color:#fff;font-weight:700}
        .btn-action.primary:hover{background:var(--ceet-red-dark);border-color:var(--ceet-red-dark);color:#fff}
        .kpi-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}
        .kpi-card,.filters-panel,.table-panel{border:1px solid var(--ceet-border);border-radius:.78rem;background:#fff;box-shadow:var(--ceet-shadow)}
        .kpi-card{padding:.75rem}.kpi-label{font-size:.76rem;font-weight:700;color:#6b7280;text-transform:uppercase}.kpi-value{font-size:2rem;font-weight:800;line-height:1}.kpi-sub{margin-top:.25rem;font-size:.82rem;color:#6b7280}
        .filters-panel{padding:.75rem}.filters-top{display:grid;grid-template-columns:1fr auto;gap:.55rem;align-items:center}
        .advanced-grid{margin-top:.55rem;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.6rem;align-items:end}
        .form-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.03em;font-weight:700;color:#6b7280;margin-bottom:.22rem}
        .form-control,.form-select{border-radius:.5rem;border-color:#d8dee5;font-size:.91rem;min-height:38px}
        .form-control:focus,.form-select:focus{border-color:#f6a6ae;box-shadow:0 0 0 .16rem rgba(239,36,51,.14)}
        .users-table{margin:0}.users-table thead th{font-size:.76rem;color:#6b7280;text-transform:uppercase;letter-spacing:.02em;border-bottom-color:#e5e7eb;background:#f9fafb}
        .users-table td{border-bottom-color:#edf2f7;vertical-align:middle;font-size:.88rem}
        .user-line{display:flex;align-items:center;gap:.5rem}.user-photo{width:34px;height:34px;border-radius:50%;background:linear-gradient(140deg,#dbeafe,#bfdbfe);color:#1e3a8a;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0}
        .user-name{margin:0;font-weight:700;color:#374151;font-size:.9rem}.user-email{margin:0;font-size:.78rem;color:#6b7280}
        .status-pill{border-radius:999px;padding:.12rem .52rem;font-size:.7rem;font-weight:700;display:inline-block;white-space:nowrap}
        .status-active{background:#ecfdf3;color:#15803d}.status-invite{background:#eff6ff;color:#1d4ed8}.status-off{background:#f3f4f6;color:#4b5563}
        .row-actions{display:inline-flex;gap:.32rem;justify-content:flex-end}
        .icon-action{width:28px;height:28px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#4b5563;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;padding:0}
        .icon-action:hover{border-color:var(--ceet-red);color:var(--ceet-red)}.icon-action:disabled{opacity:.55;cursor:not-allowed}
        .table-footer{padding:.75rem .9rem;display:flex;justify-content:space-between;align-items:center;gap:.7rem;flex-wrap:wrap;border-top:1px solid #e5e7eb}
        .footer-note{color:#6b7280;font-size:.88rem}
        .page-footer{margin-top:auto;border-top:1px solid var(--ceet-border);background:#fff;color:#6b7280;font-size:.84rem;padding:.75rem 1.2rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
        .status-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:.35rem}
        .create-user-modal .modal-content{border-radius:.75rem;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(15,23,42,.2)}
        .create-user-modal .modal-body{padding-top:.4rem}.modal-subtitle{color:#6b7280;font-size:.92rem;margin-top:-.2rem}
        @media (max-width:1199.98px){.advanced-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:991.98px){.page-shell{grid-template-columns:1fr}.topbar{justify-content:space-between;padding:.7rem 1rem}.content-wrap{padding:.9rem}.head-title{font-size:1.65rem}.kpi-grid{grid-template-columns:1fr}.filters-top,.advanced-grid{grid-template-columns:1fr}.page-footer{padding:.7rem 1rem}}
    </style>
</head>
<body>
    <div class="page-shell">
        <aside class="sidebar d-none d-lg-flex">
            <div class="sidebar-brand"><span class="sidebar-brand-badge">~</span><span>Gestion des Incidents CEET</span></div>
            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Tableau de bord</a>
                @can('incidents.view')<a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">Incidents</a>@endcan
                @can('catalogues.view')
                    <a class="sidebar-link catalogue-toggle d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold is-catalogue-current' : '' }}" data-bs-toggle="collapse" href="#desktopCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="desktopCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="catalogue-chevron small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="desktopCatalogueMenu">
                        <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">D&eacute;partements</a>
                        <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                        <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                        <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                        <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorit&eacute;s</a>
                    </div>
                @endcan
                @can('incidents.view')<a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reporting</a>@endcan
                @role('Administrateur|Superviseur')<a href="{{ route('historique.index') }}" class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">Audit</a>@endrole
                @can('users.view')<a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Utilisateurs</a>@endcan
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Reglages</a>
            </nav>
            @can('incidents.create')
                <div class="sidebar-bottom"><a href="{{ route('incidents.create') }}" class="sidebar-create"><span>+</span><span>Nouvel Incident</span></a></div>
            @endcan
        </aside>

        <div class="main-area">
            <header class="topbar">
                <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Afficher la navigation">&#9776;</button>
                <button class="icon-btn" aria-label="Notifications">&#128276;</button>
                <div class="user-chip">
                    <div class="text-end"><div class="fw-bold lh-1">{{ $currentUser?->name ?? 'Utilisateur CEET' }}</div><small class="text-muted">{{ $roleName }}</small></div>
                    <span class="user-avatar">{{ strtoupper(Str::substr($currentUser?->name ?? 'CEET', 0, 2)) }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button type="submit" class="icon-btn" aria-label="Se deconnecter">&#10140;</button></form>
                </div>
            </header>

            <main class="content-wrap">
                @if (session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
                @if (session('error'))<div class="alert alert-danger mb-0">{{ session('error') }}</div>@endif

                <div class="head-row">
                    <div>
                        <h1 class="head-title">Utilisateurs & Roles</h1>
                        <p class="head-subtitle">Gerez les acces, assignez des roles et controlez les permissions du personnel.</p>
                    </div>
                    @can('users.manage')
                        <button type="button" class="btn-action primary" data-bs-toggle="modal" data-bs-target="#createUserModal">+ Nouvel Utilisateur</button>
                    @endcan
                </div>

                <section class="kpi-grid">
                    <article class="kpi-card">
                        <div class="kpi-label">Total utilisateurs</div>
                        <div class="kpi-value">{{ number_format($stats['totalUsers'] ?? 0) }}</div>
                        <div class="kpi-sub">+{{ number_format($stats['newThisWeek'] ?? 0) }} nouveaux cette semaine</div>
                    </article>
                    <article class="kpi-card">
                        <div class="kpi-label">Operateurs actifs</div>
                        <div class="kpi-value">{{ number_format($stats['activeOperators'] ?? 0) }}</div>
                        <div class="kpi-sub">Personnel actuellement en service</div>
                    </article>
                </section>

                <form method="GET" action="{{ route('users.index') }}" class="filters-panel">
                    <div class="filters-top">
                        <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Rechercher un utilisateur par nom, email ou telephone...">
                        <button class="btn-action" type="button" data-bs-toggle="collapse" data-bs-target="#usersAdvancedFilters" aria-expanded="{{ $advancedOpen ? 'true' : 'false' }}" aria-controls="usersAdvancedFilters">Filtres avances</button>
                    </div>
                    <div class="collapse {{ $advancedOpen ? 'show' : '' }}" id="usersAdvancedFilters">
                        <div class="advanced-grid">
                            <div>
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">Tous les roles</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected((string) $filters['role'] === (string) $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Departement</label>
                                <select name="departement_id" class="form-select">
                                    <option value="">Tous les departements</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}" @selected((string) $filters['departement_id'] === (string) $dep->id)>{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Statut</label>
                                <select name="is_active" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="1" @selected((string) $filters['is_active'] === '1')>Actifs</option>
                                    <option value="0" @selected((string) $filters['is_active'] === '0')>Inactifs</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2 align-items-end">
                                <button type="submit" class="btn btn-danger flex-fill">Appliquer</button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Effacer</a>
                            </div>
                        </div>
                    </div>
                </form>

                <section class="table-panel">
                    <div class="table-responsive">
                        <table class="table users-table align-middle">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Role</th>
                                    <th>Statut</th>
                                    <th>Derniere activite</th>
                                    <th>Departement</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $fullName = $user->name ?: 'Utilisateur';
                                        $avatar = strtoupper(Str::substr($fullName, 0, 2));
                                        $userRole = $user->roles->first()?->name ?? 'Sans role';
                                        $statusClass = $user->is_active ? ($user->email_verified_at ? 'status-active' : 'status-invite') : 'status-off';
                                        $statusLabel = $user->is_active ? ($user->email_verified_at ? 'Actif' : 'Invitation envoyee') : 'Hors-ligne';
                                        $lastSeen = $user->updated_at ? Carbon::parse($user->updated_at)->diffForHumans() : '-';
                                    @endphp
                                    <tr>
                                        <td><div class="user-line"><span class="user-photo">{{ $avatar }}</span><div><p class="user-name">{{ $fullName }}</p><p class="user-email">{{ $user->email }}</p></div></div></td>
                                        <td>{{ $userRole }}</td>
                                        <td><span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                        <td>{{ $lastSeen }}</td>
                                        <td>{{ $user->departement?->nom ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="row-actions">
                                                @can('users.manage')
                                                    <a href="{{ route('users.edit', $user) }}" class="icon-action" title="Editer">&#9998;</a>
                                                    <button type="button" class="icon-action" disabled title="Gestion des droits bientot">&#128274;</button>
                                                    @if ((int) $user->id !== (int) auth()->id())
                                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer ou desactiver cet utilisateur ?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="icon-action" title="Supprimer">&#10005;</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <button type="button" class="icon-action" disabled>&#9675;</button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted">Aucun utilisateur trouve pour ces criteres.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <div class="footer-note">Affichage de {{ $from }} a {{ $to }} sur {{ $users->total() }} utilisateurs</div>
                        <div>{{ $users->links('pagination::bootstrap-5') }}</div>
                    </div>
                </section>
            </main>

            <footer class="page-footer">
                <span>&copy; {{ now()->year }} CEET - Gestion des Incidents Reseau</span>
                <span><span class="status-dot"></span>Systeme Operationnel</span>
            </footer>
        </div>
    </div>

    @can('users.manage')
        <div class="modal fade create-user-modal" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="createUserModalLabel">Ajouter un nouvel utilisateur</h5>
                            <p class="modal-subtitle mb-0">Creez un nouveau profil pour un membre de l'equipe CEET. Un email d'invitation sera envoye automatiquement.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('users.store') }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="is_active" value="1">
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prenom</label>
                                <input id="prenom" type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required>
                                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nom_famille" class="form-label">Nom de famille</label>
                                <input id="nom_famille" type="text" name="nom_famille" class="form-control @error('nom_famille') is-invalid @enderror" value="{{ old('nom_famille') }}" required>
                                @error('nom_famille')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email professionnel</label>
                                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role assigne</label>
                                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Selectionner un role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="departement_id" class="form-label">Departement / Equipe</label>
                                <select id="departement_id" name="departement_id" class="form-select @error('departement_id') is-invalid @enderror">
                                    <option value="">Aucun departement</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}" @selected((string) old('departement_id') === (string) $dep->id)>{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                                @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @error('name')<div class="col-12"><div class="alert alert-danger py-2 mb-0">{{ $message }}</div></div>@enderror
                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger">Creer le compte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-danger" id="mobileSidebarLabel">CEET Incidents</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Tableau de bord</a>
                @can('incidents.view')<a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">Incidents</a>@endcan
                @can('catalogues.view')
                    <a class="sidebar-link catalogue-toggle d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold is-catalogue-current' : '' }}" data-bs-toggle="collapse" href="#mobileCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="mobileCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="catalogue-chevron small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="mobileCatalogueMenu">
                        <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">D&eacute;partements</a>
                        <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                        <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                        <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                        <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorit&eacute;s</a>
                    </div>
                @endcan
                @can('incidents.view')<a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reporting</a>@endcan
                @role('Administrateur|Superviseur')<a href="{{ route('historique.index') }}" class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">Audit</a>@endrole
                @can('users.view')<a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Utilisateurs</a>@endcan
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Reglages</a>
            </nav>
        </div>
    </div>

    @can('users.manage')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const shouldOpen = @json($createModalOpen);
                const modalElement = document.getElementById('createUserModal');
                if (shouldOpen && modalElement && window.bootstrap && window.bootstrap.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endcan
</body>
</html>

