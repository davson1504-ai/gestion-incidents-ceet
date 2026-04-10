@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';

    $showAdvanced = collect([
        $filters['departement_id'] ?? null,
        $filters['status_id'] ?? null,
        $filters['priorite_id'] ?? null,
        $filters['type_incident_id'] ?? null,
        $filters['cause_id'] ?? null,
        $filters['operateur_id'] ?? null,
        $filters['date_from'] ?? null,
        $filters['date_to'] ?? null,
    ])->filter(fn ($value) => filled($value))->isNotEmpty();

    $dailyDate = $filters['date_from'] ?: now()->toDateString();
    $from = $incidents->firstItem() ?? 0;
    $to = $incidents->lastItem() ?? 0;

    $formatDuration = static function (?int $minutes): string {
        if ($minutes === null) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return sprintf('%02dh %02dmin', $hours, $remaining);
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Incidents | CEET</title>
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
            grid-template-columns: 280px 1fr;
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
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--ceet-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--ceet-red);
            font-weight: 700;
            font-size: 1.15rem;
        }

        .sidebar-brand-badge {
            width: 30px;
            height: 30px;
            border-radius: 0.5rem;
            background: var(--ceet-red);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 1rem;
            display: grid;
            gap: 0.3rem;
        }

        .sidebar-link {
            border-radius: 0.65rem;
            color: #1f2937;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.72rem 0.85rem;
            font-weight: 500;
        }

        .sidebar-link:hover {
            background: #f1f5f9;
        }

        .sidebar-link.active {
            background: #eceff3;
            font-weight: 700;
        }
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

        .sidebar-icon {
            width: 18px;
            height: 18px;
            color: #111827;
            flex-shrink: 0;
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid var(--ceet-border);
            padding: 1rem;
        }

        .sidebar-create {
            width: 100%;
            border: 1px solid var(--ceet-border);
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
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .icon-btn {
            border: 1px solid transparent;
            border-radius: 0.5rem;
            background: #fff;
            color: #111827;
            width: 38px;
            height: 38px;
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
            padding-left: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(140deg, #f8d64f 0%, #f5a623 100%);
            color: #3b2f18;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .content-wrap {
            padding: 1.5rem;
            display: grid;
            gap: 1.1rem;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.9rem;
            flex-wrap: wrap;
        }

        .page-title {
            margin: 0;
            font-size: 2.1rem;
            font-weight: 800;
        }

        .page-subtitle {
            margin: 0.35rem 0 0;
            color: var(--ceet-muted);
            font-size: 1.04rem;
        }

        .action-btn {
            border: 1px solid #f6d1d5;
            background: #fff;
            color: var(--ceet-red);
            border-radius: 0.65rem;
            padding: 0.55rem 0.9rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .action-btn:hover {
            border-color: var(--ceet-red);
            color: var(--ceet-red-dark);
        }

        .action-btn.primary {
            border-color: var(--ceet-red);
            background: var(--ceet-red);
            color: #fff;
        }

        .action-btn.primary:hover {
            background: var(--ceet-red-dark);
            border-color: var(--ceet-red-dark);
            color: #fff;
        }

        .panel {
            border: 1px solid var(--ceet-border);
            border-radius: 0.9rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
            padding: 1rem;
        }

        .quick-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.75rem;
            align-items: center;
        }

        .search-wrap {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 230px;
        }

        .filter-toggle {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 0.6rem;
            font-weight: 600;
            padding: 0.52rem 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .export-group {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .export-link {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 0.58rem;
            text-decoration: none;
            padding: 0.47rem 0.72rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .advanced-grid {
            margin-top: 0.9rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
            align-items: end;
        }

        .advanced-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            align-items: center;
        }

        .list-panel {
            border: 1px solid var(--ceet-border);
            border-radius: 0.9rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
            overflow: hidden;
        }

        .list-table {
            margin: 0;
        }

        .list-table thead th {
            color: #6b7280;
            font-size: 0.86rem;
            letter-spacing: 0.02em;
            border-bottom-color: #e5e7eb;
            white-space: nowrap;
            background: #f9fafb;
        }

        .list-table td {
            vertical-align: middle;
            border-bottom-color: #edf2f7;
        }

        .status-pill {
            border-radius: 999px;
            padding: 0.16rem 0.58rem;
            font-size: 0.76rem;
            font-weight: 700;
            display: inline-block;
        }

        .status-open {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-closed {
            background: #f3f4f6;
            color: #4b5563;
        }

        .duration-cell {
            font-weight: 600;
            font-size: 0.84rem;
            color: #374151;
            line-height: 1.25;
            white-space: nowrap;
        }

        .row-actions {
            display: inline-flex;
            gap: 0.4rem;
            justify-content: flex-end;
        }

        .icon-action {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #4b5563;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .icon-action:hover {
            border-color: var(--ceet-red);
            color: var(--ceet-red);
        }

        .list-footer {
            padding: 0.9rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .footer-note {
            color: #6b7280;
            font-size: 0.92rem;
        }

        .page-footer {
            margin-top: auto;
            border-top: 1px solid var(--ceet-border);
            background: #fff;
            color: #6b7280;
            font-size: 0.88rem;
            padding: 0.8rem 1.5rem;
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
            .advanced-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .page-shell {
                grid-template-columns: 1fr;
            }

            .topbar {
                justify-content: space-between;
                padding: 0.75rem 1rem;
            }

            .content-wrap {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .quick-row {
                grid-template-columns: 1fr;
            }

            .advanced-grid {
                grid-template-columns: 1fr;
            }

            .list-footer {
                padding: 0.8rem;
            }

            .page-footer {
                padding: 0.8rem 1rem;
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
                @if (session('success'))
                    <div class="alert alert-success mb-0">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mb-0">{{ session('error') }}</div>
                @endif

                <div class="page-head">
                    <div>
                        <h1 class="page-title">Liste des Incidents</h1>
                        <p class="page-subtitle">Consultez et gerez l'ensemble des anomalies detectees sur le reseau national.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('reports.daily', ['date' => $dailyDate, 'format' => 'pdf']) }}" class="action-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 3V16M12 16L7 11M12 16L17 11M5 21H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Rapport Journalier
                        </a>
                        @can('incidents.create')
                            <a href="{{ route('incidents.create') }}" class="action-btn primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Declarer un incident
                            </a>
                        @endcan
                    </div>
                </div>

                <form method="GET" action="{{ route('incidents.index') }}" class="panel">
                    <div class="quick-row">
                        <div class="search-wrap">
                            <div class="search-input">
                                <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Rechercher par ID, depart ou operateur...">
                            </div>
                            <button class="filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}" aria-controls="advancedFilters">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 5H20L14 12V19L10 17V12L4 5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                                Filtres avances
                            </button>
                        </div>
                        <div class="export-group">
                            <a href="{{ route('reports.daily', ['date' => $dailyDate, 'format' => 'excel']) }}" class="export-link">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 3V16M12 16L7 11M12 16L17 11M5 21H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Exporter Excel
                            </a>
                            <a href="{{ route('reports.daily', ['date' => $dailyDate, 'format' => 'pdf']) }}" class="export-link">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 3V16M12 16L7 11M12 16L17 11M5 21H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                PDF
                            </a>
                        </div>
                    </div>

                    <div class="collapse {{ $showAdvanced ? 'show' : '' }}" id="advancedFilters">
                        <div class="advanced-grid">
                            <div>
                                <label class="form-label small text-muted fw-semibold">Statut</label>
                                <select name="status_id" class="form-select js-tom-select" data-placeholder="Tous les statuts">
                                    <option value="">Tous les statuts</option>
                                    @foreach($statuts as $statut)
                                        <option value="{{ $statut->id }}" @selected($filters['status_id'] == $statut->id)>{{ $statut->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-semibold">Depart (feeder)</label>
                                <select name="departement_id" class="form-select js-tom-select" data-placeholder="Tous les departs">
                                    <option value="">Tous les departs</option>
                                    @foreach($departements as $dep)
                                        <option value="{{ $dep->id }}" @selected($filters['departement_id'] == $dep->id)>{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-semibold">Type d'incident</label>
                                <select id="incident-filter-type" name="type_incident_id" class="form-select js-tom-select" data-placeholder="Tous les types">
                                    <option value="">Tous les types</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" @selected($filters['type_incident_id'] == $type->id)>{{ $type->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="advanced-actions">
                                <a href="{{ route('incidents.index') }}" class="btn btn-link text-muted text-decoration-none">Reinitialiser</a>
                            </div>

                            <div>
                                <label class="form-label small text-muted fw-semibold">Cause</label>
                                <select
                                    id="incident-filter-cause"
                                    name="cause_id"
                                    class="form-select js-tom-select"
                                    data-placeholder="Toutes les causes"
                                    data-selected-cause="{{ $filters['cause_id'] }}"
                                    data-endpoint-template="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}"
                                >
                                    <option value="">Toutes les causes</option>
                                    @foreach($causes as $cause)
                                        <option value="{{ $cause->id }}" @selected($filters['cause_id'] == $cause->id)>{{ $cause->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-semibold">Priorite</label>
                                <select name="priorite_id" class="form-select js-tom-select" data-placeholder="Toutes les priorites">
                                    <option value="">Toutes les priorites</option>
                                    @foreach($priorites as $priorite)
                                        <option value="{{ $priorite->id }}" @selected($filters['priorite_id'] == $priorite->id)>{{ $priorite->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-semibold">Operateur</label>
                                <select name="operateur_id" class="form-select js-tom-select" data-placeholder="Tous les operateurs">
                                    <option value="">Tous les operateurs</option>
                                    @foreach($operateurs as $operateur)
                                        <option value="{{ $operateur->id }}" @selected($filters['operateur_id'] == $operateur->id)>{{ $operateur->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <label class="form-label small text-muted fw-semibold">Du</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label small text-muted fw-semibold">Au</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-danger">Appliquer les filtres</button>
                            <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </form>

                <section class="list-panel">
                    <div class="table-responsive">
                        <table class="table list-table align-middle">
                            <thead>
                                <tr>
                                    <th>Date & Heure</th>
                                    <th>Depart</th>
                                    <th>Type</th>
                                    <th>Cause</th>
                                    <th>Statut</th>
                                    <th>Duree</th>
                                    <th>Operateur</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidents as $incident)
                                    @php
                                        $status = $incident->statut;
                                        $isClosed = (bool) optional($status)->is_final;
                                        $duration = $incident->duree_minutes;

                                        if ($duration === null && $incident->date_debut && $incident->date_fin) {
                                            $duration = $incident->date_debut->diffInMinutes($incident->date_fin);
                                        }

                                        $durationText = $formatDuration($duration);
                                    @endphp
                                    <tr>
                                        <td>{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $incident->departement?->nom ?? '-' }}</td>
                                        <td>{{ $incident->typeIncident?->libelle ?? '-' }}</td>
                                        <td>{{ Str::limit($incident->cause?->libelle ?? '-', 34) }}</td>
                                        <td>
                                            <span class="status-pill {{ $isClosed ? 'status-closed' : 'status-open' }}">
                                                {{ $status?->libelle ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="duration-cell">{{ $durationText }}</td>
                                        <td>{{ $incident->operateur?->name ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="row-actions">
                                                <a href="{{ route('incidents.show', $incident) }}" class="icon-action" title="Voir" aria-label="Voir">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
                                                </a>
                                                @can('incidents.update')
                                                    <a href="{{ route('incidents.edit', $incident) }}" class="icon-action" title="Editer" aria-label="Editer">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M16.8 3.2A2.8 2.8 0 0 1 20.8 7.2L8.5 19.5L3 21L4.5 15.5L16.8 3.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                                                    </a>
                                                @endcan
                                                @can('incidents.delete')
                                                    <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet incident ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="icon-action" title="Supprimer" aria-label="Supprimer">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 6H21M9 6V4H15V6M7 6L8 20H16L17 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">Aucun incident trouve pour ces filtres.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="list-footer">
                        <div class="footer-note">Affichage de {{ $from }} a {{ $to }} sur {{ $incidents->total() }} incidents</div>
                        <div>{{ $incidents->links('pagination::bootstrap-5') }}</div>
                    </div>
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
                <a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">Incidents</a>
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
        const typeSelect = document.getElementById('incident-filter-type');
        const causeSelect = document.getElementById('incident-filter-cause');

        if (typeSelect && causeSelect) {
            const allCauses = @json($causes->map(fn ($cause) => ['id' => $cause->id, 'libelle' => $cause->libelle])->values());
            const endpointTemplate = causeSelect.dataset.endpointTemplate;
            const initialCauseId = String(causeSelect.dataset.selectedCause || '');

            const setOptions = (items, selectedId = '') => {
                const options = [{ value: '', text: 'Toutes les causes' }, ...items.map((item) => ({
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

            const loadCauses = async (typeId, selected = '') => {
                if (!typeId) {
                    setOptions(allCauses, selected);
                    return;
                }

                try {
                    const endpoint = endpointTemplate.replace('__TYPE__', encodeURIComponent(typeId));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load causes');
                    }

                    const causes = await response.json();
                    setOptions(causes, selected);
                } catch (error) {
                    setOptions([], '');
                }
            };

            typeSelect.addEventListener('change', () => {
                loadCauses(typeSelect.value, '');
            });

            if (typeSelect.value) {
                loadCauses(typeSelect.value, initialCauseId);
            } else {
                setOptions(allCauses, initialCauseId);
            }
        }
    </script>
</body>
</html>



