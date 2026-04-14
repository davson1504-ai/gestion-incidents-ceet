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
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Supervision réseau</h1>
            <p class="text-muted mb-0">
                Bonjour {{ explode(' ', $user->name)[0] }} — Suivi opérationnel de votre équipe
                au {{ now()->translatedFormat('d F Y') }}.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Rapports</a>
            @can('incidents.create')
                <a href="{{ route('incidents.create') }}" class="btn btn-danger">+ Incident</a>
            @endcan
        </div>
    </x-slot>

    {{-- KPIs équipe --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100 border-danger-subtle">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents ouverts équipe</span>
                    <div class="metric-value mt-2 text-danger">{{ number_format($teamOpenCount) }}</div>
                    <div class="small text-muted mt-2">Sous votre supervision</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100 border-warning-subtle">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Critiques en attente</span>
                    <div class="metric-value mt-2 text-warning">{{ number_format($pendingValidation->count()) }}</div>
                    <div class="small text-muted mt-2">Priorité 1 à traiter en urgence</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Taux résolution équipe</span>
                    <div class="metric-value mt-2 text-success">{{ number_format($teamResolutionRate, 1, ',', ' ') }}%</div>
                    <div class="small text-muted mt-2">{{ $teamResolved }}/{{ $teamTotal }} ce mois</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Disponibilité réseau</span>
                    <div class="metric-value mt-2">{{ number_format($availabilityRate, 1, ',', ' ') }}%</div>
                    <div class="small text-muted mt-2">Mis à jour à {{ $lastCheckAt }}</div>
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
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-danger btn-sm w-100">
                        Voir les rapports complets
                    </a>
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
