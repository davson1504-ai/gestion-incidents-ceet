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
            <h1 class="h3 mb-1">Bonjour, {{ explode(' ', $user->name)[0] }} 👷</h1>
            <p class="text-muted mb-0">Voici vos incidents en cours et votre activité du jour.</p>
        </div>
        @can('incidents.create')
            <a href="{{ route('incidents.create') }}" class="btn btn-danger">
                + Déclarer un incident
            </a>
        @endcan
    </x-slot>

    {{-- KPIs personnels --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100 border-danger-subtle">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Mes incidents ouverts</span>
                    <div class="metric-value mt-2 text-danger">{{ number_format($myTotalOpen) }}</div>
                    <div class="small text-muted mt-2">Assignés ou déclarés par vous</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Résolus aujourd'hui</span>
                    <div class="metric-value mt-2 text-success">{{ number_format($myResolvedToday) }}</div>
                    <div class="small text-muted mt-2">Incidents clôturés ce jour</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Ce mois</span>
                    <div class="metric-value mt-2">{{ number_format($myTotalMonth) }}</div>
                    <div class="small text-muted mt-2">Incidents déclarés en {{ now()->translatedFormat('F') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Réseau global</span>
                    <div class="metric-value mt-2">{{ number_format($availabilityRate, 1, ',', ' ') }}%</div>
                    <div class="small text-muted mt-2">Disponibilité réseau</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- File de travail personnelle --}}
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Ma file d'incidents à traiter</h2>
                    <a href="{{ route('incidents.mine') }}" class="btn btn-outline-secondary btn-sm">Voir tout</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Départ</th>
                                <th>Priorité</th>
                                <th>En attente</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myOpenIncidents as $incident)
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
                                        <span class="badge text-bg-light">
                                            {{ $incident->priorite?->libelle ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="{{ $waitingClass }}">
                                        {{ $formatDuration($waitingMinutes) }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-danger btn-sm">
                                            Traiter
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <div class="mb-2">✅</div>
                                        Aucun incident ouvert à votre charge. Bonne journée !
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($myOpenIncidents->isNotEmpty())
                    <div class="card-footer bg-white">
                        <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-danger btn-sm">
                            Voir tous les incidents en cours →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panneau latéral --}}
        <div class="col-12 col-xl-4">

            {{-- Actions rapides --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Actions rapides</div>
                <div class="card-body d-grid gap-2">
                    @can('incidents.create')
                        <a href="{{ route('incidents.create') }}" class="btn btn-danger">
                            + Déclarer un nouvel incident
                        </a>
                    @endcan
                    <a href="{{ route('incidents.mine') }}" class="btn btn-outline-secondary">
                        Mes incidents
                    </a>
                    <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-secondary">
                        Incidents en cours réseau
                    </a>
                </div>
            </div>

            {{-- Dernières actions --}}
            <div class="card">
                <div class="card-header bg-white fw-semibold">Mes dernières actions</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($myRecentActions as $action)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="badge text-bg-light text-uppercase small">
                                            {{ $action->action_type }}
                                        </span>
                                        <div class="small mt-1">{{ $action->description }}</div>
                                        @if($action->incident)
                                            <small class="text-muted">{{ $action->incident->code_incident }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted text-nowrap">
                                        {{ $action->action_date?->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted text-center py-4">
                                Aucune action récente.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
