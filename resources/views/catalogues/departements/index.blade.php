@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';
    $selected = $selectedDepartement;

    $totalPowerMw = (float) ($stats['totalPowerMw'] ?? 0);
    $powerLabel = $totalPowerMw >= 1000
        ? number_format($totalPowerMw / 1000, 1, '.', ' ') . ' GW'
        : number_format($totalPowerMw, 0, ',', ' ') . ' MW';

    $queryWithoutSelected = request()->except('selected');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Catalogue des Departs | CEET</title>
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

        .head-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            flex-wrap: wrap;
        }

        .head-title {
            margin: 0;
            font-size: 2.05rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .head-subtitle {
            margin-top: 0.2rem;
            color: #6b7280;
            font-size: 0.96rem;
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
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.9rem;
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
            color: #111827;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .stat-card {
            border: 1px solid var(--ceet-border);
            border-radius: 0.72rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
            padding: 0.75rem 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .stat-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .stat-label {
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-size: 0.7rem;
            color: #6b7280;
            margin-bottom: 0.02rem;
            font-weight: 700;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .panel {
            border: 1px solid var(--ceet-border);
            border-radius: 0.78rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
        }

        .panel-body {
            padding: 0.9rem;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 370px;
            gap: 0.9rem;
            align-items: start;
        }
        .search-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.55rem;
            margin-bottom: 0.75rem;
        }

        .table-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 0.7rem;
            overflow: hidden;
        }

        .list-table {
            margin: 0;
        }

        .list-table thead th {
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            color: #6b7280;
            text-transform: uppercase;
            background: #f9fafb;
            white-space: nowrap;
            border-bottom-color: #e5e7eb;
        }

        .list-table td {
            border-bottom-color: #edf2f7;
            vertical-align: middle;
            font-size: 0.91rem;
        }

        .selected-row {
            background: #fff7f8;
        }

        .status-pill {
            border-radius: 999px;
            padding: 0.12rem 0.54rem;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
        }

        .status-ok {
            background: #ecfdf3;
            color: #15803d;
        }

        .status-off {
            background: #fee2e2;
            color: #b91c1c;
        }

        .row-actions {
            display: inline-flex;
            gap: 0.32rem;
            justify-content: flex-end;
        }

        .icon-action {
            width: 28px;
            height: 28px;
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

        .detail-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.7rem;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid #eceff3;
        }

        .detail-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 800;
        }

        .detail-subtitle {
            margin-top: 0.1rem;
            color: #6b7280;
            font-size: 0.88rem;
        }

        .mini-status {
            font-size: 0.8rem;
            font-weight: 700;
            color: #374151;
            white-space: nowrap;
        }

        .form-label {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 0.22rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border-color: #d8dee5;
            font-size: 0.91rem;
            min-height: 38px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #f6a6ae;
            box-shadow: 0 0 0 0.16rem rgba(239, 36, 51, 0.14);
        }

        .notes-area {
            min-height: 92px;
        }

        .footer-row {
            margin-top: 0.8rem;
            border-top: 1px solid #eceff3;
            padding-top: 0.8rem;
            display: flex;
            justify-content: space-between;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .modal-upload-zone {
            border: 1px dashed #d1d5db;
            border-radius: 0.75rem;
            background: #f9fafb;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .modal-upload-zone:hover {
            border-color: var(--ceet-red);
            background: #fff7f8;
        }

        .upload-file-name {
            margin-top: 0.45rem;
            color: #6b7280;
            font-size: 0.85rem;
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
            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .catalog-grid {
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

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .search-row {
                grid-template-columns: 1fr;
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
                <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Departs</span>
                </a>
                <a href="{{ route('catalogues.types.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.types.*') || request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M5 4H19V20L12 16L5 20V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    <span>Types & Causes</span>
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M5 19V5M10 19V9M15 19V13M20 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>Reporting</span>
                </a>
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

            @can('catalogues.manage')
                <div class="sidebar-bottom">
                    <a href="{{ route('catalogues.departements.create') }}" class="sidebar-create">
                        <span>+</span>
                        <span>Nouveau Depart</span>
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
                @if(session('success'))
                    <div class="alert alert-success mb-0">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-0">{{ session('error') }}</div>
                @endif

                <div class="head-row">
                    <div>
                        <h1 class="head-title">Catalogue des Departs</h1>
                        <p class="head-subtitle">Gestion et inventaire technique des {{ $stats['totalCount'] }} departs du reseau national.</p>
                    </div>
                    <div class="head-actions">
                        @can('catalogues.manage')
                            <button type="button" class="btn-action" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 3V16M12 16L7 11M12 16L17 11M5 21H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Importer
                            </button>
                        @endcan
                        <button type="button" id="exportDepartementsCsv" class="btn-action">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 3V16M12 16L7 11M12 16L17 11M5 21H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Exporter
                        </button>
                        @can('catalogues.manage')
                            <a href="{{ route('catalogues.departements.create') }}" class="btn-action primary">+ Nouveau Depart</a>
                        @endcan
                    </div>
                </div>

                <section class="stat-grid">
                    <article class="stat-card">
                        <span class="stat-icon">S</span>
                        <div><div class="stat-label">Total departs</div><div class="stat-value">{{ number_format($stats['totalCount']) }}</div></div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-icon" style="background:#ecfdf3;color:#16a34a;">O</span>
                        <div><div class="stat-label">En service</div><div class="stat-value">{{ number_format($stats['activeCount']) }}</div></div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-icon" style="background:#eff6ff;color:#1d4ed8;">Z</span>
                        <div><div class="stat-label">Zones couvertes</div><div class="stat-value">{{ number_format($stats['zoneCount']) }}</div></div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-icon" style="background:#fef3c7;color:#d97706;">P</span>
                        <div><div class="stat-label">Puissance totale</div><div class="stat-value">{{ $powerLabel }}</div></div>
                    </article>
                </section>

                <section class="catalog-grid">
                    <article class="panel">
                        <div class="panel-body">
                            <form method="GET" action="{{ route('catalogues.departements.index') }}" class="search-row">
                                <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Rechercher par nom ou ID...">
                                <button class="btn btn-outline-secondary" type="submit">Rechercher</button>
                            </form>

                            <div class="table-wrap">
                                <div class="table-responsive">
                                    <table class="table list-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom du Depart</th>
                                                <th>Zone</th>
                                                <th>Etat</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($departements as $dep)
                                                @php
                                                    $isSelected = $selected && $selected->id === $dep->id;
                                                    $rowRoute = route('catalogues.departements.index', array_merge($queryWithoutSelected, ['selected' => $dep->id]));
                                                @endphp
                                                <tr class="{{ $isSelected ? 'selected-row' : '' }}" data-row="1" data-code="{{ $dep->code }}" data-name="{{ $dep->nom }}" data-zone="{{ $dep->zone }}" data-status="{{ $dep->is_active ? 'Operationnel' : 'Defaut' }}" data-power="{{ $dep->charge_maximale }} {{ $dep->charge_unite }}" data-poste="{{ $dep->poste_repartition }}">
                                                    <td><a href="{{ $rowRoute }}" class="text-decoration-none fw-semibold">{{ $dep->code }}</a></td>
                                                    <td>{{ $dep->nom }}</td>
                                                    <td>{{ $dep->zone ?: '-' }}</td>
                                                    <td>
                                                        <span class="status-pill {{ $dep->is_active ? 'status-ok' : 'status-off' }}">{{ $dep->is_active ? 'Operationnel' : 'Defaut' }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="row-actions">
                                                            <a href="{{ $rowRoute }}" class="icon-action" title="Selectionner" aria-label="Selectionner">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
                                                            </a>
                                                            @can('catalogues.manage')
                                                                <form method="POST" action="{{ route('catalogues.departements.destroy', $dep) }}" class="d-inline" onsubmit="return confirm('Supprimer ce depart ?');">
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
                                                <tr><td colspan="5" class="text-center py-4 text-muted">Aucun depart trouve.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-2">
                                {{ $departements->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </article>

                    <article class="panel">
                        <div class="panel-body">
                            @if($selected)
                                <div class="detail-head">
                                    <div>
                                        <h2 class="detail-title">{{ $selected->nom }}</h2>
                                        <div class="detail-subtitle">Modification technique de l'unite {{ $selected->code }}</div>
                                    </div>
                                    <div class="mini-status">{{ $selected->is_active ? 'Operationnel' : 'Defaut' }}</div>
                                </div>

                                <form method="POST" action="{{ route('catalogues.departements.update', $selected) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Nom de l'entite</label>
                                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $selected->nom) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">ID reseau</label>
                                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $selected->code) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Zone d'affectation</label>
                                            <input type="text" name="zone" class="form-control @error('zone') is-invalid @enderror" value="{{ old('zone', $selected->zone) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Puissance nominale</label>
                                            <input type="number" step="0.01" name="charge_maximale" class="form-control @error('charge_maximale') is-invalid @enderror" value="{{ old('charge_maximale', $selected->charge_maximale) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('charge_maximale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Unite</label>
                                            <input type="text" name="charge_unite" class="form-control @error('charge_unite') is-invalid @enderror" value="{{ old('charge_unite', $selected->charge_unite ?: 'MW') }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('charge_unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Poste de rattachement</label>
                                            <input type="text" name="poste_repartition" class="form-control @error('poste_repartition') is-invalid @enderror" value="{{ old('poste_repartition', $selected->poste_repartition) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('poste_repartition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Poste source</label>
                                            <input type="text" name="poste_source" class="form-control @error('poste_source') is-invalid @enderror" value="{{ old('poste_source', $selected->poste_source) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('poste_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Direction exploitation</label>
                                            <input type="text" name="direction_exploitation" class="form-control @error('direction_exploitation') is-invalid @enderror" value="{{ old('direction_exploitation', $selected->direction_exploitation) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('direction_exploitation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Arrivee</label>
                                            <input type="text" name="arrivee" class="form-control @error('arrivee') is-invalid @enderror" value="{{ old('arrivee', $selected->arrivee) }}" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                            @error('arrivee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes techniques</label>
                                            <textarea name="description" class="form-control notes-area @error('description') is-invalid @enderror" {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>{{ old('description', $selected->description) }}</textarea>
                                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="is_active_{{ $selected->id }}" name="is_active" {{ old('is_active', $selected->is_active) ? 'checked' : '' }} {{ auth()->user()->can('catalogues.manage') ? '' : 'disabled' }}>
                                                <label class="form-check-label" for="is_active_{{ $selected->id }}">Depart actif (operationnel)</label>
                                            </div>
                                        </div>
                                    </div>

                                    @can('catalogues.manage')
                                        <div class="footer-row">
                                            <button type="submit" class="btn-action primary">Enregistrer</button>
                                            <a href="{{ route('catalogues.departements.index', array_merge($queryWithoutSelected, ['selected' => $selected->id])) }}" class="btn-action">Reinitialiser</a>
                                        </div>
                                    @endcan
                                </form>
                            @else
                                <div class="text-center py-5 text-muted">Selectionnez un depart dans la liste pour afficher ses details.</div>
                            @endif
                        </div>
                    </article>
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
                <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">Departs</a>
                <a href="{{ route('catalogues.types.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.types.*') || request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Types & Causes</a>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reporting</a>
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

    @can('catalogues.manage')
        <div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="bulkImportModalLabel">Mise a jour groupee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Telechargez votre fichier Excel (.xlsx) pour mettre a jour les informations des departs simultanement.</p>
                        <input type="file" id="bulkFileInput" class="d-none" accept=".xlsx,.xls,.csv">
                        <label for="bulkFileInput" class="modal-upload-zone w-100" id="bulkUploadZone">
                            <div>
                                <div class="fw-semibold">Glissez votre fichier ici</div>
                                <div class="text-muted small mt-1">Formats acceptes: Excel, CSV (max 10MB)</div>
                                <div id="bulkFileName" class="upload-file-name">Aucun fichier selectionne</div>
                            </div>
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" id="confirmImportBtn" class="btn btn-danger">Valider l'importation</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <script>
        (() => {
            const exportBtn = document.getElementById('exportDepartementsCsv');

            if (exportBtn) {
                exportBtn.addEventListener('click', () => {
                    const rows = Array.from(document.querySelectorAll('tr[data-row="1"]'));

                    if (rows.length === 0) {
                        alert('Aucune donnee a exporter.');
                        return;
                    }

                    const header = ['Code', 'Nom', 'Zone', 'Statut', 'Puissance', 'Poste'];
                    const lines = [header.join(';')];

                    rows.forEach((row) => {
                        const values = [
                            row.dataset.code || '',
                            row.dataset.name || '',
                            row.dataset.zone || '',
                            row.dataset.status || '',
                            row.dataset.power || '',
                            row.dataset.poste || '',
                        ].map((value) => '"' + String(value).replace(/"/g, '""') + '"');

                        lines.push(values.join(';'));
                    });

                    const csv = '\uFEFF' + lines.join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'catalogue-departs-' + new Date().toISOString().slice(0, 10) + '.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }

            const fileInput = document.getElementById('bulkFileInput');
            const fileName = document.getElementById('bulkFileName');
            const confirmBtn = document.getElementById('confirmImportBtn');

            if (fileInput && fileName) {
                fileInput.addEventListener('change', () => {
                    const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                    fileName.textContent = file ? file.name : 'Aucun fichier selectionne';
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                        alert('Veuillez selectionner un fichier avant de continuer.');
                        return;
                    }

                    alert('Import prepare. La synchronisation automatique sera activee dans la prochaine iteration.');
                });
            }
        })();
    </script>
</body>
</html>


