@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    $userName = $currentUser?->name ?? 'Administrateur CEET';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $roleName = 'ADMINISTRATEUR';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = mb_strtoupper($currentUser->getRoleNames()->first() ?? 'ADMINISTRATEUR');
    }

    $canViewIncidents = ($currentUser?->can('incidents.view') ?? false) || ($currentUser?->can('incidents.view.assigned') ?? false);
    $canCreateIncident = $currentUser?->can('incidents.create') ?? false;
    $canExportIncidents = $currentUser?->can('incidents.export') ?? false;
    $canViewUsers = $currentUser?->can('users.view') ?? false;
    $canViewCatalogues = $currentUser?->can('catalogues.view') ?? false;
    $canViewReports = $currentUser?->can('reporting.view') ?? false;
    $canViewSystem = ($currentUser?->isAdmin() ?? false) || ($currentUser?->can('system.view') ?? false);
    $canViewLogs = ($currentUser?->isAdmin() ?? false) || ($currentUser?->can('logs.view') ?? false);

    $totalIncidents = (int) data_get($kpis ?? [], 'total', 0);
    $openIncidents = (int) data_get($kpis ?? [], 'openCount', 0);
    $closedIncidents = (int) data_get($kpis ?? [], 'closedCount', 0);
    $avgDuration = data_get($kpis ?? [], 'avgDuration');
    $todayResolved = (int) ($todayResolved ?? 0);
    $availabilityRate = (float) ($availabilityRate ?? 0);
    $totalUsers = (int) ($totalUsers ?? 0);

    $avgDurationLabel = $avgDuration
        ? floor($avgDuration / 60) . 'h ' . ((int) $avgDuration % 60) . 'm'
        : 'N/A';

    $recentIncidents = collect($recentIncidents ?? []);
    $adminUsers = collect($adminUsers ?? []);
    $roleCounts = collect($roleCounts ?? []);
    $topDepartements = collect($topDepart ?? [])->take(4);
    $lastCheckAt = $lastCheckAt ?? now()->format('H:i');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Administrateur - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/admin-dashboard.css',
        'resources/js/app.js',
        'resources/js/pages/admin-dashboard.js'
    ])
</head>

<body class="ceet-admin-dashboard-page">
    <div class="ceet-admin-shell" data-admin-dashboard>
        <div class="ceet-dashboard-overlay" data-dashboard-overlay></div>

        <aside class="ceet-admin-sidebar" data-dashboard-sidebar>
            <div class="ceet-admin-brand">
                <div class="ceet-admin-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-admin-nav" aria-label="Navigation administrateur">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-admin-nav-link'])
            </nav>

            <div class="ceet-admin-sidebar-user">
                <div class="ceet-admin-sidebar-user-main">
                    <div class="ceet-admin-avatar">{{ $initials }}</div>

                    <div>
                        <strong>{{ $userName }}</strong>
                        <span>{{ $roleName }}</span>
                    </div>
                </div>

                <form action="{{ $safeRoute('logout', [], '#') }}" method="POST" class="ceet-admin-logout-form">
                    @csrf

                    <button type="submit" class="ceet-admin-logout-button">
                        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <header class="ceet-admin-topbar">
            <button type="button" class="ceet-admin-menu-btn" data-dashboard-sidebar-toggle aria-label="Ouvrir le menu" aria-expanded="false">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>

            <form action="{{ $safeRoute('incidents.index', [], '/incidents') }}" method="GET" class="ceet-admin-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="search" name="q" placeholder="Rechercher un incident, un départ ou un utilisateur..." autocomplete="off">
            </form>

            <div class="ceet-admin-top-actions">
                <a href="{{ $safeRoute('notifications.index', [], '#') }}" class="ceet-admin-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                    <span class="ceet-admin-notification-dot"></span>
                </a>

                <a href="{{ $safeRoute('profile.edit', [], '/profile') }}" class="ceet-admin-icon-btn" aria-label="Aide et profil">
                    <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
                </a>

                <div class="ceet-admin-top-divider"></div>

                <div class="ceet-admin-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-admin-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

        <main class="ceet-admin-main">
            <section class="ceet-admin-page-header">
                <div>
                    <h2>Dashboard </h2>
                    <div class="ceet-admin-breadcrumb">
                        <span>Administration système</span>
                        <strong>Vue globale CEET</strong>
                    </div>
                </div>
            </section>

            <section class="ceet-admin-stats-grid" aria-label="Indicateurs administrateur">
                <article class="ceet-admin-stat-card">
                    <div class="ceet-admin-stat-head">
                        <span>Total incidents</span>
                        <span class="material-symbols-outlined" aria-hidden="true">bolt</span>
                    </div>
                    <div class="ceet-admin-stat-body">
                        <strong>{{ number_format($totalIncidents, 0, ',', ' ') }}</strong>
                        <small>{{ $todayResolved }} résolu(s) aujourd’hui</small>
                    </div>
                </article>

                <article class="ceet-admin-stat-card">
                    <div class="ceet-admin-stat-head">
                        <span>Incidents ouverts</span>
                        <span class="material-symbols-outlined" aria-hidden="true">pending_actions</span>
                    </div>
                    <div class="ceet-admin-stat-body">
                        <strong>{{ number_format($openIncidents, 0, ',', ' ') }}</strong>
                        <small>Moyenne {{ $avgDurationLabel }}</small>
                    </div>
                </article>

                <article class="ceet-admin-stat-card">
                    <div class="ceet-admin-stat-head">
                        <span>Incidents clôturés</span>
                        <span class="material-symbols-outlined" aria-hidden="true">task_alt</span>
                    </div>
                    <div class="ceet-admin-stat-body">
                        <strong>{{ number_format($closedIncidents, 0, ',', ' ') }}</strong>
                        <small>{{ number_format($availabilityRate, 1, ',', ' ') }}% résolution</small>
                    </div>
                </article>

                <article class="ceet-admin-stat-card">
                    <div class="ceet-admin-stat-head">
                        <span>Utilisateurs</span>
                        <span class="material-symbols-outlined" aria-hidden="true">group</span>
                    </div>
                    <div class="ceet-admin-stat-body">
                        <strong>{{ number_format($totalUsers, 0, ',', ' ') }}</strong>
                        <small>Comptes actifs</small>
                    </div>
                </article>
            </section>

            <section class="ceet-admin-content-grid">
                <article class="ceet-admin-panel ceet-admin-incidents-panel">
                    <header class="ceet-admin-panel-header">
                        <h3>Incidents récents</h3>
                        @if($canViewIncidents)
                            <a href="{{ $safeRoute('incidents.index', [], '/incidents') }}">Voir tout</a>
                        @endif
                    </header>

                    <div class="ceet-admin-table-wrap">
                        <table class="ceet-admin-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Type / Cause</th>
                                    <th>Localisation</th>
                                    <th>Statut</th>
                                    <th class="is-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIncidents as $incident)
                                    @php
                                        $incidentCode = $incident->code_incident ?: 'INC-' . $incident->id;
                                        $incidentTitle = $incident->titre ?: optional($incident->typeIncident)->libelle ?: 'Incident sans titre';
                                        $incidentCause = optional($incident->cause)->libelle ?? 'Cause non renseignée';
                                        $incidentStatus = optional($incident->status)->libelle ?? 'N/A';
                                        $incidentUrl = $safeRoute('incidents.show', $incident, '#');
                                    @endphp
                                    <tr>
                                        <td><strong>{{ str_starts_with($incidentCode, '#') ? $incidentCode : '#' . $incidentCode }}</strong></td>
                                        <td><strong>{{ $incidentTitle }}</strong><br><span>{{ $incidentCause }}</span></td>
                                        <td>{{ $incident->localisation ?: optional($incident->departement)->nom ?: 'N/A' }}</td>
                                        <td><span class="ceet-admin-chip">{{ $incidentStatus }}</span></td>
                                        <td class="is-right">
                                            <a href="{{ $incidentUrl }}" class="ceet-admin-row-action" aria-label="Voir l’incident {{ $incidentCode }}">
                                                <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="ceet-admin-empty-row">Aucun incident récent.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <aside class="ceet-admin-side-stack">
                    <article class="ceet-admin-panel">
                        <header class="ceet-admin-panel-header">
                            <h3>Actions rapides</h3>
                        </header>

                        <div class="ceet-admin-quick-grid">

                            @if($canViewUsers)
                                <a href="{{ $safeRoute('users.index', [], '/users') }}" class="ceet-admin-quick-action">
                                    <span class="material-symbols-outlined" aria-hidden="true">manage_accounts</span>
                                    Gérer users
                                </a>
                            @endif

                            @if($canViewCatalogues)
                                @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                                <a href="{{ $safeRoute('catalogues.index', [], '#') }}" class="ceet-admin-quick-action">
                                    <span class="material-symbols-outlined" aria-hidden="true">category</span>
                                    Catalogues
                                </a>
                                @endunless
                            @endif

                            @if($canViewReports)
                                <a href="{{ $safeRoute('reports.index', [], '/reports') }}" class="ceet-admin-quick-action">
                                    <span class="material-symbols-outlined" aria-hidden="true">bar_chart</span>
                                    Rapports
                                </a>
                            @endif
                        </div>
                    </article>

                    <article class="ceet-admin-panel">
                        <header class="ceet-admin-panel-header">
                            <h3>Répartition rôles</h3>
                        </header>

                        <div class="ceet-admin-log-list">
                            @forelse($roleCounts as $role)
                                <div class="ceet-admin-log-item">
                                    <span class="ceet-admin-log-dot {{ $loop->first ? '' : 'is-muted' }}"></span>
                                    <div>
                                        <p>{{ data_get($role, 'label', 'Rôle') }} : <strong>{{ number_format((int) data_get($role, 'count', 0), 0, ',', ' ') }}</strong></p>
                                        <time>Synchronisé {{ $lastCheckAt }}</time>
                                    </div>
                                </div>
                            @empty
                                <div class="ceet-admin-empty-row">Aucun rôle trouvé.</div>
                            @endforelse
                        </div>
                    </article>
                </aside>
            </section>

            <section class="ceet-admin-bottom-grid">
                <article class="ceet-admin-map-card">
                    <div class="ceet-admin-map-pattern"></div>
                    <div class="ceet-admin-map-content">
                        <span class="material-symbols-outlined" aria-hidden="true">map</span>
                        <p>Départs surveillés</p>
                        @forelse($topDepartements as $departement)
                            <strong>{{ data_get($departement, 'label', 'N/A') }} · {{ data_get($departement, 'total', 0) }}</strong>
                        @empty
                            <strong>Aucune donnée récente</strong>
                        @endforelse
                    </div>
                    @if($canViewReports)
                        <a href="{{ $safeRoute('reports.index', [], '/reports') }}" class="ceet-admin-map-action">Analyser</a>
                    @endif
                </article>

                <article class="ceet-admin-alert-card">
                    <div>
                        <h3>Contrôle système</h3>
                        <p>Dernière synchronisation : {{ $lastCheckAt }}. Vérifiez les statuts, logs et permissions après chaque déploiement.</p>
                    </div>

                    @if($canViewSystem)
                        @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                        <a href="{{ $safeRoute('system.status', [], '#') }}">Ouvrir status</a>
                        @endunless
                    @elseif($canViewLogs)
                        <a href="{{ $safeRoute('historique.index', [], '#') }}">Voir logs</a>
                    @endif
                </article>
            </section>
        </main>
    </div>
</body>
</html>
