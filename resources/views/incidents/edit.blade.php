@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';
    $finalStatusId = optional($statuts->firstWhere('is_final', true))->id;

    $startedAt = old('date_debut', $incident->date_debut?->format('Y-m-d\TH:i'));
    $endedAt = old('date_fin', $incident->date_fin?->format('Y-m-d\TH:i'));

    $durationMinutes = $incident->duree_minutes;
    if ($durationMinutes === null && $incident->date_debut && $incident->date_fin) {
        $durationMinutes = $incident->date_debut->diffInMinutes($incident->date_fin);
    }

    $durationHours = intdiv((int) ($durationMinutes ?? 0), 60);
    $durationRemain = (int) ($durationMinutes ?? 0) % 60;
    $durationLabel = $durationMinutes !== null ? sprintf('%dh %02dmin', $durationHours, $durationRemain) : '-';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fiche Incident | CEET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
            --ceet-bg: #f5f6f8;
            --ceet-border: #e5e7eb;
            --ceet-muted: #6b7280;
            --ceet-shadow: 0 14px 32px rgba(15, 23, 42, 0.04);
        }

        html,
        body {
            margin: 0;
            width: 100%;
            min-height: 100%;
            background: var(--ceet-bg);
            color: #1f2937;
            font-family: "Figtree", "Segoe UI", sans-serif;
        }

        .page-shell {
            min-height: 100dvh;
            display: grid;
            grid-template-columns: 245px 1fr;
        }

        .sidebar {
            border-right: 1px solid var(--ceet-border);
            background: #f8f9fb;
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
            position: sticky;
            top: 0;
            height: 100dvh;
            z-index: 1010;
        }

        .sidebar-brand {
            min-height: 72px;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--ceet-border);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--ceet-red);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .sidebar-brand-badge {
            width: 23px;
            height: 23px;
            border-radius: 0.35rem;
            background: var(--ceet-red);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 0.8rem;
            display: grid;
            gap: 0.25rem;
        }

        .sidebar-link {
            border-radius: 0.55rem;
            color: #1f2937;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.65rem 0.72rem;
            font-weight: 500;
            font-size: 0.92rem;
        }

        .sidebar-link:hover {
            background: #f1f5f9;
        }

        .sidebar-link.active {
            background: #eceff3;
            font-weight: 700;
        }

        .sidebar-icon {
            width: 16px;
            height: 16px;
            color: #111827;
            flex-shrink: 0;
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid var(--ceet-border);
            padding: 0.8rem;
        }

        .sidebar-create {
            width: 100%;
            border: 1px solid var(--ceet-border);
            border-radius: 0.62rem;
            background: #fff;
            color: #111827;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 0.62rem 0.8rem;
            font-size: 0.9rem;
        }

        .main-area {
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
        }

        .topbar {
            min-height: 72px;
            border-bottom: 1px solid var(--ceet-border);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.9rem;
            padding: 0.8rem 1.2rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .icon-btn {
            border: 1px solid transparent;
            border-radius: 0.5rem;
            background: #fff;
            color: #111827;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            border-color: var(--ceet-border);
            background: #f8fafc;
        }

        .user-chip {
            border-left: 1px solid var(--ceet-border);
            padding-left: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(140deg, #f8d64f 0%, #f5a623 100%);
            color: #3b2f18;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .content-wrap {
            padding: 1rem 1.3rem;
            display: grid;
            gap: 0.9rem;
        }

        .return-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .return-link:hover {
            color: #111827;
        }

        .head-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .head-title {
            margin: 0;
            font-size: 2.05rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .head-meta {
            margin-top: 0.2rem;
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-head-pill {
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            padding: 0.2rem 0.55rem;
            font-size: 0.74rem;
            font-weight: 700;
            color: #374151;
        }

        .head-actions {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .btn-action {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 0.55rem;
            font-weight: 600;
            padding: 0.52rem 0.84rem;
            text-decoration: none;
        }

        .btn-action.primary {
            border-color: var(--ceet-red);
            background: var(--ceet-red);
            color: #fff;
            font-weight: 700;
        }

        .btn-action.primary:hover {
            background: var(--ceet-red-dark);
            border-color: var(--ceet-red-dark);
            color: #fff;
        }

        .btn-action:hover {
            border-color: #9ca3af;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 0.9rem;
            align-items: start;
        }

        .panel {
            border: 1px solid var(--ceet-border);
            border-radius: 0.78rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
        }

        .panel-body {
            padding: 0.95rem;
        }

        .panel-head {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 800;
            color: #374151;
            margin-bottom: 0.8rem;
            border-bottom: 1px solid #eceff3;
            padding-bottom: 0.65rem;
            font-size: 1.05rem;
        }

        .panel-head .dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1px solid #f6b1b8;
            color: var(--ceet-red);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .form-label {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.01em;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border-color: #d8dee5;
            font-size: 0.92rem;
            min-height: 40px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #f6a6ae;
            box-shadow: 0 0 0 0.16rem rgba(239, 36, 51, 0.14);
        }

        .stack-rows {
            display: grid;
            gap: 0.65rem;
        }

        .mini-card {
            border: 1px solid #e7ebf0;
            border-radius: 0.55rem;
            background: #f9fafb;
            padding: 0.65rem;
        }

        .duration-box {
            border: 1px solid #fbc8ce;
            background: #fff6f7;
            border-radius: 0.6rem;
            padding: 0.65rem;
            text-align: center;
            margin-top: 0.65rem;
        }

        .duration-title {
            color: #e25a66;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.08em;
            font-size: 0.65rem;
            margin-bottom: 0.2rem;
        }

        .duration-value {
            color: var(--ceet-red);
            font-size: 1.95rem;
            font-weight: 800;
            line-height: 1;
        }

        .duration-note {
            color: #9ca3af;
            font-size: 0.68rem;
            margin-top: 0.2rem;
        }

        .person-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #e7ebf0;
            border-radius: 0.55rem;
            padding: 0.45rem 0.5rem;
            margin-bottom: 0.45rem;
            background: #f8fafc;
        }

        .person-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(140deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e3a8a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.72rem;
            flex-shrink: 0;
        }

        .person-name {
            margin: 0;
            font-size: 0.86rem;
            font-weight: 700;
            color: #374151;
        }

        .person-role {
            margin: 0;
            font-size: 0.74rem;
            color: #6b7280;
        }

        .bottom-actions {
            margin-top: 0.45rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .bottom-actions .hint {
            color: #9ca3af;
            font-size: 0.77rem;
            font-style: italic;
        }

        .page-footer {
            margin-top: auto;
            border-top: 1px solid var(--ceet-border);
            background: #fff;
            color: #6b7280;
            font-size: 0.84rem;
            padding: 0.75rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
            margin-right: 0.35rem;
        }

        @media (max-width: 1199.98px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .page-shell {
                grid-template-columns: 1fr;
            }

            .topbar {
                justify-content: space-between;
                padding: 0.7rem 1rem;
            }

            .content-wrap {
                padding: 0.9rem;
            }

            .head-title {
                font-size: 1.65rem;
            }

            .page-footer {
                padding: 0.7rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <aside class="sidebar d-none d-lg-flex">
            <div class="sidebar-brand">
                <span class="sidebar-brand-badge">~</span>
                <span>Gestion des Incidents CEET</span>
            </div>

            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    <span>Tableau de bord</span>
                </a>
                <a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Incidents</span>
                </a>
                @can('catalogues.view')
                    <a class="sidebar-link d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="desktopCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="desktopCatalogueMenu">
                        <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">Départements</a>
                        <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                        <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                        <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                        <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorités</a>
                    </div>
                @endcan
                @can('incidents.view')
                    <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M5 19V5M10 19V9M15 19V13M20 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <span>Reporting</span>
                    </a>
                @endcan
                @role('Administrateur|Superviseur')
                    <a href="{{ route('historique.index') }}" class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M3 12A9 9 0 1 0 6 5.3M3 4V9H8M12 7V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Audit</span>
                    </a>
                @endrole
                @can('users.view')
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M16 19V17A4 4 0 0 0 12 13H8A4 4 0 0 0 4 17V19M20 19V17A4 4 0 0 0 17 13.1M12 5A3 3 0 1 1 12 11A3 3 0 0 1 12 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Utilisateurs</span>
                    </a>
                @endcan
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 0 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 0 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.38A1.65 1.65 0 0 0 7.1 19.71L7.04 19.77A2 2 0 1 1 4.21 16.94L4.27 16.88A1.65 1.65 0 0 0 4.6 15.06A1.65 1.65 0 0 0 3.07 13.98H3A2 2 0 0 1 3 9.98H3.09A1.65 1.65 0 0 0 4.62 8.9A1.65 1.65 0 0 0 4.29 7.08L4.23 7.02A2 2 0 1 1 7.06 4.19L7.12 4.25A1.65 1.65 0 0 0 8.94 4.58H9A1.65 1.65 0 0 0 10.06 3.05V3A2 2 0 1 1 14.06 3V3.09A1.65 1.65 0 0 0 15.14 4.62A1.65 1.65 0 0 0 16.96 4.29L17.02 4.23A2 2 0 1 1 19.85 7.06L19.79 7.12A1.65 1.65 0 0 0 19.46 8.94V9A1.65 1.65 0 0 0 21 10.06H21.09A2 2 0 0 1 21.09 14.06H21A1.65 1.65 0 0 0 19.47 15.14Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Reglages</span>
                </a>
            </nav>

            @can('incidents.create')
                <div class="sidebar-bottom">
                    <a href="{{ route('incidents.create') }}" class="sidebar-create">
                        <span>+</span>
                        <span>Nouvel Incident</span>
                    </a>
                </div>
            @endcan
        </aside>

        <div class="main-area">
            <header class="topbar">
                <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Afficher la navigation">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>

                <button class="icon-btn" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 17H20L18.6 15.6C18.2 15.2 18 14.7 18 14.2V10.5C18 7.4 15.9 4.8 13 4V3.5A1.5 1.5 0 0 0 10 3.5V4C7.1 4.8 5 7.4 5 10.5V14.2C5 14.7 4.8 15.2 4.4 15.6L3 17H8M9 17C9 18.7 10.3 20 12 20C13.7 20 15 18.7 15 17H9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <div class="user-chip">
                    <div class="text-end">
                        <div class="fw-bold lh-1">{{ $currentUser?->name ?? 'Utilisateur CEET' }}</div>
                        <small class="text-muted">{{ $roleName }}</small>
                    </div>
                    <span class="user-avatar">{{ strtoupper(Str::substr($currentUser?->name ?? 'CEET', 0, 2)) }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="icon-btn" aria-label="Se deconnecter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9M16 17L21 12L16 7M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                </div>
            </header>

            <main class="content-wrap">
                <a href="{{ route('incidents.index') }}" class="return-link">&larr; Retour a la liste</a>

                <div class="head-row">
                    <div>
                        <h1 class="head-title">Fiche Incident</h1>
                        <div class="head-meta">ID: {{ $incident->code_incident }} <span class="status-head-pill">{{ strtoupper($incident->statut?->libelle ?? 'EN INTERVENTION') }}</span></div>
                    </div>
                    <div class="head-actions">
                        <a href="{{ route('incidents.show', $incident) }}" class="btn-action">Annuler</a>
                        <button type="submit" form="incident-edit-form" class="btn-action primary">Enregistrer</button>
                        <button type="button" id="close-incident-btn" class="btn-action">Cloturer</button>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mb-0">
                        <div class="fw-semibold">Le formulaire contient des erreurs.</div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form id="incident-edit-form" method="POST" action="{{ route('incidents.update', $incident) }}">
                    @csrf
                    @method('PUT')

                    <div class="layout-grid">
                        <div class="stack-rows">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="panel-head"><span class="dot">!</span> Identification de l'Incident</div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Intitule incident *</label>
                                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror" value="{{ old('titre', $incident->titre) }}" required>
                                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Depart (reseau) *</label>
                                            <select name="departement_id" class="form-select js-tom-select @error('departement_id') is-invalid @enderror" data-placeholder="Selectionner un depart" required>
                                                <option value="">Selectionner</option>
                                                @foreach($departements as $dep)
                                                    <option value="{{ $dep->id }}" @selected(old('departement_id', $incident->departement_id) == $dep->id)>{{ $dep->nom }}</option>
                                                @endforeach
                                            </select>
                                            @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Localisation precise / Poste</label>
                                            <input type="text" name="localisation" class="form-control @error('localisation') is-invalid @enderror" value="{{ old('localisation', $incident->localisation) }}" placeholder="Zone industrielle - Poste H2">
                                            @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Type d'incident *</label>
                                            <select id="incident-type-select" name="type_incident_id" class="form-select js-tom-select @error('type_incident_id') is-invalid @enderror" data-placeholder="Selectionner un type" required>
                                                <option value="">Selectionner</option>
                                                @foreach($types as $type)
                                                    <option value="{{ $type->id }}" @selected(old('type_incident_id', $incident->type_incident_id) == $type->id)>{{ $type->libelle }}</option>
                                                @endforeach
                                            </select>
                                            @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Cause probable</label>
                                            <select id="incident-cause-select" name="cause_id" class="form-select js-tom-select @error('cause_id') is-invalid @enderror" data-placeholder="Selectionner une cause" data-selected-cause="{{ old('cause_id', $incident->cause_id) }}" data-endpoint-template="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}">
                                                <option value="">Aucune</option>
                                                @foreach($causes as $cause)
                                                    <option value="{{ $cause->id }}" @selected(old('cause_id', $incident->cause_id) == $cause->id)>{{ $cause->libelle }}</option>
                                                @endforeach
                                            </select>
                                            @error('cause_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Statut *</label>
                                            <select id="status-select" name="status_id" class="form-select js-tom-select @error('status_id') is-invalid @enderror" data-placeholder="Selectionner un statut" required>
                                                @foreach($statuts as $statut)
                                                    <option value="{{ $statut->id }}" @selected(old('status_id', $incident->status_id) == $statut->id)>{{ $statut->libelle }}</option>
                                                @endforeach
                                            </select>
                                            @error('status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Priorite *</label>
                                            <select name="priorite_id" class="form-select js-tom-select @error('priorite_id') is-invalid @enderror" data-placeholder="Selectionner une priorite" required>
                                                @foreach($priorites as $priorite)
                                                    <option value="{{ $priorite->id }}" @selected(old('priorite_id', $incident->priorite_id) == $priorite->id)>{{ $priorite->libelle }}</option>
                                                @endforeach
                                            </select>
                                            @error('priorite_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="panel">
                                <div class="panel-body">
                                    <div class="panel-head"><span class="dot">+</span> Details & Actions</div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Description des constatations</label>
                                            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $incident->description) }}</textarea>
                                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Actions menees sur le terrain</label>
                                            <textarea name="actions_menees" rows="4" class="form-control @error('actions_menees') is-invalid @enderror">{{ old('actions_menees', $incident->actions_menees) }}</textarea>
                                            @error('actions_menees')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Resume de resolution</label>
                                            <textarea name="resolution_summary" rows="3" class="form-control @error('resolution_summary') is-invalid @enderror">{{ old('resolution_summary', $incident->resolution_summary) }}</textarea>
                                            @error('resolution_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="stack-rows">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="panel-head"><span class="dot">o</span> Chronologie</div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label">Debut de l'incident *</label>
                                            <input id="date-debut-input" type="datetime-local" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror" value="{{ $startedAt }}" required>
                                            @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Fin / Retablissement</label>
                                            <input id="date-fin-input" type="datetime-local" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror" value="{{ $endedAt }}">
                                            @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="duration-box">
                                        <div class="duration-title">Duree totale d'interruption</div>
                                        <div id="duration-display" class="duration-value">{{ $durationLabel }}</div>
                                        <div class="duration-note">Calcule automatiquement selon les normes CEET</div>
                                    </div>
                                </div>
                            </section>

                            <section class="panel">
                                <div class="panel-body">
                                    <div class="panel-head"><span class="dot">*</span> Intervenants</div>

                                    <label class="form-label">Operateur terrain</label>
                                    <div class="person-row">
                                        <span class="person-avatar">{{ strtoupper(Str::substr($incident->operateur?->name ?? 'OP', 0, 2)) }}</span>
                                        <div>
                                            <p class="person-name">{{ $incident->operateur?->name ?? 'Non assigne' }}</p>
                                            <p class="person-role">Operateur terrain</p>
                                        </div>
                                    </div>

                                    <label class="form-label">Responsable d'intervention</label>
                                    <select name="responsable_id" class="form-select js-tom-select @error('responsable_id') is-invalid @enderror" data-placeholder="Selectionner un responsable">
                                        <option value="">Non assigne</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('responsable_id', $incident->responsable_id) == $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('responsable_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                                    <label class="form-label mt-2">Superviseur</label>
                                    <select name="superviseur_id" class="form-select js-tom-select @error('superviseur_id') is-invalid @enderror" data-placeholder="Selectionner un superviseur">
                                        <option value="">Non assigne</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('superviseur_id', $incident->superviseur_id) == $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('superviseur_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="bottom-actions">
                        <span class="hint">Derniere modification enregistree il y a quelques minutes</span>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('incidents.show', $incident) }}" class="btn-action">Abandonner les modifications</a>
                            <button type="submit" class="btn-action primary">Enregistrer brouillon</button>
                            <button type="button" id="bottom-close-btn" class="btn-action">Valider & Cloturer</button>
                        </div>
                    </div>
                </form>
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
                <a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">Incidents</a>
                @can('catalogues.view')
                    <a class="sidebar-link d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#mobileCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="mobileCatalogueMenu">
                        <span>Catalogue</span>
                        <span class="small">v</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="mobileCatalogueMenu">
                        <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">Départements</a>
                        <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                        <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                        <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                        <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorités</a>
                    </div>
                @endcan
                @can('incidents.view')
                    <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reporting</a>
                @endcan
                @role('Administrateur|Superviseur')
                    <a href="{{ route('historique.index') }}" class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">Audit</a>
                @endrole
                @can('users.view')
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Utilisateurs</a>
                @endcan
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Reglages</a>
            </nav>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeSelect = document.getElementById('incident-type-select');
            const causeSelect = document.getElementById('incident-cause-select');
            const dateDebutInput = document.getElementById('date-debut-input');
            const dateFinInput = document.getElementById('date-fin-input');
            const durationDisplay = document.getElementById('duration-display');
            const closeButtons = [document.getElementById('close-incident-btn'), document.getElementById('bottom-close-btn')];
            const statusSelect = document.getElementById('status-select');
            const form = document.getElementById('incident-edit-form');
            const finalStatusId = '{{ $finalStatusId }}';

            const toDate = (value) => {
                if (!value) return null;
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
            };

            const pad = (number) => String(number).padStart(2, '0');
            const asDateTimeLocal = (date) => {
                return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
            };

            const refreshDuration = () => {
                if (!durationDisplay || !dateDebutInput || !dateFinInput) return;

                const start = toDate(dateDebutInput.value);
                const end = toDate(dateFinInput.value);

                if (!start || !end || end < start) {
                    durationDisplay.textContent = '-';
                    return;
                }

                const diffMinutes = Math.floor((end - start) / 60000);
                const hours = Math.floor(diffMinutes / 60);
                const mins = diffMinutes % 60;
                durationDisplay.textContent = `${hours}h ${String(mins).padStart(2, '0')}min`;
            };

            if (dateDebutInput) dateDebutInput.addEventListener('change', refreshDuration);
            if (dateFinInput) dateFinInput.addEventListener('change', refreshDuration);
            refreshDuration();

            const closeIncident = () => {
                if (!form) return;

                if (!dateFinInput.value) {
                    dateFinInput.value = asDateTimeLocal(new Date());
                }

                if (finalStatusId && statusSelect) {
                    if (statusSelect.tomselect) {
                        statusSelect.tomselect.setValue(finalStatusId);
                    } else {
                        statusSelect.value = finalStatusId;
                    }
                }

                form.submit();
            };

            closeButtons.forEach((button) => {
                if (button) {
                    button.addEventListener('click', closeIncident);
                }
            });

            if (!typeSelect || !causeSelect) {
                return;
            }

            const endpointTemplate = causeSelect.dataset.endpointTemplate;
            const initialCauseId = String(causeSelect.dataset.selectedCause || causeSelect.value || '');

            const setDisabled = (disabled) => {
                causeSelect.disabled = disabled;
                if (causeSelect.tomselect) {
                    if (disabled) {
                        causeSelect.tomselect.disable();
                    } else {
                        causeSelect.tomselect.enable();
                    }
                }
            };

            const setOptions = (items, selectedId = '') => {
                const options = [{ value: '', text: 'Aucune' }, ...items.map((item) => ({
                    value: String(item.id),
                    text: item.libelle
                }))];

                if (causeSelect.tomselect) {
                    const control = causeSelect.tomselect;
                    control.clear(true);
                    control.clearOptions();
                    control.addOptions(options);
                    control.refreshOptions(false);
                    control.setValue(String(selectedId || ''), true);
                    return;
                }

                causeSelect.innerHTML = '';
                options.forEach((option) => {
                    const element = document.createElement('option');
                    element.value = option.value;
                    element.textContent = option.text;
                    if (option.value === String(selectedId || '')) {
                        element.selected = true;
                    }
                    causeSelect.appendChild(element);
                });
            };

            const loadCausesByType = async (typeId, selectedId = '') => {
                if (!typeId) {
                    setOptions([], '');
                    setDisabled(true);
                    return;
                }

                setDisabled(true);
                setOptions([{ id: '', libelle: 'Chargement...' }], '');

                try {
                    const endpoint = endpointTemplate.replace('__TYPE__', encodeURIComponent(typeId));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Unable to fetch causes');
                    }

                    const causes = await response.json();
                    setOptions(causes, selectedId);
                    setDisabled(false);
                } catch (error) {
                    setOptions([], '');
                    setDisabled(true);
                }
            };

            typeSelect.addEventListener('change', () => {
                loadCausesByType(typeSelect.value, '');
            });

            if (typeSelect.value) {
                loadCausesByType(typeSelect.value, initialCauseId);
            } else if (!initialCauseId) {
                setOptions([], '');
                setDisabled(true);
            }
        });
    </script>
</body>
</html>


