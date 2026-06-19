@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();

    

    $isAdmin = $isAdmin ?? ($currentUser?->isAdmin() ?? false);
    $isSupervisor = $isSupervisor ?? ($currentUser?->isSuperviseur() ?? false);
$userName = $currentUser?->name ?? 'Superviseur Réseau';
    $userEmail = $currentUser?->email ?? 'superviseur@ceet.com';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = strtoupper($initials ?: 'SR');

    $safeRoute = function (string $name, $params = []) {
        return Route::has($name) ? route($name, $params) : '#';
    };

    $totalIncidents = (int) data_get($kpis ?? [], 'total', 0);
    $openIncidents = (int) ($teamOpenCount ?? data_get($kpis ?? [], 'openCount', 0));
    $closedIncidents = (int) data_get($kpis ?? [], 'closedCount', 0);
    $avgDuration = data_get($kpis ?? [], 'avgDuration');

    $resolutionRate = (float) ($teamResolutionRate ?? $availabilityRate ?? 0);
    $criticalIncidents = collect($pendingValidation ?? [])->count();

    $recentIncidents = collect($teamOpenIncidents ?? []);

    if ($recentIncidents->isEmpty()) {
        $recentIncidents = collect($recentIncidents ?? []);
    }

    $topDepartements = collect($topDepart ?? [])->take(5);
    $chartRows = collect($timeseries ?? [])->take(-8)->values();
    $maxChartValue = max(1, (int) $chartRows->max('total'));

    $avgDurationLabel = $avgDuration
        ? floor($avgDuration / 60) . 'h ' . ((int) $avgDuration % 60) . 'm'
        : 'N/A';

    $roleName = 'SUPERVISEUR RÉSEAU';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = strtoupper($currentUser->getRoleNames()->first() ?? 'SUPERVISEUR RÉSEAU');
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Superviseur - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/supervisor-dashboard.css',
        'resources/js/app.js',
        'resources/js/pages/supervisor-dashboard.js'
    ])
</head>

<body class="ceet-supervisor-dashboard-page">
    <div class="ceet-supervisor-shell" data-supervisor-dashboard>
        <div class="ceet-supervisor-overlay" data-supervisor-overlay></div>

        <aside class="ceet-supervisor-sidebar" data-supervisor-sidebar>
            <div class="ceet-supervisor-brand">
                <div class="ceet-supervisor-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-supervisor-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-supervisor-nav-link'])
            </nav>

            <div class="ceet-supervisor-sidebar-user">
                <div class="ceet-supervisor-sidebar-user-main">
                    <div class="ceet-supervisor-avatar">{{ $initials }}</div>

                    <div>
                        <strong>{{ $userName }}</strong>
                        <span>{{ $roleName }}</span>
                    </div>
                </div>

                <form action="{{ $safeRoute('logout') }}" method="POST" class="ceet-supervisor-logout-form">
                    @csrf

                    <button type="submit" class="ceet-supervisor-logout-button">
                        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <header class="ceet-supervisor-topbar">
            <button type="button" class="ceet-supervisor-menu-btn" data-supervisor-sidebar-toggle aria-label="Ouvrir le menu">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>

            <form action="{{ $safeRoute('incidents.index') }}" method="GET" class="ceet-supervisor-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input
                    type="search"
                    name="q"
                    placeholder="Rechercher un incident, un départ..."
                    autocomplete="off"
                >
            </form>

            <div class="ceet-supervisor-top-actions">
                <a href="{{ $safeRoute('notifications.index') }}" class="ceet-supervisor-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                    <span class="ceet-supervisor-notification-dot"></span>
                </a>

                <a href="{{ $safeRoute('profile.edit') }}" class="ceet-supervisor-icon-btn" aria-label="Profil">
                    <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
                </a>

                <div class="ceet-supervisor-top-divider"></div>

                <div class="ceet-supervisor-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-supervisor-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

        <main class="ceet-supervisor-main">
            <section class="ceet-supervisor-page-header">
                <div>
                    <h2>Vue Réseau Global</h2>
                    <p>Statistiques consolidées sur les 30 derniers jours.</p>
                </div>

                <div class="ceet-supervisor-header-actions">
                    <a href="{{ $safeRoute('incidents.create', [], '/incidents/create') }}" class="ceet-supervisor-create-header-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        Créer un incident
                    </a>
                </div>
            </section>

            <section class="ceet-supervisor-stats-grid" aria-label="Indicateurs principaux">
                <article class="ceet-supervisor-stat-card">
                    <p>Total incidents</p>

                    <div>
                        <strong>{{ number_format($totalIncidents, 0, ',', ' ') }}</strong>

                        @if (! is_null($weekDelta ?? null))
                            <span class="{{ $weekDelta >= 0 ? 'is-danger' : 'is-positive' }}">
                                <span class="material-symbols-outlined" aria-hidden="true">
                                    {{ $weekDelta >= 0 ? 'trending_up' : 'trending_down' }}
                                </span>
                                {{ $weekDelta >= 0 ? '+' : '' }}{{ $weekDelta }}%
                            </span>
                        @else
                            <span>Stable</span>
                        @endif
                    </div>
                </article>

                <article class="ceet-supervisor-stat-card">
                    <p>En cours</p>

                    <div>
                        <strong>{{ number_format($openIncidents, 0, ',', ' ') }}</strong>
                        <span>Temps moy: {{ $avgDurationLabel }}</span>
                    </div>
                </article>

                <article class="ceet-supervisor-stat-card">
                    <p>Critiques</p>

                    <div>
                        <strong class="is-danger-text">{{ str_pad((string) $criticalIncidents, 2, '0', STR_PAD_LEFT) }}</strong>
                        <span class="ceet-supervisor-danger-badge">Priorité haute</span>
                    </div>
                </article>

                <article class="ceet-supervisor-stat-card">
                    <p>Taux résolution</p>

                    <div>
                        <strong>{{ number_format($resolutionRate, 1, ',', ' ') }}%</strong>

                        <div class="ceet-supervisor-progress" aria-label="Taux de résolution {{ $resolutionRate }}%">
                            <span style="width: {{ min(100, max(0, $resolutionRate)) }}%"></span>
                        </div>
                    </div>
                </article>
            </section>

            <section class="ceet-supervisor-grid">
                <article class="ceet-supervisor-panel ceet-supervisor-chart-panel">
                    <header class="ceet-supervisor-panel-header">
                        <h3>Volume d'Incidents Quotidiens</h3>

                        <div class="ceet-supervisor-legend">
                            <span><i></i> Résolus</span>
                            <span><i class="is-muted"></i> Nouveaux</span>
                        </div>
                    </header>

                    <div class="ceet-supervisor-chart">
                        @forelse ($chartRows as $row)
                            @php
                                $value = (int) data_get($row, 'total', 0);
                                $height = max(8, round(($value / $maxChartValue) * 78));
                                $mutedHeight = max(8, min(70, $height - 12));
                                $dateLabel = data_get($row, 'd')
                                    ? \Carbon\Carbon::parse(data_get($row, 'd'))->format('d M')
                                    : 'N/A';
                            @endphp

                            <div class="ceet-supervisor-chart-bar">
                                <div class="ceet-supervisor-chart-bars">
                                    <span class="is-muted" style="height: {{ $mutedHeight }}%"></span>
                                    <span style="height: {{ $height }}%"></span>
                                </div>

                                <small>{{ $dateLabel }}</small>
                            </div>
                        @empty
                            @foreach ([38, 52, 44, 70, 76, 60, 66, 34] as $index => $height)
                                <div class="ceet-supervisor-chart-bar">
                                    <div class="ceet-supervisor-chart-bars">
                                        <span class="is-muted" style="height: {{ max(8, $height - 18) }}%"></span>
                                        <span style="height: {{ $height }}%"></span>
                                    </div>

                                    <small>{{ str_pad((string) (($index + 1) * 2 - 1), 2, '0', STR_PAD_LEFT) }} Mai</small>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </article>

                <aside class="ceet-supervisor-panel ceet-supervisor-departs-panel">
                    <header class="ceet-supervisor-panel-header is-stacked">
                        <h3>Départs les plus exposés</h3>
                        <p>Fréquence d'incidents sur 30 jours</p>
                    </header>

                    <div class="ceet-supervisor-depart-list">
                        @forelse ($topDepartements as $departement)
                            <div class="ceet-supervisor-depart-item">
                                <div>
                                    <strong>{{ data_get($departement, 'label', 'Départ N/A') }}</strong>
                                    <span>Réseau CEET</span>
                                </div>

                                <div>
                                    <strong>{{ number_format((int) data_get($departement, 'total', 0), 0, ',', ' ') }}</strong>
                                    <span>Incidents</span>
                                </div>
                            </div>
                        @empty
                            <div class="ceet-supervisor-empty-small">
                                Aucun départ exposé pour la période.
                            </div>
                        @endforelse
                    </div>

                    <div class="ceet-supervisor-panel-footer">
                        <a href="{{ $safeRoute('reports.index') }}">Voir le rapport complet</a>
                    </div>
                </aside>
            </section>

            <section class="ceet-supervisor-panel ceet-supervisor-table-panel">
                <header class="ceet-supervisor-panel-header">
                    <h3>Incidents récents</h3>

                    <div class="ceet-supervisor-table-actions">
                        <button type="button" data-supervisor-filter>
                            <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                            Filtrer
                        </button>

                        <a href="{{ $safeRoute('incidents.export') }}">
                            <span class="material-symbols-outlined" aria-hidden="true">download</span>
                            Exporter
                        </a>
                    </div>
                </header>

                <div class="ceet-supervisor-table-wrap">
                    <table class="ceet-supervisor-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type / Cause</th>
                                <th>Localisation</th>
                                <th>Statut</th>
                                <th>Durée</th>
                                <th class="is-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($recentIncidents as $incident)
                                @php
                                    $incidentCode = $incident->code_incident ?: 'INC-' . $incident->id;
                                    $incidentTitle = $incident->titre
                                        ?: optional($incident->typeIncident)->libelle
                                        ?: 'Incident sans titre';

                                    $incidentCause = optional($incident->cause)->libelle ?? 'Cause non renseignée';
                                    $incidentStatus = optional($incident->status)->libelle ?? 'N/A';
                                    $duration = $incident->duree_minutes ?? $incident->duree_en_attente ?? null;

                                    $durationLabel = $duration
                                        ? floor($duration / 60) . 'h ' . ((int) $duration % 60) . 'm'
                                        : '--';

                                    $incidentUrl = Route::has('incidents.show')
                                        ? route('incidents.show', $incident)
                                        : '#';
                                @endphp

                                <tr>
                                    <td>{{ str_starts_with($incidentCode, '#') ? $incidentCode : '#' . $incidentCode }}</td>

                                    <td>
                                        <strong>{{ $incidentTitle }}</strong>
                                        <span>{{ $incidentCause }}</span>
                                    </td>

                                    <td>{{ $incident->localisation ?: optional($incident->departement)->nom ?: 'N/A' }}</td>

                                    <td>
                                        <span class="ceet-supervisor-status">
                                            {{ $incidentStatus }}
                                        </span>
                                    </td>

                                    <td>{{ $durationLabel }}</td>

                                    <td class="is-right">
                                        <a href="{{ $incidentUrl }}">Détails</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="ceet-supervisor-empty-row">
                                        Aucun incident récent.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="ceet-supervisor-table-footer">
                    <span>
                        Affichage {{ $recentIncidents->count() }} incident(s)
                    </span>

                    <div>
                        <button type="button" disabled>
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        </button>

                        <button type="button">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                        </button>
                    </div>
                </footer>
            </section>
        </main>

        <a href="{{ $safeRoute('incidents.create') }}" class="ceet-supervisor-fab">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            <small>Déclarer un incident</small>
        </a>
    </div>
</body>
</html>