@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';
    $dbDriver = strtoupper((string) config('database.default', 'mysql'));
    $timezoneLabel = config('app.timezone', 'Africa/Lome');
    $defaultFooterText = 'CEET - Direction Technique & Distribution';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reglages | CEET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--ceet-red:#ef2433;--ceet-red-dark:#ce1220;--ceet-bg:#f5f6f8;--ceet-border:#e5e7eb;--ceet-muted:#6b7280;--ceet-shadow:0 14px 32px rgba(15,23,42,.04)}
        html,body{margin:0;min-height:100%;background:var(--ceet-bg);color:#1f2937;font-family:"Figtree","Segoe UI",sans-serif}
        .page-shell{min-height:100dvh;display:grid;grid-template-columns:250px 1fr}
        .sidebar{border-right:1px solid var(--ceet-border);background:#f8f9fb;display:flex;flex-direction:column}
        .sidebar-brand{min-height:72px;padding:1rem 1.2rem;border-bottom:1px solid var(--ceet-border);display:flex;align-items:center;gap:.6rem;color:var(--ceet-red);font-weight:700;font-size:1.03rem}
        .sidebar-brand-badge{width:24px;height:24px;border-radius:.35rem;background:var(--ceet-red);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem}
        .sidebar-menu{padding:.8rem;display:grid;gap:.25rem}
        .sidebar-link{border-radius:.55rem;color:#1f2937;text-decoration:none;padding:.65rem .72rem;font-weight:500;font-size:.92rem}
        .sidebar-link:hover{background:#f1f5f9}.sidebar-link.active{background:#eceff3;font-weight:700}
        .sidebar-bottom{margin-top:auto;border-top:1px solid var(--ceet-border);padding:.8rem}
        .sidebar-create{width:100%;border:1px solid var(--ceet-border);border-radius:.62rem;background:#fff;color:#111827;font-weight:600;text-decoration:none;display:inline-flex;justify-content:center;gap:.5rem;padding:.62rem .8rem;font-size:.9rem}
        .main-area{min-width:0;display:flex;flex-direction:column;min-height:100dvh}
        .topbar{min-height:72px;border-bottom:1px solid var(--ceet-border);background:#fff;display:flex;align-items:center;justify-content:flex-end;gap:.9rem;padding:.8rem 1.2rem}
        .icon-btn{border:1px solid transparent;border-radius:.5rem;background:#fff;color:#111827;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center}
        .icon-btn:hover{border-color:var(--ceet-border);background:#f8fafc}
        .user-chip{border-left:1px solid var(--ceet-border);padding-left:.8rem;display:inline-flex;align-items:center;gap:.7rem}
        .user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(140deg,#f8d64f 0%,#f5a623 100%);color:#3b2f18;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.88rem}
        .content-wrap{padding:1rem 1.3rem;display:grid;gap:1rem}
        .head-row{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;border-bottom:1px solid var(--ceet-border);padding-bottom:1rem}
        .head-title{margin:0;font-size:2.05rem;font-weight:800;line-height:1.1}
        .head-subtitle{margin-top:.2rem;color:#6b7280;font-size:.96rem;max-width:720px}
        .head-actions{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap}
        .db-pill{border:1px solid #e5e7eb;background:#fff;border-radius:999px;padding:.35rem .7rem;font-size:.82rem;color:#4b5563;line-height:1.2}
        .save-btn{border:1px solid var(--ceet-red);background:var(--ceet-red);color:#fff;border-radius:.55rem;font-weight:700;padding:.54rem .9rem;display:inline-flex;align-items:center;gap:.35rem}
        .save-btn:hover{background:var(--ceet-red-dark);border-color:var(--ceet-red-dark)}
        .settings-title{text-align:center}
        .settings-title h2{font-size:2.25rem;font-weight:800;margin:0}
        .settings-title p{margin:.25rem 0 0;color:#6b7280}
        .settings-layout{display:grid;grid-template-columns:190px 1fr;gap:1rem;align-items:start}
        .tab-list{display:grid;gap:.5rem}
        .tab-item{border:1px solid #eceff3;background:#fff;border-radius:.55rem;padding:.62rem .7rem;color:#6b7280;font-weight:600;font-size:.9rem}
        .tab-item.active{color:#111827;border-left:3px solid var(--ceet-red)}
        .panel-group{display:grid;gap:.8rem}
        .panel{border:1px solid var(--ceet-border);border-radius:.75rem;background:#fff;padding:.85rem 1rem}
        .panel h3{margin:0;font-size:1.45rem;font-weight:800}
        .panel p{margin:.2rem 0 .8rem;color:#6b7280}
        .form-label{font-size:.82rem;font-weight:700;color:#374151;margin-bottom:.24rem}
        .form-control,.form-select{border-radius:.5rem;border-color:#d8dee5;font-size:.91rem;min-height:40px}
        .form-control:focus,.form-select:focus{border-color:#f6a6ae;box-shadow:0 0 0 .16rem rgba(239,36,51,.14)}
        .maintenance{background:#fff5f6;border-color:#f6d5da}
        .maintenance h4{margin:0;color:#dc2626;font-size:1.2rem;font-weight:800}
        .maintenance p{margin:.2rem 0 .7rem;color:#6b7280}
        .switch-line{display:flex;justify-content:space-between;align-items:center;gap:.8rem}
        .form-check-input:checked{background-color:var(--ceet-red);border-color:var(--ceet-red)}
        .security-card{border:1px solid var(--ceet-border);border-radius:.75rem;background:#fff;padding:1rem;box-shadow:var(--ceet-shadow)}
        .security-title{margin:0;font-size:1.45rem;font-weight:800}
        .page-footer{margin-top:auto;border-top:1px solid var(--ceet-border);background:#fff;color:#6b7280;font-size:.84rem;padding:.75rem 1.2rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
        .status-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:.35rem}
        @media (max-width:1199.98px){.settings-layout{grid-template-columns:1fr}.tab-list{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:991.98px){.page-shell{grid-template-columns:1fr}.topbar{justify-content:space-between;padding:.7rem 1rem}.content-wrap{padding:.9rem}.head-title{font-size:1.65rem}.settings-title h2{font-size:1.8rem}.tab-list{grid-template-columns:1fr}.page-footer{padding:.7rem 1rem}}
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
                    <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">Departs</a>
                    <a href="{{ route('catalogues.types.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.types.*') || request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Types & Causes</a>
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
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success mb-0">Reglages enregistres avec succes.</div>
                @endif
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success mb-0">Mot de passe mis a jour avec succes.</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
                @endif

                <div class="head-row">
                    <div>
                        <h1 class="head-title">Reglages Systeme</h1>
                        <p class="head-subtitle">Configurez les parametres globaux, les integrations et les preferences de l'application.</p>
                    </div>
                    <div class="head-actions">
                        <span class="db-pill">Base de donnees connectee : {{ $dbDriver }} {{ $dbVersion ?? 'N/A' }}</span>
                        <button type="submit" form="generalSettingsForm" class="save-btn">Enregistrer les modifications</button>
                    </div>
                </div>

                <section class="settings-title">
                    <h2>Configuration Generale</h2>
                    <p>Parametres d'identite et de comportement de l'instance locale de l'application.</p>
                </section>

                <form id="generalSettingsForm" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="email" value="{{ old('email', Str::lower($user->email)) }}">

                    <div class="settings-layout">
                        <aside class="tab-list">
                            <div class="tab-item active">General</div>
                            <div class="tab-item">Notifications</div>
                            <div class="tab-item">Integration & DB</div>
                            <div class="tab-item">Exports & Rapports</div>
                        </aside>

                        <div class="panel-group">
                            <section class="panel">
                                <h3>Identite de l'application</h3>
                                <p>Informations affichees sur les rapports et l'interface utilisateur.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">Nom de l'instance</label>
                                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="org_unit">Unite Organisationnelle</label>
                                        <input id="org_unit" name="org_unit" type="text" class="form-control" value="{{ old('org_unit', $user->departement?->nom ?? 'Cellule de Transformation Digitale') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="footer_text">Texte de bas de page personnalise</label>
                                        <input id="footer_text" name="footer_text" type="text" class="form-control" value="{{ old('footer_text', $defaultFooterText) }}">
                                    </div>
                                </div>
                            </section>

                            <section class="panel">
                                <h3>Region et Langue</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="app_locale">Langue de l'interface</label>
                                        <select id="app_locale" class="form-select" name="app_locale">
                                            <option value="fr_TG" selected>Francais (Benin/Togo)</option>
                                            <option value="en_US">English (US)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="app_timezone">Fuseau horaire</label>
                                        <input id="app_timezone" type="text" class="form-control" name="app_timezone" value="{{ old('app_timezone', '(GMT+00:00) ' . $timezoneLabel) }}">
                                    </div>
                                </div>
                            </section>

                            <section class="panel maintenance">
                                <h4>Maintenance & Archives</h4>
                                <p>Actions critiques sur les donnees historiques.</p>
                                <div class="switch-line">
                                    <div>
                                        <strong>Archivage automatique</strong>
                                        <div class="text-muted small">Deplacer les incidents clotures depuis plus de 24 mois vers la base d'archives.</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="archive_enabled" name="archive_enabled" value="1" {{ old('archive_enabled', '1') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </form>

                <section class="security-card">
                    <h3 class="security-title">Securite du compte</h3>
                    <p class="text-muted mb-3">Mettez a jour votre mot de passe administrateur.</p>
                    <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-4">
                            <label class="form-label" for="current_password">Mot de passe actuel</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password">
                            @if($errors->updatePassword->has('current_password'))<div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>@endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="password">Nouveau mot de passe</label>
                            <input id="password" name="password" type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password">
                            @if($errors->updatePassword->has('password'))<div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>@endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password">
                            @if($errors->updatePassword->has('password_confirmation'))<div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>@endif
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-outline-danger">Mettre a jour le mot de passe</button>
                        </div>
                    </form>
                </section>
            </main>

            <footer class="page-footer">
                <span>&copy; {{ now()->year }} CEET - Gestion des Incidents Reseau</span>
                <span><span class="status-dot"></span>Systeme Operationnel</span>
            </footer>
        </div>
    </div>

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
                    <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">Departs</a>
                    <a href="{{ route('catalogues.types.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.types.*') || request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Types & Causes</a>
                @endcan
                @can('incidents.view')<a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reporting</a>@endcan
                @role('Administrateur|Superviseur')<a href="{{ route('historique.index') }}" class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">Audit</a>@endrole
                @can('users.view')<a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Utilisateurs</a>@endcan
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Reglages</a>
            </nav>
        </div>
    </div>
</body>
</html>
