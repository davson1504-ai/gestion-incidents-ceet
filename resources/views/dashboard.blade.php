@php
    use Illuminate\Support\Carbon;

    $avgMinutes = (int) round($kpis['avgDuration'] ?? 0);
    $avgHours = intdiv($avgMinutes, 60);
    $avgRemainingMinutes = $avgMinutes % 60;
    $mttrLabel = $avgHours > 0 ? sprintf('%dh %02dmin', $avgHours, $avgRemainingMinutes) : sprintf('%dmin', $avgRemainingMinutes);
    $weekDeltaLabel = $weekDelta !== null ? number_format($weekDelta, 1, ',', ' ') . '%' : 'n/d';
    $dashboardChartPayload = [
        'timeseries' => [
            'labels' => collect($timeseries)->map(fn ($item) => Carbon::parse($item->d)->format('d/m'))->values(),
            'data' => collect($timeseries)->pluck('total')->map(fn ($total) => (int) $total)->values(),
        ],
        'topDepart' => collect($topDepart)->take(7)->values(),
        'byStatus' => collect($byStatus)->values(),
        'byPriorite' => collect($byPriorite)->values(),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Tableau de bord</h1>
            <p class="text-muted mb-0">Vue synthétique des incidents CEET, des priorités opérationnelles et des tendances des 30 derniers jours.</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents en cours</span>
                    <div class="metric-value mt-2">{{ number_format($kpis['openCount']) }}</div>
                    <div class="small text-muted mt-2">Dossiers non clôturés à cet instant.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Résolus aujourd'hui</span>
                    <div class="metric-value mt-2">{{ number_format($todayResolved) }}</div>
                    <div class="small text-muted mt-2">Incidents clôturés sur la journée en cours.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Temps moyen MTTR</span>
                    <div class="metric-value mt-2">{{ $mttrLabel }}</div>
                    <div class="small text-muted mt-2">Variation hebdomadaire : {{ $weekDeltaLabel }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Disponibilité réseau</span>
                    <div class="metric-value mt-2">{{ number_format($availabilityRate, 1, ',', ' ') }}%</div>
                    <div class="small text-muted mt-2">Dernière vérification à {{ $lastCheckAt }}.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="date_from" class="form-label fw-semibold">Date début</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_to" class="form-label fw-semibold">Date fin</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Appliquer</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="h4 mb-1">Évolution des incidents (30 jours)</h2>
                            <p class="text-muted mb-0">Nombre d’incidents déclarés sur la période glissante.</p>
                        </div>
                    </div>
                    <div class="dashboard-chart-box">
                        <canvas id="dashboard-timeseries-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Répartition par statut</h2>
                    <p class="text-muted mb-3">Utilise la palette fonctionnelle de chaque statut.</p>
                    <div class="dashboard-chart-box">
                        <canvas id="dashboard-status-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Top départs</h2>
                    <p class="text-muted mb-3">Les 7 départs les plus exposés aux incidents.</p>
                    <div class="dashboard-chart-box">
                        <canvas id="dashboard-top-depart-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Répartition par priorité</h2>
                    <p class="text-muted mb-3">Vision immédiate des niveaux d’urgence.</p>
                    <div class="dashboard-chart-box">
                        <canvas id="dashboard-priority-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Causes fréquentes</h2>
                    <div class="list-group list-group-flush">
                        @forelse($byCause as $cause)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>{{ $cause['label'] }}</span>
                                <span class="badge text-bg-light">{{ $cause['total'] }}</span>
                            </div>
                        @empty
                            <div class="text-muted">Aucune cause disponible sur la période sélectionnée.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Répartition par type</h2>
                    <div class="list-group list-group-flush">
                        @forelse($byType as $type)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>{{ $type['label'] }}</span>
                                <span class="badge text-bg-light">{{ $type['total'] }}</span>
                            </div>
                        @empty
                            <div class="text-muted">Aucun type d’incident trouvé.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Incidents récents</h2>
                    <div class="list-group list-group-flush">
                        @forelse($recentIncidents as $incident)
                            <a href="{{ route('incidents.show', $incident) }}" class="list-group-item list-group-item-action px-0 border-0 border-bottom">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $incident->code_incident }}</div>
                                        <div class="small text-muted">{{ $incident->departement?->nom ?? 'Départ non renseigné' }}</div>
                                        <div class="small text-muted">{{ $incident->cause?->libelle ?? 'Cause non renseignée' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $incident->status?->is_final ? 'text-bg-success' : 'text-bg-danger' }}">
                                            {{ $incident->status?->libelle ?? 'N/A' }}
                                        </span>
                                        <div class="small text-muted mt-2">
                                            {{ $incident->date_debut ? Carbon::parse($incident->date_debut)->diffForHumans() : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-muted">Aucun incident récent à afficher.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-danger-subtle">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                    <div>
                        <h2 class="h4 mb-1">Lecture opérationnelle</h2>
                        <p class="text-muted mb-0">Le temps moyen de résolution évolue de {{ $weekDeltaLabel }} sur la semaine. Les zones à surveiller en priorité restent {{ $focusText }}.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('reports.monthly', ['month' => now()->format('Y-m'), 'format' => 'pdf']) }}" class="btn btn-outline-secondary">Rapport PDF</a>
                        <a href="{{ route('incidents.en-cours') }}" class="btn btn-danger">Voir les incidents en cours</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.dashboardChartData = @json($dashboardChartPayload);
    </script>
    @vite('resources/js/charts/dashboard.js')
</x-app-layout>
