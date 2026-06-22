@extends('layouts.app')

@section('title', 'Dashboard Administrateur')

@section('page_css')
    @vite('resources/css/pages/dashboard-admin.css')
@endsection

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $currentUser = auth()->user();
    $userName = $currentUser?->name ?? 'Administrateur CEET';

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

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
    $weekDelta = $weekDelta ?? null;
    $lastCheckAt = $lastCheckAt ?? now()->format('H:i:s');

    $avgDurationLabel = $avgDuration
        ? floor($avgDuration / 60) . 'h ' . str_pad((string) ((int) $avgDuration % 60), 2, '0', STR_PAD_LEFT) . 'm'
        : 'N/A';

    $recentIncidents = collect($recentIncidents ?? []);
    $roleCounts = collect($roleCounts ?? []);
    $topDepartements = collect($topDepart ?? [])->take(4)->values();
    $priorities = collect($byPriorite ?? []);
    $timeseriesCollection = collect($timeseries ?? []);

    $criticalCount = (int) $priorities
        ->filter(fn ($row) => Str::contains(Str::lower((string) data_get($row, 'label', '')), ['critique', 'urgent', 'haute']))
        ->sum('total');

    $resolutionRate = min(100, max(0, $availabilityRate));

    $formatDuration = function ($minutes, $startDate = null): string {
        if (($minutes === null || (int) $minutes <= 0) && $startDate) {
            try {
                $minutes = Carbon::parse($startDate)->diffInMinutes(now());
            } catch (Throwable $e) {
                $minutes = null;
            }
        }

        if ($minutes === null || (int) $minutes <= 0) {
            return '--';
        }

        $minutes = (int) $minutes;
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0
            ? $hours . 'h ' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT) . 'm'
            : $remaining . 'm';
    };

    $statusClass = function (?string $label): string {
        $value = Str::lower(Str::ascii((string) $label));

        return match (true) {
            Str::contains($value, ['critique', 'urgence', 'urgent']) => 'is-critical',
            Str::contains($value, ['attente', 'ouvert', 'rapporte']) => 'is-waiting',
            Str::contains($value, ['planifie', 'affecte', 'cours']) => 'is-planned',
            Str::contains($value, ['resolu', 'valide', 'cloture', 'ferme']) => 'is-resolved',
            default => 'is-neutral',
        };
    };

    $formatDay = function ($date): string {
        try {
            return Carbon::parse($date)->locale('fr')->isoFormat('DD MMM');
        } catch (Throwable $e) {
            return 'N/A';
        }
    };

    $rawChartSeries = $timeseriesCollection->take(-8)->values();

    if ($rawChartSeries->isEmpty()) {
        $rawChartSeries = collect(range(7, 0))->map(fn ($day) => [
            'd' => now()->subDays($day)->toDateString(),
            'total' => 0,
        ]);
    }

    $maxVolume = max(1, (int) $rawChartSeries->max(fn ($point) => (int) data_get($point, 'total', 0)));

    $chartSeries = $rawChartSeries->map(function ($point) use ($formatDay, $maxVolume) {
        $total = (int) data_get($point, 'total', 0);

        return [
            'label' => $formatDay(data_get($point, 'd')),
            'total' => $total,
            'height' => $total > 0 ? max(8, round(($total / $maxVolume) * 100)) : 0,
        ];
    })->values();

    $hasFilters = filled(data_get($filters ?? [], 'date_from')) || filled(data_get($filters ?? [], 'date_to'));
@endphp

@section('content')
<div class="ceet-admin-dashboard-page" data-admin-dashboard>
    <script id="ceet-admin-chart-data" type="application/json">@json($chartSeries)</script>

    <main class="ceet-admin-main" aria-label="Tableau de bord administrateur">
        <section class="ceet-admin-hero" aria-labelledby="ceet-admin-page-title">
            <div>
                <p class="ceet-admin-eyebrow">Administrateur</p>
                <h1 id="ceet-admin-page-title">Vue Réseau Global</h1>
                <p>Statistiques consolidées sur les 30 derniers jours.</p>
            </div>

            <div class="ceet-admin-hero-meta" aria-label="État de synchronisation">
                <span class="material-symbols-outlined" aria-hidden="true">sync</span>
                <div>
                    <strong>Synchronisé</strong>
                    <small>{{ $lastCheckAt }}</small>
                </div>
            </div>
        </section>

        <section class="ceet-admin-kpi-grid" aria-label="Indicateurs clés">
            <article class="ceet-admin-kpi-card">
                <span>Total incidents</span>
                <div class="ceet-admin-kpi-value-row">
                    <strong>{{ number_format($totalIncidents, 0, ',', ' ') }}</strong>
                    @if(! is_null($weekDelta))
                        <em class="{{ $weekDelta >= 0 ? 'is-up' : 'is-down' }}">
                            {{ $weekDelta >= 0 ? '+' : '' }}{{ number_format($weekDelta, 1, ',', ' ') }}%
                        </em>
                    @endif
                </div>
                <small>{{ $todayResolved }} résolu(s) aujourd’hui</small>
            </article>

            <article class="ceet-admin-kpi-card">
                <span>En cours</span>
                <div class="ceet-admin-kpi-value-row">
                    <strong>{{ number_format($openIncidents, 0, ',', ' ') }}</strong>
                    <small>Temps moy: {{ $avgDurationLabel }}</small>
                </div>
                <small>Incidents ouverts ou en traitement</small>
            </article>

            <article class="ceet-admin-kpi-card is-critical">
                <span>Critiques</span>
                <div class="ceet-admin-kpi-value-row">
                    <strong>{{ str_pad((string) $criticalCount, 2, '0', STR_PAD_LEFT) }}</strong>
                    <em>Priorité haute</em>
                </div>
                <small>Priorités critiques / urgentes</small>
            </article>

            <article class="ceet-admin-kpi-card">
                <span>Taux résolution</span>
                <div class="ceet-admin-kpi-resolution" style="--rate: {{ $resolutionRate }}%;">
                    <strong>{{ number_format($availabilityRate, 1, ',', ' ') }}%</strong>
                    <i aria-hidden="true"><b></b></i>
                </div>
                <small>{{ number_format($closedIncidents, 0, ',', ' ') }} incident(s) clôturé(s)</small>
            </article>
        </section>

        <section class="ceet-admin-network-grid">
            <article class="ceet-admin-panel ceet-admin-chart-panel">
                <header class="ceet-admin-panel-header">
                    <div>
                        <h2>Volume d'Incidents Quotidiens</h2>
                        <p>Vue synthétique des déclarations récentes</p>
                    </div>
                    <div class="ceet-admin-chart-legend" aria-hidden="true">
                        <span><i class="is-black"></i> Résolus</span>
                        <span><i></i> Nouveaux</span>
                    </div>
                </header>

                <div class="ceet-admin-chart" data-ceet-chart aria-label="Histogramme des incidents récents">
                    <div class="ceet-admin-chart-bars" data-chart-bars>
                        @foreach($chartSeries as $point)
                            <div class="ceet-admin-chart-bar-item" title="{{ $point['total'] }} incident(s)">
                                <div class="ceet-admin-chart-track">
                                    <span style="height: {{ $point['height'] }}%"></span>
                                </div>
                                <small>{{ $point['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <aside class="ceet-admin-panel ceet-admin-exposed-panel" aria-label="Départs les plus exposés">
                <header class="ceet-admin-panel-header">
                    <div>
                        <h2>Départs les plus exposés</h2>
                        <p>Fréquence d’incidents (30j)</p>
                    </div>
                </header>

                <div class="ceet-admin-exposed-list">
                    @forelse($topDepartements as $departement)
                        <div class="ceet-admin-exposed-item">
                            <div>
                                <strong>{{ data_get($departement, 'label', 'N/A') }}</strong>
                                <span>Zone réseau CEET</span>
                            </div>
                            <em class="{{ $loop->index < 2 ? 'is-hot' : '' }}">
                                {{ str_pad((string) data_get($departement, 'total', 0), 2, '0', STR_PAD_LEFT) }}
                                <small>incidents</small>
                            </em>
                        </div>
                    @empty
                        <div class="ceet-admin-empty-state">Aucune donnée récente.</div>
                    @endforelse
                </div>

                @if($canViewReports)
                    <a class="ceet-admin-outline-btn" href="{{ $safeRoute('reports.index', [], '/reports') }}">Voir le rapport complet</a>
                @endif
            </aside>
        </section>

        <section class="ceet-admin-panel ceet-admin-incidents-panel" aria-labelledby="ceet-admin-recent-title">
            <header class="ceet-admin-table-header">
                <div>
                    <h2 id="ceet-admin-recent-title">Incidents récents</h2>
                    <p>Derniers événements enregistrés sur le réseau</p>
                </div>

                <div class="ceet-admin-table-actions">
                    <button type="button" class="ceet-admin-outline-btn" data-filter-toggle aria-expanded="{{ $hasFilters ? 'true' : 'false' }}">
                        <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                        Filtrer
                    </button>

                    @if($canExportIncidents)
                        <a class="ceet-admin-outline-btn" href="{{ $safeRoute('incidents.export', request()->query(), '#') }}">
                            <span class="material-symbols-outlined" aria-hidden="true">download</span>
                            Exporter
                        </a>
                    @endif
                </div>
            </header>

            <form class="ceet-admin-filter-panel {{ $hasFilters ? 'is-open' : '' }}" data-filter-panel method="GET" action="{{ $safeRoute('dashboard', [], '/dashboard') }}" @unless($hasFilters) hidden @endunless>
                <label>
                    <span>Du</span>
                    <input type="date" name="date_from" value="{{ data_get($filters ?? [], 'date_from') }}">
                </label>
                <label>
                    <span>Au</span>
                    <input type="date" name="date_to" value="{{ data_get($filters ?? [], 'date_to') }}">
                </label>
                <button type="submit">Appliquer</button>
                @if($hasFilters)
                    <a href="{{ $safeRoute('dashboard', [], '/dashboard') }}">Réinitialiser</a>
                @endif
            </form>

            <div class="ceet-admin-table-wrap">
                <table class="ceet-admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type / Cause</th>
                            <th>Localisation</th>
                            <th>Statut</th>
                            <th>Durée</th>
                            <th>Action</th>
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
                                $incidentDuration = $formatDuration($incident->duree_minutes ?? null, $incident->date_debut ?? null);
                            @endphp
                            <tr data-row-url="{{ $incidentUrl }}">
                                <td><strong>{{ Str::startsWith($incidentCode, '#') ? $incidentCode : '#' . $incidentCode }}</strong></td>
                                <td>
                                    <strong>{{ $incidentTitle }}</strong>
                                    <span>{{ $incidentCause }}</span>
                                </td>
                                <td>{{ $incident->localisation ?: optional($incident->departement)->nom ?: 'N/A' }}</td>
                                <td>
                                    <span class="ceet-admin-status {{ $statusClass($incidentStatus) }}">{{ $incidentStatus }}</span>
                                </td>
                                <td>{{ $incidentDuration }}</td>
                                <td>
                                    <a href="{{ $incidentUrl }}" class="ceet-admin-detail-link" aria-label="Voir l’incident {{ $incidentCode }}">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ceet-admin-empty-state">Aucun incident récent.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="ceet-admin-table-footer">
                <span>Affichage {{ $recentIncidents->count() }} sur {{ number_format($totalIncidents, 0, ',', ' ') }} incidents</span>
                @if($canViewIncidents)
                    <a href="{{ $safeRoute('incidents.index', [], '/incidents') }}">Voir tout</a>
                @endif
            </footer>
        </section>

        <section class="ceet-admin-utility-grid" aria-label="Administration rapide">
            <article class="ceet-admin-panel ceet-admin-quick-panel">
                <header class="ceet-admin-panel-header">
                    <div>
                        <h2>Actions rapides</h2>
                        <p>Accès administrateur</p>
                    </div>
                </header>

                <div class="ceet-admin-quick-grid">
                    @if($canViewUsers)
                        <a href="{{ $safeRoute('users.index', [], '/users') }}">
                            <span class="material-symbols-outlined" aria-hidden="true">manage_accounts</span>
                            Gérer users
                        </a>
                    @endif

                    @if($canViewCatalogues)
                        @unless(($currentUser?->isSuperviseur() ?? false) && ! ($currentUser?->isAdmin() ?? false))
                            <a href="{{ $safeRoute('catalogues.index', [], '#') }}">
                                <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                                Catalogues
                            </a>
                        @endunless
                    @endif

                    @if($canViewReports)
                        <a href="{{ $safeRoute('reports.index', [], '/reports') }}">
                            <span class="material-symbols-outlined" aria-hidden="true">assessment</span>
                            Rapports
                        </a>
                    @endif
                </div>
            </article>

            <article class="ceet-admin-panel ceet-admin-roles-panel">
                <header class="ceet-admin-panel-header">
                    <div>
                        <h2>Répartition rôles</h2>
                        <p>Comptes actifs : {{ number_format($totalUsers, 0, ',', ' ') }}</p>
                    </div>
                </header>

                <div class="ceet-admin-roles-list">
                    @forelse($roleCounts as $role)
                        <div>
                            <span>{{ data_get($role, 'label', 'Rôle') }}</span>
                            <strong>{{ number_format((int) data_get($role, 'count', 0), 0, ',', ' ') }}</strong>
                        </div>
                    @empty
                        <div class="ceet-admin-empty-state">Aucun rôle trouvé.</div>
                    @endforelse
                </div>
            </article>

            <article class="ceet-admin-system-card">
                <div>
                    <span class="material-symbols-outlined" aria-hidden="true">settings_input_component</span>
                    <h2>Contrôle système</h2>
                    <p>Dernière synchronisation : {{ $lastCheckAt }}. Vérifiez les statuts, logs et permissions après chaque déploiement.</p>
                </div>

                @if($canViewSystem)
                    @unless(($currentUser?->isSuperviseur() ?? false) && ! ($currentUser?->isAdmin() ?? false))
                        <a href="{{ $safeRoute('system.status', [], '#') }}">Ouvrir status</a>
                    @endunless
                @elseif($canViewLogs)
                    <a href="{{ $safeRoute('historique.index', [], '#') }}">Voir logs</a>
                @endif
            </article>
        </section>
    </main>

    @if($canCreateIncident)
        <a href="{{ $safeRoute('incidents.create', [], '#') }}" class="ceet-admin-fab" aria-label="Déclarer un incident">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
        </a>
    @endif
</div>
@endsection

@section('page_js')
    @vite('resources/js/pages/admin-dashboard.js')
@endsection
