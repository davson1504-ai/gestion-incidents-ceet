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
    <style>
        /* ========================================
           VARIABLES & BASE
           ======================================== */
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
            --ceet-gold: #f59e0b;
            --ceet-blue-night: #0f172a;
            --ceet-blue-deep: #1e293b;
            --ceet-gray-light: #f8fafc;
            --ceet-border-light: #e2e8f0;
            --ceet-text-muted: #64748b;
            --ceet-success: #22c55e;
        }

        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-light {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.85;
            }
        }

        /* ========================================
           CARDS KPI - Modern Design
           ======================================== */
        .row.g-3.mb-4:first-of-type .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.88));
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.07);
            animation: fadeInUp 0.6s ease both;
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(1) .card {
            animation-delay: 0.1s;
            border-left: 4px solid var(--ceet-red);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(2) .card {
            animation-delay: 0.2s;
            border-left: 4px solid var(--ceet-gold);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(3) .card {
            animation-delay: 0.3s;
            border-left: 4px solid var(--ceet-success);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(4) .card {
            animation-delay: 0.4s;
            border-left: 4px solid var(--ceet-blue-deep);
        }

        .row.g-3.mb-4:first-of-type .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.15);
            border-color: rgba(226, 232, 240, 0.8);
        }

        /* ========================================
           GENERAL CARDS
           ======================================== */
        .card {
            border-radius: 16px;
            border: 1px solid var(--ceet-border-light);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: fadeInUp 0.6s ease both;
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }

        /* ========================================
           FORMS & INPUTS
           ======================================== */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--ceet-border-light);
            transition: all 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--ceet-red);
            box-shadow: 0 0 0 3px rgba(239, 36, 51, 0.1);
        }

        /* ========================================
           BUTTONS
           ======================================== */
        .btn-danger {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 36, 51, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 36, 51, 0.4);
        }

        .btn-outline-secondary {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }

        .btn-outline-success, .btn-outline-danger {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-success:hover, .btn-outline-danger:hover {
            transform: translateY(-2px);
        }

        /* ========================================
           TABLE ANIMATION
           ======================================== */
        table tbody tr {
            animation: fadeInUp 0.6s ease backwards;
        }

        table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        table tbody tr:nth-child(2) { animation-delay: 0.15s; }
        table tbody tr:nth-child(3) { animation-delay: 0.2s; }
        table tbody tr:nth-child(4) { animation-delay: 0.25s; }
        table tbody tr:nth-child(5) { animation-delay: 0.3s; }
        table tbody tr:nth-child(n+6) { animation-delay: 0.35s; }

        table tbody tr:hover {
            background-color: rgba(239, 36, 51, 0.03);
            transition: all 0.2s ease;
        }

        /* ========================================
           PROGRESS BAR - Modern Style
           ======================================== */
        .progress {
            border-radius: 10px;
            background-color: rgba(239, 36, 51, 0.1);
            height: 8px;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--ceet-gold), var(--ceet-red));
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        /* ========================================
           METRIC VALUES
           ======================================== */
        .metric-value {
            animation: pulse-light 3s ease-in-out infinite;
        }
    </style>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Rapports et analyse</h1>
            <p class="text-muted mb-0">Analyse mensuelle des incidents, de leur durée et des départs critiques.</p>
        </div>
    </x-slot>

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

    @php
        $reportsChartData = [
            'evolutionLabels' => $evolutionLabels,
            'evolutionIncidentData' => $evolutionIncidentData,
            'evolutionDurationData' => $evolutionDurationData,
            'byType' => collect($byType)->values()->all(),
        ];
    @endphp
    <script>
        window.reportsChartData = {{ \Illuminate\Support\Js::from($reportsChartData) }};
    </script>
    @vite('resources/js/charts/reports.js')
</x-app-layout>
