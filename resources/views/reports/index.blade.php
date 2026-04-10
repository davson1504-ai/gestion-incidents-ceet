@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';

    $metricCards = [
        [
            'label' => 'Total incidents',
            'value' => number_format($totalIncidents),
            'trend' => $incidentDelta,
            'sub' => 'Incidents enregistres ce mois',
        ],
        [
            'label' => 'Duree moyenne',
            'value' => $avgDuration . ' min',
            'trend' => $avgDurationDelta,
            'sub' => 'Temps moyen de retablissement',
        ],
        [
            'label' => 'Taux de resolution',
            'value' => number_format($resolutionRate, 1) . '%',
            'trend' => $resolutionDelta,
            'sub' => 'Incidents clos avec succes',
        ],
        [
            'label' => 'Depart le plus sollicite',
            'value' => $topDepartName,
            'trend' => $topDepartDelta,
            'sub' => 'Secteur avec le plus d activite',
        ],
    ];

    $buildTrendLabel = static function (float $trend): string {
        $sign = $trend > 0 ? '+' : '';
        return $sign . number_format($trend, 1) . '%';
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reporting & Analyse | CEET</title>
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
        .filters-form {
            border: 1px solid var(--ceet-border);
            border-radius: 0.78rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
            padding: 0.75rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.75rem;
            align-items: end;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.6rem;
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

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .kpi-card {
            border: 1px solid var(--ceet-border);
            border-radius: 0.72rem;
            background: #fff;
            box-shadow: var(--ceet-shadow);
            padding: 0.72rem;
        }

        .kpi-label {
            font-size: 0.76rem;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin: 0;
        }

        .kpi-sub {
            margin-top: 0.24rem;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .kpi-trend {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .trend-up {
            color: #16a34a;
        }

        .trend-down {
            color: #dc2626;
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

        .panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.6rem;
        }

        .panel-title {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .panel-subtitle {
            margin-top: 0.16rem;
            color: #6b7280;
            font-size: 0.82rem;
        }

        .chart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 0.7rem;
        }

        .chart-box {
            min-height: 255px;
        }

        .chart-canvas {
            width: 100%;
            height: 220px;
        }

        .type-donut-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.5rem 0;
        }

        .type-donut {
            width: 165px;
            height: 165px;
            border-radius: 50%;
            position: relative;
            background: var(--donut-gradient);
        }

        .type-donut::after {
            content: '';
            position: absolute;
            inset: 30%;
            border-radius: 50%;
            background: #fff;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }

        .legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            justify-content: center;
            font-size: 0.77rem;
            margin-top: 0.5rem;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #374151;
            font-weight: 600;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 0.7rem;
        }

        .cause-bars {
            display: grid;
            gap: 0.65rem;
            margin-top: 0.5rem;
        }

        .cause-row {
            display: grid;
            grid-template-columns: 120px 1fr 34px;
            align-items: center;
            gap: 0.5rem;
        }

        .cause-label {
            font-size: 0.8rem;
            color: #374151;
        }

        .cause-track {
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .cause-fill {
            height: 100%;
            border-radius: inherit;
            background: #facc15;
        }

        .depart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .depart-table thead th {
            font-size: 0.74rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.45rem 0.35rem;
            text-align: left;
            white-space: nowrap;
        }

        .depart-table tbody td {
            border-bottom: 1px solid #edf2f7;
            padding: 0.45rem 0.35rem;
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .network-pill {
            border-radius: 999px;
            padding: 0.1rem 0.45rem;
            font-size: 0.68rem;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
        }

        .pill-critical {
            background: #fee2e2;
            color: #b91c1c;
        }

        .pill-stable {
            background: #ecfdf3;
            color: #15803d;
        }

        .pill-optimal {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .load-wrap {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .load-track {
            flex: 1;
            min-width: 70px;
            height: 6px;
            border-radius: 999px;
            background: #f3f4f6;
            overflow: hidden;
        }

        .load-fill {
            height: 100%;
            background: #ef2433;
            border-radius: inherit;
        }

        .load-value {
            font-size: 0.72rem;
            color: #6b7280;
            width: 34px;
            text-align: right;
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
            .filters-grid,
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .chart-layout,
            .bottom-grid {
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

            .filters-form {
                grid-template-columns: 1fr;
            }

            .filters-grid,
            .kpi-grid {
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
                <a class="sidebar-link catalogue-toggle d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold is-catalogue-current' : '' }}" data-bs-toggle="collapse" href="#desktopCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="desktopCatalogueMenu">
                    <span>Catalogue</span>
                    <svg class="sidebar-icon catalogue-chevron" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="desktopCatalogueMenu">
                    <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">D&eacute;partements</a>
                    <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                    <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                    <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                    <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorit&eacute;s</a>
                </div>
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
                <div class="head-row">
                    <div>
                        <h1 class="head-title">Reporting & Analyse</h1>
                        <p class="head-subtitle">Consultez les indicateurs de performance et exportez les rapports periodiques.</p>
                    </div>
                    <div class="head-actions">
                        <a href="{{ route('reports.monthly', array_merge($exportQuery, ['format' => 'excel'])) }}" class="btn-action">Exporter Excel</a>
                        <a href="{{ route('reports.monthly', array_merge($exportQuery, ['format' => 'pdf'])) }}" class="btn-action primary">Generer PDF</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('reports.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <div>
                            <label class="form-label">Periode</label>
                            <select name="period" class="form-select">
                                @foreach($periodOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected($filters['period'] === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Depart</label>
                            <select name="departement_id" class="form-select js-tom-select" data-placeholder="Tous les departs">
                                <option value="">Tous les departs</option>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" @selected((string) $filters['departement_id'] === (string) $departement->id)>{{ $departement->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Cause</label>
                            <select name="cause_id" class="form-select js-tom-select" data-placeholder="Toutes les causes">
                                <option value="">Toutes les causes</option>
                                @foreach($causes as $cause)
                                    <option value="{{ $cause->id }}" @selected((string) $filters['cause_id'] === (string) $cause->id)>{{ $cause->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Reset</label>
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary w-100">Reinitialiser</a>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-danger">Actualiser</button>
                    </div>
                </form>
                <section class="kpi-grid">
                    @foreach($metricCards as $card)
                        @php $trendValue = (float) $card['trend']; @endphp
                        <article class="kpi-card">
                            <div class="kpi-label">{{ $card['label'] }}</div>
                            <p class="kpi-value">{{ $card['value'] }}</p>
                            <div class="kpi-sub">{{ $card['sub'] }}</div>
                            <div class="kpi-trend {{ $trendValue >= 0 ? 'trend-up' : 'trend-down' }}">{{ $buildTrendLabel($trendValue) }} vs periode precedente</div>
                        </article>
                    @endforeach
                </section>

                <section class="chart-layout">
                    <article class="panel chart-box">
                        <div class="panel-body">
                            <div class="panel-head">
                                <div>
                                    <h2 class="panel-title">Evolution des incidents</h2>
                                    <p class="panel-subtitle">Analyse temporelle du volume et de la duree moyenne de resolution.</p>
                                </div>
                            </div>
                            <canvas id="evolutionChart" class="chart-canvas"></canvas>
                        </div>
                    </article>

                    <article class="panel chart-box">
                        <div class="panel-body">
                            <div class="panel-head">
                                <div>
                                    <h2 class="panel-title">Repartition par Type</h2>
                                    <p class="panel-subtitle">Analyse des categories incidents dominantes.</p>
                                </div>
                            </div>
                            <div class="type-donut-wrap">
                                <div class="type-donut" style="--donut-gradient: {{ $typeDonutGradient }};"></div>
                            </div>
                            <div class="legend-row">
                                @foreach($byType as $index => $typeItem)
                                    <span class="legend-item"><span class="legend-dot" style="background: {{ $typePalette[$index % count($typePalette)] }};"></span>{{ $typeItem['label'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    </article>
                </section>

                <section class="bottom-grid">
                    <article class="panel">
                        <div class="panel-body">
                            <div class="panel-head">
                                <div>
                                    <h2 class="panel-title">Repartition par Cause</h2>
                                    <p class="panel-subtitle">Identification des origines frequentes.</p>
                                </div>
                            </div>
                            <div class="cause-bars">
                                @forelse($causeBars as $causeBar)
                                    <div class="cause-row">
                                        <span class="cause-label">{{ Str::limit($causeBar['label'], 16) }}</span>
                                        <div class="cause-track"><span class="cause-fill" style="width: {{ $causeBar['percent'] }}%;"></span></div>
                                        <span class="small text-muted">{{ $causeBar['total'] }}</span>
                                    </div>
                                @empty
                                    <div class="text-muted">Aucune donnee de cause pour cette periode.</div>
                                @endforelse
                            </div>
                        </div>
                    </article>

                    <article class="panel">
                        <div class="panel-body">
                            <div class="panel-head">
                                <div>
                                    <h2 class="panel-title">Top 5 des Departs Critiques</h2>
                                    <p class="panel-subtitle">Departs avec le plus grand nombre d incidents enregistres.</p>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="depart-table">
                                    <thead>
                                        <tr>
                                            <th>Designation depart</th>
                                            <th>Nb incidents</th>
                                            <th>Statut reseau</th>
                                            <th>Charge actuelle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($criticalDepartRows as $row)
                                            <tr>
                                                <td><span class="fw-semibold">{{ strtoupper($row['label']) }}</span></td>
                                                <td>{{ $row['total'] }}</td>
                                                <td>
                                                    <span class="network-pill {{ $row['network_status'] === 'Critique' ? 'pill-critical' : ($row['network_status'] === 'Stable' ? 'pill-stable' : 'pill-optimal') }}">{{ $row['network_status'] }}</span>
                                                </td>
                                                <td>
                                                    <div class="load-wrap">
                                                        <span class="load-track"><span class="load-fill" style="width: {{ $row['load'] }}%;"></span></span>
                                                        <span class="load-value">{{ $row['load'] }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-muted py-3">Aucun depart disponible pour cette periode.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
                <a class="sidebar-link catalogue-toggle d-flex justify-content-between align-items-center {{ request()->routeIs('catalogues.*') ? 'active fw-semibold is-catalogue-current' : '' }}" data-bs-toggle="collapse" href="#mobileCatalogueMenu" role="button" aria-expanded="{{ request()->routeIs('catalogues.*') ? 'true' : 'false' }}" aria-controls="mobileCatalogueMenu">
                    <span>Catalogue</span>
                    <svg class="sidebar-icon catalogue-chevron" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <div class="collapse {{ request()->routeIs('catalogues.*') ? 'show' : '' }}" id="mobileCatalogueMenu">
                    <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">D&eacute;partements</a>
                    <a href="{{ route('catalogues.types.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}">Types d'incidents</a>
                    <a href="{{ route('catalogues.causes.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">Causes</a>
                    <a href="{{ route('catalogues.statuts.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}">Statuts</a>
                    <a href="{{ route('catalogues.priorites.index') }}" class="sidebar-link ps-4 {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}">Priorit&eacute;s</a>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const evolutionCanvas = document.getElementById('evolutionChart');
            if (!evolutionCanvas) {
                return;
            }

            new Chart(evolutionCanvas, {
                type: 'line',
                data: {
                    labels: @json($evolutionLabels),
                    datasets: [
                        {
                            label: 'Nombre incidents',
                            data: @json($evolutionIncidentData),
                            borderColor: '#ef2433',
                            backgroundColor: 'rgba(239, 36, 51, 0.12)',
                            tension: 0.35,
                            fill: false,
                            pointRadius: 2.5,
                        },
                        {
                            label: 'Duree moyenne (min)',
                            data: @json($evolutionDurationData),
                            borderColor: '#facc15',
                            backgroundColor: 'rgba(250, 204, 21, 0.18)',
                            tension: 0.35,
                            fill: false,
                            pointRadius: 2.5,
                        }
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        })();
    </script>
</body>
</html>

