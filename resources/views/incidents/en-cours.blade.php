@php
    use Illuminate\Support\Carbon;

    $formatDuration = static function (?int $minutes): string {
        if ($minutes === null) {
            return '-';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'j';
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = $hours . 'h';
        }
        $parts[] = $remainingMinutes . 'min';

        return implode(' ', $parts);
    };

    $plusAncienLabel = $plusAncien ? $formatDuration($plusAncien->duree_en_attente) : '-';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Incidents en cours</h1>
            <p class="text-muted mb-0">Tableau de bord opérationnel des incidents non résolus, triés par criticité puis ancienneté.</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Total en cours</span>
                    <div class="metric-value mt-2" id="en-cours-total">{{ number_format($totalEnCours) }}</div>
                    <div class="small text-muted mt-2">Compteur mis à jour automatiquement toutes les 60 secondes.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents critiques</span>
                    <div class="metric-value mt-2" id="en-cours-critiques">{{ number_format($critiquesCount) }}</div>
                    <div class="small text-muted mt-2">Priorité de niveau 1 actuellement ouverte.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Plus ancien</span>
                    <div class="metric-value mt-2" id="en-cours-plus-ancien">{{ $plusAncienLabel }}</div>
                    <div class="small text-muted mt-2" id="en-cours-plus-ancien-code">{{ $plusAncien?->code_incident ?? 'Aucun incident ouvert' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('incidents.en-cours') }}" class="row g-3 align-items-end">
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
                    <label class="form-label fw-semibold">Priorité</label>
                    <select name="priorite_id" class="form-select js-tom-select" data-placeholder="Toutes les priorités">
                        <option value="">Toutes les priorités</option>
                        @foreach($priorites as $priorite)
                            <option value="{{ $priorite->id }}" @selected((string) $filters['priorite_id'] === (string) $priorite->id)>{{ $priorite->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Code incident ou titre">
                </div>
                <div class="col-12 col-md-6 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-danger">Appliquer les filtres</button>
                    <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-danger">Retour à la liste complète</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Départ</th>
                            <th>Type</th>
                            <th>Priorité</th>
                            <th>Date début</th>
                            <th>Durée attente</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            @php
                                $waitingMinutes = $incident->duree_en_attente;
                                $waitingClass = $waitingMinutes > 120
                                    ? 'text-danger'
                                    : ($waitingMinutes > 60 ? 'text-warning' : 'text-success');
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $incident->code_incident }}</td>
                                <td>{{ $incident->departement?->nom ?? '-' }}</td>
                                <td>{{ $incident->typeIncident?->libelle ?? '-' }}</td>
                                <td>
                                    <span class="badge text-bg-light">{{ $incident->priorite?->libelle ?? '-' }}</span>
                                </td>
                                <td>{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="fw-semibold {{ $waitingClass }}">{{ $formatDuration($waitingMinutes) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-danger btn-sm">Prendre en charge</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Aucun incident ouvert ne correspond aux filtres.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="small text-muted">Affichage de {{ $incidents->firstItem() ?? 0 }} à {{ $incidents->lastItem() ?? 0 }} sur {{ $incidents->total() }} incidents ouverts</span>
            {{ $incidents->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        (() => {
            const endpoint = @json(request()->fullUrl());
            const totalNode = document.getElementById('en-cours-total');
            const criticalNode = document.getElementById('en-cours-critiques');
            const oldestNode = document.getElementById('en-cours-plus-ancien');
            const oldestCodeNode = document.getElementById('en-cours-plus-ancien-code');

            if (!totalNode || !criticalNode || !oldestNode || !oldestCodeNode) {
                return;
            }

            const refreshCounters = async () => {
                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Impossible de rafraîchir les compteurs.');
                    }

                    const payload = await response.json();
                    totalNode.textContent = payload.totalEnCours ?? '0';
                    criticalNode.textContent = payload.critiquesCount ?? '0';
                    oldestNode.textContent = payload.plusAncien?.label ?? '-';
                    oldestCodeNode.textContent = payload.plusAncien?.code_incident ?? 'Aucun incident ouvert';
                } catch (_error) {
                }
            };

            window.setInterval(refreshCounters, 60000);
        })();
    </script>
</x-app-layout>
