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

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
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
            border-left: 4px solid var(--ceet-blue-deep);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(4) .card {
            animation-delay: 0.4s;
            border-left: 4px solid var(--ceet-success);
        }

        .row.g-3.mb-4:first-of-type .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.15);
            border-color: rgba(226, 232, 240, 0.8);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.1), rgba(245, 158, 11, 0.1));
            flex-shrink: 0;
            animation: pulse-light 3s ease-in-out infinite;
        }

        .kpi-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--ceet-red);
            stroke-width: 2;
            fill: none;
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(2) .kpi-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.15));
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(2) .kpi-icon svg {
            stroke: var(--ceet-gold);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(3) .kpi-icon {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.1), rgba(30, 41, 59, 0.15));
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(3) .kpi-icon svg {
            stroke: var(--ceet-blue-deep);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(4) .kpi-icon {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.15));
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(4) .kpi-icon svg {
            stroke: var(--ceet-success);
        }

        .kpi-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: var(--ceet-text-muted);
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--ceet-blue-night);
            line-height: 1;
            margin-bottom: 12px;
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(1) .metric-value {
            color: var(--ceet-red);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(2) .metric-value {
            color: var(--ceet-gold);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(3) .metric-value {
            color: var(--ceet-blue-deep);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(4) .metric-value {
            color: var(--ceet-success);
        }

        .row.g-3.mb-4:first-of-type .card-body > .small {
            font-size: 0.85rem;
            line-height: 1.5;
            color: var(--ceet-text-muted);
        }

        /* ========================================
           FILTER CARD
           ======================================== */
        .card.mb-4 {
            border-radius: 16px;
            border: 1px solid var(--ceet-border-light);
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
            animation: slideInDown 0.5s ease 0.5s both;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid var(--ceet-border-light);
            padding: 10px 14px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--ceet-gold);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--ceet-blue-night);
            margin-bottom: 6px;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(239, 36, 51, 0.25);
            color: white;
        }

        .btn-outline-secondary {
            border-radius: 10px;
            border-color: var(--ceet-border-light);
            color: var(--ceet-text-muted);
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-outline-secondary:hover {
            background-color: var(--ceet-gray-light);
            border-color: var(--ceet-border-light);
            color: var(--ceet-blue-night);
        }

        /* ========================================
           CHART CARDS & SECTIONS
           ======================================== */
        .row.g-3.mb-4 > .col-12 > .card,
        .row.g-3:not(:first-of-type) .card {
            border-radius: 16px;
            border: 1px solid var(--ceet-border-light);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease both;
        }

        .row.g-3.mb-4 > .col-12:nth-child(1) > .card {
            animation-delay: 0.6s;
        }

        .row.g-3.mb-4 > .col-12:nth-child(2) > .card {
            animation-delay: 0.7s;
        }

        .row.g-3.mb-4 > .col-12:nth-child(3) > .card {
            animation-delay: 0.8s;
        }

        .row.g-3.mb-4 > .col-12:nth-child(4) > .card {
            animation-delay: 0.9s;
        }

        .row.g-3:not(:first-of-type) .card {
            animation-delay: 1s;
        }

        .row.g-3:not(:first-of-type) .col-12:nth-child(2) .card {
            animation-delay: 1.1s;
        }

        .row.g-3:not(:first-of-type) .col-12:nth-child(3) .card {
            animation-delay: 1.2s;
        }

        .row.g-3:not(:first-of-type) .col-12:nth-child(4) .card {
            animation-delay: 1.3s;
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            border-color: var(--ceet-border-light);
        }

        .card h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ceet-blue-night);
        }

        .card > .card-body > .text-muted {
            font-size: 0.9rem;
            color: var(--ceet-text-muted);
        }

        /* ========================================
           BADGES MODERN
           ======================================== */
        .badge {
            border-radius: 8px;
            font-weight: 600;
            padding: 6px 12px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .badge.text-bg-light {
            background: rgba(239, 36, 51, 0.08) !important;
            color: var(--ceet-red) !important;
            border: 1px solid rgba(239, 36, 51, 0.15);
            font-weight: 700;
        }

        .badge.text-bg-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.08)) !important;
            color: var(--ceet-success) !important;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .badge.text-bg-danger {
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.15), rgba(239, 36, 51, 0.08)) !important;
            color: var(--ceet-red) !important;
            border: 1px solid rgba(239, 36, 51, 0.2);
        }

        /* ========================================
           LIST GROUPS
           ======================================== */
        .list-group-item {
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s;
            padding: 12px 0;
            margin-bottom: 8px;
        }

        .list-group-item:not(.list-group-item-action) {
            background: rgba(248, 250, 252, 0.5);
            border-color: var(--ceet-border-light);
        }

        .list-group-item:hover {
            background: linear-gradient(90deg, rgba(239, 36, 51, 0.04), transparent);
            border-left: 3px solid var(--ceet-red);
            padding-left: 12px;
            padding-right: -3px;
        }

        .list-group-item-action {
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s;
            padding: 12px;
            margin-bottom: 8px;
        }

        .list-group-item-action:hover {
            background: rgba(239, 36, 51, 0.05);
            border-color: rgba(239, 36, 51, 0.2);
            border-radius: 10px;
            transform: translateX(4px);
        }

        .list-group-item .fw-semibold {
            color: var(--ceet-blue-night);
            font-size: 0.95rem;
        }

        .list-group-item .small {
            color: var(--ceet-text-muted);
            font-size: 0.85rem;
        }

        /* ========================================
           SUMMARY CARD (Lecture opérationnelle)
           ======================================== */
        .card.border-danger-subtle {
            border-radius: 16px;
            border: 2px solid var(--ceet-red) !important;
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.05), rgba(245, 158, 11, 0.03));
            box-shadow: 0 4px 12px rgba(239, 36, 51, 0.1);
            animation: fadeInUp 0.6s ease 1.4s both;
        }

        .card.border-danger-subtle h2 {
            color: var(--ceet-red);
            font-weight: 800;
        }

        .card.border-danger-subtle .text-muted {
            color: var(--ceet-text-muted) !important;
            line-height: 1.6;
        }

        .card.border-danger-subtle .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .card.border-danger-subtle .btn-outline-secondary {
            border-color: var(--ceet-border-light);
            color: var(--ceet-text-muted);
        }

        .card.border-danger-subtle .btn-outline-secondary:hover {
            background: var(--ceet-gray-light);
            border-color: var(--ceet-red);
            color: var(--ceet-red);
        }

        .card.border-danger-subtle .btn-danger:hover {
            box-shadow: 0 12px 24px rgba(239, 36, 51, 0.3);
        }

        /* ========================================
           DASHBOARD CHART BOXES
           ======================================== */
        .dashboard-chart-box {
            position: relative;
            min-height: 300px;
            padding: 12px 0;
        }

        /* ========================================
           HEADER
           ======================================== */
        x-slot\[name="header"\] {
            animation: slideInDown 0.6s ease both;
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 768px) {
            .metric-value {
                font-size: 2rem;
            }

            .kpi-icon {
                width: 40px;
                height: 40px;
            }

            .card h2 {
                font-size: 1.1rem;
            }

            .list-group-item {
                padding: 10px 0;
            }
        }
    </style>

    <x-slot name="header">
        <div style="animation: slideInDown 0.6s ease both;">
            <h1 class="h3 mb-1">Tableau de bord</h1>
            <p class="text-muted mb-0">Vue synthétique des incidents CEET, des priorités opérationnelles et des tendances des 30 derniers jours.</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Incidents en cours</span>
                            <div class="metric-value">{{ number_format($kpis['openCount']) }}</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 6v6m0 0l3-3m-3 3l-3-3" />
                            </svg>
                        </div>
                    </div>
                    <small class="text-muted">Dossiers non clôturés à cet instant.</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Résolus aujourd'hui</span>
                            <div class="metric-value">{{ number_format($todayResolved) }}</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M8 12l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <small class="text-muted">Incidents clôturés sur la journée en cours.</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Temps moyen MTTR</span>
                            <div class="metric-value">{{ $mttrLabel }}</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 6v6m0 0l3-3m-3 3l-3-3" />
                            </svg>
                        </div>
                    </div>
                    <small class="text-muted">Variation hebdomadaire : {{ $weekDeltaLabel }}</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Disponibilité réseau</span>
                            <div class="metric-value">{{ number_format($availabilityRate, 1, ',', ' ') }}%</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M8 12l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <small class="text-muted">Dernière vérification à {{ $lastCheckAt }}.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="date_from" class="form-label">Date début</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_to" class="form-label">Date fin</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-danger flex-grow-1">Appliquer</button>
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
                            <a href="{{ route('incidents.show', $incident) }}" class="list-group-item list-group-item-action px-0">
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
                        <p class="text-muted mb-0">Le temps moyen de résolution évolue de <strong style="color: var(--ceet-red); font-size: 1.1em;">{{ $weekDeltaLabel }}</strong> sur la semaine. Les zones à surveiller en priorité restent <strong style="color: var(--ceet-blue-night);">{{ $focusText }}</strong>.</p>
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
