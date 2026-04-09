@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $maxDepartCount = max(1, (int) collect($topDepart)->max('total'));
    $topCauses = collect($byCause)->take(4)->values();
    $causePalette = ['#ef233c', '#facc15', '#dc2626', '#f97316'];
    $totalCauseCount = max(1, (int) $topCauses->sum('total'));

    $cursor = 0;
    $segments = [];
    foreach ($topCauses as $index => $cause) {
        $percent = ($cause['total'] / $totalCauseCount) * 100;
        $start = $cursor;
        $cursor += $percent;
        $segments[] = sprintf('%s %.2f%% %.2f%%', $causePalette[$index % count($causePalette)], $start, $cursor);
    }

    $donutGradient = count($segments) > 0
        ? 'conic-gradient(' . implode(', ', $segments) . ')'
        : 'conic-gradient(#e5e7eb 0% 100%)';

    $avgMinutes = (int) round($kpis['avgDuration'] ?? 0);
    $avgHours = intdiv($avgMinutes, 60);
    $remainingMinutes = $avgMinutes % 60;
    $mttrLabel = $avgHours > 0 ? sprintf('%dh %02dm', $avgHours, $remainingMinutes) : sprintf('%dm', $remainingMinutes);

    $openTrend = $kpis['openCount'] > 0 ? '+' . max(1, (int) ceil($kpis['openCount'] * 0.1)) : '0';
    $resolvedTrend = $todayResolved > 0 ? '+' . $todayResolved : '0';
    $mttrTrend = $weekDelta !== null ? '-' . abs($weekDelta) . '%' : '-';
    $availabilityTrend = $weekDelta !== null ? '+' . max(0, $weekDelta) . '%' : '+0.1%';
    $isFilterOpen = filled($filters['date_from']) || filled($filters['date_to']);

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Operateur';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | CEET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--ceet-red:#ef2433;--ceet-red-dark:#ce1220;--ceet-bg:#f5f6f8;--ceet-border:#e5e7eb;--ceet-text:#1f2937;--ceet-muted:#6b7280;--ceet-shadow:0 14px 32px rgba(15,23,42,.04)}
        html,body{margin:0;width:100%;min-height:100%;background:var(--ceet-bg);color:var(--ceet-text);font-family:"Figtree","Segoe UI",sans-serif}
        .dashboard-shell{min-height:100dvh;display:grid;grid-template-columns:280px 1fr}
        .sidebar{border-right:1px solid var(--ceet-border);background:#f8f9fb;display:flex;flex-direction:column;min-height:100dvh}
        .sidebar-brand{min-height:72px;padding:1rem 1.5rem;border-bottom:1px solid var(--ceet-border);display:flex;align-items:center;gap:.75rem;color:var(--ceet-red);font-weight:700;font-size:1.15rem}
        .sidebar-brand-badge{width:30px;height:30px;border-radius:.5rem;background:var(--ceet-red);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700}
        .sidebar-menu{padding:1rem;display:grid;gap:.3rem}
        .sidebar-link{border-radius:.65rem;color:#1f2937;text-decoration:none;display:flex;align-items:center;gap:.75rem;padding:.72rem .85rem;font-weight:500}
        .sidebar-link:hover{background:#f1f5f9}.sidebar-link.active{background:#eceff3;font-weight:700}
        .sidebar-icon{width:18px;height:18px;color:#111827;flex-shrink:0}
        .sidebar-bottom{margin-top:auto;border-top:1px solid var(--ceet-border);padding:1rem}
        .sidebar-create{width:100%;border:1px solid var(--ceet-border);border-radius:.75rem;background:#fff;color:#111827;font-weight:600;text-decoration:none;display:inline-flex;justify-content:center;align-items:center;gap:.5rem;padding:.72rem .95rem}
        .main-area{min-width:0;display:flex;flex-direction:column;min-height:100dvh}
        .topbar{min-height:72px;border-bottom:1px solid var(--ceet-border);background:#fff;display:flex;align-items:center;justify-content:flex-end;gap:.9rem;padding:.85rem 1.5rem}
        .icon-btn{border:1px solid transparent;border-radius:.5rem;background:#fff;color:#111827;width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center}
        .icon-btn:hover{border-color:var(--ceet-border);background:#f8fafc}
        .user-chip{border-left:1px solid var(--ceet-border);padding-left:.9rem;display:inline-flex;align-items:center;gap:.75rem}
        .user-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(140deg,#f8d64f 0%,#f5a623 100%);color:#3b2f18;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem}
        .content-wrap{padding:1.5rem;display:grid;gap:1.2rem}
        .page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .page-title{margin:0;font-size:2.1rem;font-weight:800}.page-subtitle{margin:.35rem 0 0;color:var(--ceet-muted);font-size:1.04rem}
        .filter-btn{border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:.65rem;font-weight:600;padding:.58rem .95rem}
        .incident-btn{border:0;border-radius:.65rem;background:var(--ceet-red);color:#fff;font-weight:700;padding:.58rem 1rem;text-decoration:none;display:inline-flex;align-items:center;gap:.45rem}
        .incident-btn:hover{background:var(--ceet-red-dark);color:#fff}
        .filter-card,.dashboard-card{border:1px solid var(--ceet-border);border-radius:.9rem;background:#fff;box-shadow:var(--ceet-shadow)}
        .filter-card{padding:1rem}.panel-section{padding:1.2rem}.kpi-card{padding:.95rem 1rem 1rem}
        .kpi-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem}
        .kpi-icon{width:36px;height:36px;border-radius:.625rem;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700}
        .kpi-icon.red{background:#fee2e2;color:#dc2626}.kpi-icon.green{background:#dcfce7;color:#16a34a}.kpi-icon.gray{background:#f3f4f6;color:#111827}.kpi-icon.yellow{background:#fef3c7;color:#d97706}
        .trend-pill{border-radius:999px;border:1px solid #e5e7eb;background:#f9fafb;color:#4b5563;font-size:.76rem;font-weight:700;padding:.16rem .5rem}
        .kpi-label{text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-size:.8rem;margin-bottom:.2rem}.kpi-value{margin:0;font-size:2.25rem;line-height:1.05;font-weight:800;color:#111827}
        .card-heading{font-size:1.65rem;font-weight:800;margin:0}.card-subheading{margin-top:.25rem;color:#6b7280}
        .depart-chart{display:grid;gap:.85rem;margin-top:1.15rem}.depart-row{display:grid;grid-template-columns:110px 1fr 36px;align-items:center;gap:.75rem}
        .depart-label{font-size:.88rem;color:#374151}.depart-track{width:100%;border-radius:999px;background:#f1f5f9;height:17px;overflow:hidden}.depart-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#f1192b 0%,#ef2433 100%)}.depart-count{text-align:right;font-weight:700}
        .role-list{list-style:none;margin:0;padding:0;display:grid;gap:.55rem}.role-item{border:1px solid #e5e7eb;border-radius:.65rem;padding:.6rem .75rem;display:flex;justify-content:space-between;align-items:center;font-weight:600;color:#374151}
        .role-badge-alert{min-width:24px;text-align:center;background:#fee2e2;color:#dc2626;border-radius:999px;font-size:.76rem;font-weight:700;padding:.1rem .4rem}
        .system-health{margin-top:2.2rem;padding-top:1rem;border-top:1px solid #e5e7eb}.health-head{display:flex;justify-content:space-between;align-items:center}.health-pill{background:#22c55e;color:#fff;border-radius:999px;font-size:.76rem;font-weight:700;padding:.12rem .58rem}
        .health-progress{margin-top:.85rem;height:9px;border-radius:999px;background:#e5e7eb;overflow:hidden}.health-progress>span{display:block;height:100%;border-radius:inherit;background:#22c55e}.health-foot{margin-top:.55rem;color:#6b7280;font-size:.78rem}
        .causes-wrap{display:flex;justify-content:center;margin:.8rem 0 1rem}.cause-donut{width:245px;height:245px;border-radius:50%;position:relative;background:var(--donut-gradient)}.cause-donut:after{content:'';position:absolute;inset:30%;border-radius:50%;background:#fff;box-shadow:inset 0 0 0 1px #e5e7eb}
        .causes-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:.8rem;font-size:.95rem}.legend-item{display:inline-flex;align-items:center;gap:.3rem;color:#374151;font-weight:500}.legend-dot{width:11px;height:11px;border-radius:2px;display:inline-block}
        .recent-head{display:flex;align-items:center;justify-content:space-between;gap:.8rem}.recent-link{color:var(--ceet-red);font-weight:700;text-decoration:none}.recent-list{margin-top:1rem;border-top:1px solid #e5e7eb}
        .recent-item{border-bottom:1px solid #e5e7eb;padding:.85rem 0;display:flex;gap:.8rem;align-items:flex-start}.incident-dot{width:8px;height:8px;border-radius:50%;margin-top:.42rem;flex-shrink:0;background:var(--ceet-red)}
        .incident-main{min-width:0;flex:1}.incident-code{margin:0;font-weight:700;font-size:.98rem}.incident-cause{color:#6b7280;font-size:.9rem;margin:.15rem 0 0}
        .incident-meta{text-align:right;flex-shrink:0}.status-chip{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;min-width:76px;font-size:.75rem;font-weight:700;padding:.2rem .55rem}.status-open{background:#ef233c;color:#fff}.status-closed{background:#f3f4f6;color:#4b5563}.incident-time{margin-top:.35rem;color:#9ca3af;font-size:.78rem}
        .analysis-card{border:1px solid #fecaca;border-radius:.9rem;background:#fff8f8;padding:1.2rem;display:flex;align-items:center;justify-content:space-between;gap:1.3rem;flex-wrap:wrap}
        .analysis-title{margin:0;font-size:1.65rem;font-weight:800}.analysis-text{margin:.4rem 0 0;color:#4b5563;max-width:900px;font-size:1.02rem}.analysis-actions{display:flex;gap:.7rem;flex-wrap:wrap}
        .btn-subtle{border:1px solid #d1d5db;border-radius:.65rem;color:#374151;background:#fff;font-weight:600;padding:.55rem .95rem;text-decoration:none}.btn-danger-soft{border:0;border-radius:.65rem;color:#fff;background:var(--ceet-red);font-weight:700;padding:.55rem .95rem;text-decoration:none}
        .dashboard-footer{margin-top:auto;border-top:1px solid var(--ceet-border);background:#fff;color:#6b7280;font-size:.88rem;padding:.8rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .status-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:.35rem}
        @media (max-width:991.98px){.dashboard-shell{grid-template-columns:1fr}.topbar{justify-content:space-between;padding:.75rem 1rem}.content-wrap{padding:1rem}.page-title{font-size:1.75rem}.kpi-value{font-size:1.85rem}.card-heading,.analysis-title{font-size:1.4rem}.depart-row{grid-template-columns:88px 1fr 32px;gap:.55rem}.dashboard-footer{padding:.8rem 1rem}}
    </style>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="sidebar d-none d-lg-flex">
            <div class="sidebar-brand">
                <span class="sidebar-brand-badge">~</span>
                <span>CEET Gestion des Incidents</span>
            </div>
            <nav class="sidebar-menu">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    <span>Tableau de bord</span>
                </a>
                @can('incidents.view')
                    <a href="{{ route('incidents.index') }}" class="sidebar-link {{ request()->routeIs('incidents.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Incidents</span>
                    </a>
                @endcan
                @can('catalogues.view')
                    <a href="{{ route('catalogues.departements.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <span>Departs</span>
                    </a>
                    <a href="{{ route('catalogues.types.index') }}" class="sidebar-link {{ request()->routeIs('catalogues.types.*') || request()->routeIs('catalogues.causes.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none"><path d="M5 4H19V20L12 16L5 20V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                        <span>Types & Causes</span>
                    </a>
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
                    <a href="{{ route('incidents.create') }}" class="sidebar-create"><span>+</span><span>Nouvel Incident</span></a>
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
                    <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
                        <button type="submit" class="icon-btn" aria-label="Se deconnecter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9M16 17L21 12L16 7M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                </div>
            </header>

            <main class="content-wrap">
                @if (session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
                @if (session('error'))<div class="alert alert-danger mb-0">{{ session('error') }}</div>@endif

                <div class="page-head">
                    <div>
                        <h1 class="page-title">Tableau de bord</h1>
                        <p class="page-subtitle">Supervision du reseau electrique en temps reel.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFilters" aria-expanded="{{ $isFilterOpen ? 'true' : 'false' }}" aria-controls="dashboardFilters">Filtrer par date</button>
                        @can('incidents.create')<a href="{{ route('incidents.create') }}" class="incident-btn"><span>+</span><span>Nouveau Signalement</span></a>@endcan
                    </div>
                </div>

                <div class="collapse {{ $isFilterOpen ? 'show' : '' }}" id="dashboardFilters">
                    <div class="filter-card">
                        <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                            <div class="col-12 col-md-4 col-lg-3"><label for="date_from" class="form-label fw-semibold">Date debut</label><input type="date" id="date_from" name="date_from" class="form-control" value="{{ $filters['date_from'] }}"></div>
                            <div class="col-12 col-md-4 col-lg-3"><label for="date_to" class="form-label fw-semibold">Date fin</label><input type="date" id="date_to" name="date_to" class="form-control" value="{{ $filters['date_to'] }}"></div>
                            <div class="col-12 col-md-4 col-lg-4 d-flex gap-2"><button type="submit" class="btn btn-danger">Appliquer</button><a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reinitialiser</a></div>
                        </form>
                    </div>
                </div>

                <section class="row g-3">
                    <div class="col-12 col-md-6 col-xl-3"><article class="dashboard-card kpi-card"><div class="kpi-top"><span class="kpi-icon red">!</span><span class="trend-pill">{{ $openTrend }}</span></div><div class="kpi-label">Incidents en cours</div><p class="kpi-value">{{ number_format($kpis['openCount']) }}</p></article></div>
                    <div class="col-12 col-md-6 col-xl-3"><article class="dashboard-card kpi-card"><div class="kpi-top"><span class="kpi-icon green">v</span><span class="trend-pill">{{ $resolvedTrend }}</span></div><div class="kpi-label">Resolus aujourd'hui</div><p class="kpi-value">{{ number_format($todayResolved) }}</p></article></div>
                    <div class="col-12 col-md-6 col-xl-3"><article class="dashboard-card kpi-card"><div class="kpi-top"><span class="kpi-icon gray">T</span><span class="trend-pill">{{ $mttrTrend }}</span></div><div class="kpi-label">Temps moyen (MTTR)</div><p class="kpi-value">{{ $mttrLabel }}</p></article></div>
                    <div class="col-12 col-md-6 col-xl-3"><article class="dashboard-card kpi-card"><div class="kpi-top"><span class="kpi-icon yellow">S</span><span class="trend-pill">{{ $availabilityTrend }}</span></div><div class="kpi-label">Disponibilite reseau</div><p class="kpi-value">{{ number_format($availabilityRate, 1) }}%</p></article></div>
                </section>

                <section class="row g-3">
                    <div class="col-12 col-xl-8">
                        <article class="dashboard-card panel-section">
                            <h2 class="card-heading">Repartition par Depart</h2>
                            <p class="card-subheading">Top 7 des departs avec le plus grand nombre d'incidents actifs.</p>
                            <div class="depart-chart">
                                @forelse($topDepart as $entry)
                                    @php $barWidth = max(6, min(100, round(($entry['total'] / $maxDepartCount) * 100))); @endphp
                                    <div class="depart-row"><span class="depart-label">{{ Str::limit($entry['label'], 16) }}</span><div class="depart-track"><span class="depart-fill" style="width: {{ $barWidth }}%;"></span></div><span class="depart-count">{{ $entry['total'] }}</span></div>
                                @empty
                                    <div class="text-muted">Aucune donnee disponible pour les departs.</div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-xl-4">
                        <article class="dashboard-card panel-section h-100">
                            <h2 class="card-heading">Actions par Role</h2>
                            <p class="card-subheading">Acces rapide selon votre profil.</p>
                            <ul class="role-list mt-3">
                                @forelse($roleCounts as $index => $roleCount)
                                    <li class="role-item"><span>{{ $roleCount['label'] }}</span>@if($index===0)<span class="role-badge-alert">{{ $roleCount['count'] }}</span>@else<span>{{ $roleCount['count'] }}</span>@endif</li>
                                @empty
                                    <li class="text-muted">Aucun role detecte.</li>
                                @endforelse
                            </ul>
                            <div class="system-health">
                                <div class="health-head"><span class="fw-semibold">Sante du Systeme</span><span class="health-pill">Stable</span></div>
                                <div class="health-progress"><span style="width: {{ max(5, min(100, round($availabilityRate))) }}%;"></span></div>
                                <div class="health-foot">Derniere verification : {{ $lastCheckAt }}</div>
                            </div>
                        </article>
                    </div>
                </section>
                <section class="row g-3">
                    <div class="col-12 col-xl-6">
                        <article class="dashboard-card panel-section">
                            <h2 class="card-heading">Causes Frequentes</h2>
                            <p class="card-subheading">Repartition des incidents par cause identifiee ce mois-ci.</p>
                            <div class="causes-wrap"><div class="cause-donut" style="--donut-gradient: {{ $donutGradient }};"></div></div>
                            <div class="causes-legend">
                                @forelse($topCauses as $index => $cause)
                                    <span class="legend-item"><span class="legend-dot" style="background: {{ $causePalette[$index % count($causePalette)] }};"></span><span>{{ $cause['label'] }}</span></span>
                                @empty
                                    <span class="text-muted">Aucune cause enregistree.</span>
                                @endforelse
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-xl-6">
                        <article class="dashboard-card panel-section h-100">
                            <div class="recent-head">
                                <div><h2 class="card-heading mb-0">Incidents Recents</h2><p class="card-subheading mb-0">Dernieres 24 heures</p></div>
                                @can('incidents.view')<a href="{{ route('incidents.index') }}" class="recent-link">Voir tout</a>@endcan
                            </div>
                            <div class="recent-list">
                                @forelse($recentIncidents as $incident)
                                    @php
                                        $isClosed = (bool) optional($incident->statut)->is_final;
                                        $startedAt = $incident->date_debut ? Carbon::parse($incident->date_debut) : null;
                                        $deptLabel = optional($incident->departement)->nom ?? 'N/A';
                                        $causeLabel = optional($incident->cause)->libelle ?? 'Cause non renseignee';
                                    @endphp
                                    <div class="recent-item">
                                        <span class="incident-dot"></span>
                                        <div class="incident-main">
                                            <p class="incident-code">{{ $incident->code_incident }} - {{ $deptLabel }}</p>
                                            <p class="incident-cause">{{ $causeLabel }}</p>
                                        </div>
                                        <div class="incident-meta">
                                            <span class="status-chip {{ $isClosed ? 'status-closed' : 'status-open' }}">{{ $isClosed ? 'Resolu' : 'En cours' }}</span>
                                            <div class="incident-time">{{ $startedAt ? $startedAt->diffForHumans() : '-' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted py-3">Aucun incident recent.</div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </section>

                <section class="analysis-card">
                    <div>
                        <h2 class="analysis-title">Analyse de la Performance Hebdomadaire</h2>
                        <p class="analysis-text">Le temps moyen de resolution a evolue de {{ $weekDelta !== null ? abs($weekDelta) . '%' : '0%' }} par rapport a la semaine precedente. Les departs de {{ $focusText }} restent les zones les plus sensibles necessitant une maintenance preventive.</p>
                    </div>
                    <div class="analysis-actions">
                        @can('incidents.view')<a class="btn-subtle" href="{{ route('reports.monthly', ['month' => now()->format('Y-m'), 'format' => 'pdf']) }}">Rapport PDF Complet</a>@endcan
                        @can('catalogues.manage')<a class="btn-danger-soft" href="{{ route('catalogues.departements.index') }}">Gerer les Departs</a>@endcan
                    </div>
                </section>
            </main>

            <footer class="dashboard-footer">
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


