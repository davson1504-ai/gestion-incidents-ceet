@php
    $metricCards = [
        [
            'label' => 'Total incidents',
            'value' => number_format($totalIncidents),
            'trend' => $incidentDelta,
            'sub' => 'Volume constaté sur la période',
        ],
        [
            'label' => 'Durée moyenne',
            'value' => $avgDuration . ' min',
            'trend' => $avgDurationDelta,
            'sub' => 'Temps moyen de rétablissement',
        ],
        [
            'label' => 'Taux de résolution',
            'value' => number_format($resolutionRate, 1, ',', ' ') . '%',
            'trend' => $resolutionDelta,
            'sub' => 'Part des incidents clôturés',
        ],
        [
            'label' => 'Départ le plus exposé',
            'value' => $topDepartName,
            'trend' => $topDepartDelta,
            'sub' => 'Départ le plus sollicité',
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Rapports et analyse</h1>
            <p class="text-muted mb-0">Analyse mensuelle des incidents, de leur durée et des départs critiques.</p>
        </div>
    </x-slot>

    <style>
        .reports-chart-box {
            height: 320px;
        }
    </style>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Période</label>
                    <select name="period" class="form-select">
                        @foreach($periodOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($filters['period'] === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Départ</label>
                    <select name="departement_id" class="form-select js-tom-select" data-placeholder="Tous les départs">
                        <option value="">Tous les départs</option>
                        @foreach($departements as $departement)
                            <option value="{{ $departement->id }}" @selected((string) $filters['departement_id'] === (string) $departement->id)>{{ $departement->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Cause</label>
                    <select name="cause_id" class="form-select js-tom-select" data-placeholder="Toutes les causes">
                        <option value="">Toutes les causes</option>
                        @foreach($causes as $cause)
                            <option value="{{ $cause->id }}" @selected((string) $filters['cause_id'] === (string) $cause->id)>{{ $cause->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-danger">Actualiser</button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <a href="{{ route('reports.monthly', array_merge($exportQuery, ['format' => 'excel'])) }}" class="btn btn-outline-success">Exporter Excel</a>
                    <a href="{{ route('reports.monthly', array_merge($exportQuery, ['format' => 'pdf'])) }}" class="btn btn-outline-danger">Générer PDF</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($metricCards as $card)
            <div class="col-12 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted fw-semibold">{{ $card['label'] }}</span>
                        <div class="metric-value mt-2">{{ $card['value'] }}</div>
                        <div class="small mt-2 {{ $card['trend'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $card['trend'] >= 0 ? '+' : '' }}{{ number_format($card['trend'], 1, ',', ' ') }}% vs période précédente
                        </div>
                        <div class="small text-muted mt-1">{{ $card['sub'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Évolution mensuelle</h2>
                    <p class="text-muted mb-3">Axe gauche : nombre d’incidents. Axe droit : durée moyenne en minutes.</p>
                    <div class="reports-chart-box">
                        <canvas id="reports-evolution-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Répartition par type</h2>
                    <p class="text-muted mb-3">Palette CEET centrée sur les catégories majeures.</p>
                    <div class="reports-chart-box">
                        <canvas id="reports-type-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Répartition par cause</h2>
                    <div class="list-group list-group-flush">
                        @forelse($causeBars as $causeBar)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $causeBar['label'] }}</span>
                                    <span class="badge text-bg-light">{{ $causeBar['total'] }}</span>
                                </div>
                                <div class="progress" role="progressbar" aria-valuenow="{{ $causeBar['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-warning text-dark" style="width: {{ $causeBar['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Aucune donnée de cause pour cette période.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Top départs critiques</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Départ</th>
                                    <th>Incidents</th>
                                    <th>Statut réseau</th>
                                    <th>Charge actuelle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($criticalDepartRows as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row['label'] }}</td>
                                        <td>{{ $row['total'] }}</td>
                                        <td>
                                            <span class="badge {{ $row['network_status'] === 'Critique' ? 'text-bg-danger' : ($row['network_status'] === 'Stable' ? 'text-bg-success' : 'text-bg-primary') }}">
                                                {{ $row['network_status'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" role="progressbar" aria-valuenow="{{ $row['load'] }}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-danger" style="width: {{ $row['load'] }}%"></div>
                                                </div>
                                                <span class="small text-muted">{{ $row['load'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Aucun départ critique disponible pour cette période.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        window.reportsChartData = @json([
            'evolutionLabels' => $evolutionLabels,
            'evolutionIncidentData' => $evolutionIncidentData,
            'evolutionDurationData' => $evolutionDurationData,
            'byType' => collect($byType)->values(),
        ]);
    </script>
    @vite('resources/js/charts/reports.js')
</x-app-layout>
