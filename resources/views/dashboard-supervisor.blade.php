@php
    use Illuminate\Support\Carbon;

    $user = auth()->user();

    $formatDuration = static function (?int $minutes): string {
        if ($minutes === null) return '-';
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;
        $parts = [];
        if ($days > 0) $parts[] = $days . 'j';
        if ($hours > 0 || $days > 0) $parts[] = $hours . 'h';
        $parts[] = $remainingMinutes . 'min';
        return implode(' ', $parts);
    };
@endphp

<x-app-layout>
    <style>
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse-light {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }

        .card {
            border-radius: 16px;
            border: 1px solid var(--ceet-border-light);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease both;
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            border-color: var(--ceet-border-light);
        }

        .row.g-3.mb-4:first-of-type .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.88));
            backdrop-filter: blur(10px);
            animation-delay: var(--animation-delay);
        }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(1) .card { --animation-delay: 0.1s; border-left: 4px solid var(--ceet-red); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(2) .card { --animation-delay: 0.2s; border-left: 4px solid var(--ceet-gold); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(3) .card { --animation-delay: 0.3s; border-left: 4px solid var(--ceet-success); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(4) .card { --animation-delay: 0.4s; border-left: 4px solid var(--ceet-blue-deep); }

        .row.g-3.mb-4:first-of-type .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.15);
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
            flex-shrink: 0;
            animation: pulse-light 3s ease-in-out infinite;
        }

        .kpi-icon svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            fill: none;
        }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(1) .kpi-icon {
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.1), rgba(239, 36, 51, 0.15));
        }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(1) .kpi-icon svg { stroke: var(--ceet-red); }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(2) .kpi-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.15));
        }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(2) .kpi-icon svg { stroke: var(--ceet-gold); }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(3) .kpi-icon {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.15));
        }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(3) .kpi-icon svg { stroke: var(--ceet-success); }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(4) .kpi-icon {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.1), rgba(30, 41, 59, 0.15));
        }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(4) .kpi-icon svg { stroke: var(--ceet-blue-deep); }

        .metric-value {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 12px;
        }

        .row.g-3.mb-4:first-of-type .col-6:nth-child(1) .metric-value { color: var(--ceet-red); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(2) .metric-value { color: var(--ceet-gold); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(3) .metric-value { color: var(--ceet-success); }
        .row.g-3.mb-4:first-of-type .col-6:nth-child(4) .metric-value { color: var(--ceet-blue-deep); }

        .kpi-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: var(--ceet-text-muted);
            margin-bottom: 8px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            animation: slideInDown 0.5s ease 0.5s both;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.1), rgba(239, 36, 51, 0.05));
            border-left: 4px solid var(--ceet-red);
            color: var(--ceet-red);
        }

        .badge {
            border-radius: 8px;
            font-weight: 600;
            padding: 6px 12px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .list-group-item {
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s;
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
        }

        .list-group-item-action {
            border-radius: 10px;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .list-group-item-action:hover {
            background: rgba(239, 36, 51, 0.05);
            border-color: rgba(239, 36, 51, 0.2);
            transform: translateX(4px);
        }

        .table {
            font-size: 0.9rem;
        }

        .btn-outline-secondary {
            border-radius: 10px;
            transition: all 0.2s;
        }

        .btn-outline-secondary:hover {
            background-color: var(--ceet-gray-light);
            border-color: var(--ceet-border-light);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border: none;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(239, 36, 51, 0.25);
            color: white;
        }

        .card-header {
            border-bottom: 1px solid var(--ceet-border-light);
            border-radius: 0;
        }

        .card-footer {
            border-top: 1px solid var(--ceet-border-light);
            border-radius: 0;
        }

        @media (max-width: 768px) {
            .metric-value {
                font-size: 1.5rem;
            }
            .kpi-icon {
                width: 40px;
                height: 40px;
            }
        }
    </style>

    <x-slot name="header">
        <div style="animation: slideInDown 0.6s ease both;">
            <h1 class="h3 mb-1">Supervision réseau</h1>
            <p class="text-muted mb-0">
                Bonjour <strong>{{ explode(' ', $user->name)[0] }}</strong> — Suivi opérationnel de votre équipe
                au <strong>{{ now()->translatedFormat('d F Y') }}</strong>.
            </p>
        </div>
        <div class="d-flex gap-2">
            @can('reporting.view')
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Rapports</a>
            @endcan
            @can('incidents.create')
                <a href="{{ route('incidents.create') }}" class="btn btn-danger">+ Incident</a>
            @endcan
        </div>
    </x-slot>

    {{-- KPIs équipe --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Incidents ouverts équipe</span>
                            <div class="metric-value">{{ number_format($teamOpenCount) }}</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 6v6m0 0l3-3m-3 3l-3-3" /></svg>
                        </div>
                    </div>
                    <small class="text-muted">Sous votre supervision</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Critiques en attente</span>
                            <div class="metric-value">{{ number_format($pendingValidation->count()) }}</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 8v4m0 4v.01" /></svg>
                        </div>
                    </div>
                    <small class="text-muted">Priorité 1 à traiter en urgence</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Taux résolution équipe</span>
                            <div class="metric-value">{{ number_format($teamResolutionRate, 1, ',', ' ') }}%</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M8 12l2 2 4-4" /></svg>
                        </div>
                    </div>
                    <small class="text-muted">{{ $teamResolved }}/{{ $teamTotal }} ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="kpi-header">
                        <div>
                            <span class="kpi-label">Disponibilité réseau</span>
                            <div class="metric-value">{{ number_format($availabilityRate, 1, ',', ' ') }}%</div>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M8 12l2 2 4-4" /></svg>
                        </div>
                    </div>
                    <small class="text-muted">Mis à jour à {{ $lastCheckAt }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes incidents critiques --}}
    @if($pendingValidation->isNotEmpty())
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
            <div class="flex-shrink-0 fs-4">⚠️</div>
            <div>
                <div class="fw-bold">{{ $pendingValidation->count() }} incident(s) critique(s) nécessitent votre attention</div>
                <div class="small mt-1">
                    @foreach($pendingValidation->take(3) as $crit)
                        <a href="{{ route('incidents.edit', $crit) }}" class="text-danger fw-semibold me-2">
                            {{ $crit->code_incident }}
                        </a>
                    @endforeach
                    @if($pendingValidation->count() > 3)
                        <span class="text-muted">et {{ $pendingValidation->count() - 3 }} autre(s)...</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Table incidents équipe --}}
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Incidents ouverts — Mon équipe</h2>
                    <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-secondary btn-sm">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Départ</th>
                                <th>Opérateur</th>
                                <th>Priorité</th>
                                <th>En attente</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamOpenIncidents as $incident)
                                @php
                                    $waitingMinutes = $incident->duree_en_attente;
                                    $waitingClass = $waitingMinutes > 120
                                        ? 'text-danger fw-bold'
                                        : ($waitingMinutes > 60 ? 'text-warning fw-semibold' : 'text-success');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $incident->code_incident }}</td>
                                    <td>{{ $incident->departement?->nom ?? '-' }}</td>
                                    <td>
                                        <small>{{ $incident->operateur?->name ?? 'Non assigné' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $incident->priorite?->couleur ?? '#6c757d' }}">
                                            {{ $incident->priorite?->libelle ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="{{ $waitingClass }}">{{ $formatDuration($waitingMinutes) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-danger btn-sm">
                                            Superviser
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <div class="mb-2">✅</div>
                                        Aucun incident ouvert sous supervision.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panneau latéral superviseur --}}
        <div class="col-12 col-xl-4">

            {{-- Charge des opérateurs --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Charge des opérateurs</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($teamOperators as $operator)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold small">{{ $operator->name }}</div>
                                        <small class="text-muted">{{ $operator->departement?->nom ?? 'Sans départ' }}</small>
                                    </div>
                                    <span class="badge {{ $operator->open_count > 3 ? 'text-bg-danger' : ($operator->open_count > 1 ? 'text-bg-warning' : 'text-bg-success') }}">
                                        {{ $operator->open_count }} ouvert(s)
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted text-center py-3">
                                Aucun opérateur actif.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Répartition par statut --}}
            <div class="card">
                <div class="card-header bg-white fw-semibold">Répartition par statut</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($byStatus as $statusItem)
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:{{ $statusItem['color'] }};flex-shrink:0"></span>
                                    <span class="small">{{ $statusItem['label'] }}</span>
                                </div>
                                <span class="badge text-bg-light">{{ $statusItem['total'] }}</span>
                            </div>
                        @empty
                            <div class="list-group-item text-muted text-center py-3">Aucune donnée.</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white">
                    @can('reporting.view')
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-danger btn-sm w-100">
                            Voir les rapports complets
                        </a>
                    @endcan
                </div>
            </div>

        </div>
    </div>

    {{-- Incidents récents réseau (vue globale) --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Activité récente réseau</h2>
            <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm">Liste complète</a>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($recentIncidents as $incident)
                    <a href="{{ route('incidents.show', $incident) }}" class="list-group-item list-group-item-action px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">{{ $incident->code_incident }}</div>
                                <small class="text-muted">
                                    {{ $incident->departement?->nom ?? '-' }}
                                    — {{ $incident->cause?->libelle ?? 'Cause non renseignée' }}
                                </small>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge {{ $incident->status?->is_final ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ $incident->status?->libelle ?? 'N/A' }}
                                </span>
                                <div class="small text-muted mt-1">
                                    {{ $incident->date_debut ? Carbon::parse($incident->date_debut)->diffForHumans() : '-' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-muted text-center py-4">Aucun incident récent.</div>
                @endforelse
            </div>
        </div>
    </div>

</x-app-layout>
