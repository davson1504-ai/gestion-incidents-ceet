@php
    use Illuminate\Support\Str;

    $from = $incidents->firstItem() ?? 0;
    $to = $incidents->lastItem() ?? 0;
    $avgDuration = $stats['avgDuration'] !== null ? (int) round($stats['avgDuration']) : null;
    $avgDurationLabel = $avgDuration !== null ? sprintf('%dh %02dmin', intdiv($avgDuration, 60), $avgDuration % 60) : '-';
    $exportFilters = collect($filters)->filter(fn ($value) => filled($value))->all();

    $formatDuration = static function (?int $minutes): string {
        if ($minutes === null) {
            return '-';
        }

        return sprintf('%dh %02dmin', intdiv($minutes, 60), $minutes % 60);
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">{{ $listContext['title'] }}</h1>
            <p class="text-muted mb-0">{{ $listContext['subtitle'] }}</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents ouverts</span>
                    <div class="metric-value mt-2">{{ number_format($stats['openCount']) }}</div>
                    <div class="small text-muted mt-2">Statuts non finaux encore actifs.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents clôturés</span>
                    <div class="metric-value mt-2">{{ number_format($stats['closedCount']) }}</div>
                    <div class="small text-muted mt-2">Statuts finaux enregistrés.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Durée moyenne</span>
                    <div class="metric-value mt-2">{{ $avgDurationLabel }}</div>
                    <div class="small text-muted mt-2">Moyenne calculée sur les incidents clôturés.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column gap-2">
                    <span class="text-uppercase small text-muted fw-semibold">Exports et suivi</span>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-danger btn-sm">Incidents en cours</a>
                        <a href="{{ route('incidents.export', array_merge($exportFilters, ['format' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm">CSV</a>
                        <a href="{{ route('incidents.export', array_merge($exportFilters, ['format' => 'excel'])) }}" class="btn btn-outline-success btn-sm">Excel</a>
                    </div>
                    @can('incidents.create')
                        <a href="{{ route('incidents.create') }}" class="btn btn-danger btn-sm mt-2">Déclarer un incident</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($listContext['indexRoute']) }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Code incident ou titre">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="status_id" class="form-select js-tom-select" data-placeholder="Tous les statuts">
                        <option value="">Tous les statuts</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut->id }}" @selected((string) $filters['status_id'] === (string) $statut->id)>{{ $statut->libelle }}</option>
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
                    <label class="form-label fw-semibold">Type d’incident</label>
                    <select id="incident-filter-type" name="type_incident_id" class="form-select js-tom-select" data-placeholder="Tous les types">
                        <option value="">Tous les types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected((string) $filters['type_incident_id'] === (string) $type->id)>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Cause</label>
                    <select
                        id="incident-filter-cause"
                        name="cause_id"
                        class="form-select js-tom-select"
                        data-placeholder="Toutes les causes"
                        data-selected-cause="{{ $filters['cause_id'] }}"
                        data-endpoint-template="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}"
                    >
                        <option value="">Toutes les causes</option>
                        @foreach($causes as $cause)
                            <option value="{{ $cause->id }}" @selected((string) $filters['cause_id'] === (string) $cause->id)>{{ $cause->libelle }}</option>
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
                    <label class="form-label fw-semibold">Opérateur</label>
                    <select name="operateur_id" class="form-select js-tom-select" data-placeholder="Tous les opérateurs">
                        <option value="">Tous les opérateurs</option>
                        @foreach($operateurs as $operateur)
                            <option value="{{ $operateur->id }}" @selected((string) $filters['operateur_id'] === (string) $operateur->id)>{{ $operateur->name }}</option>
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
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-danger">Appliquer les filtres</button>
                    <a href="{{ route($listContext['indexRoute']) }}" class="btn btn-outline-secondary">Réinitialiser</a>
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
                            <th>Date début</th>
                            <th>Départ</th>
                            <th>Type</th>
                            <th>Cause</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th>Durée</th>
                            <th>Opérateur</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            @php
                                $duration = $incident->duree_minutes;
                                if ($duration === null && $incident->date_debut && $incident->date_fin) {
                                    $duration = $incident->date_debut->diffInMinutes($incident->date_fin);
                                }
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $incident->code_incident }}</td>
                                <td>{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $incident->departement?->nom ?? '-' }}</td>
                                <td>{{ $incident->typeIncident?->libelle ?? '-' }}</td>
                                <td>{{ Str::limit($incident->cause?->libelle ?? '-', 36) }}</td>
                                <td>
                                    <span class="badge {{ $incident->status?->is_final ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ $incident->status?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-light">{{ $incident->priorite?->libelle ?? '-' }}</span>
                                </td>
                                <td>{{ $formatDuration($duration) }}</td>
                                <td>{{ $incident->operateur?->name ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary btn-sm">Voir</a>
                                        @can('incidents.update')
                                            <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-danger btn-sm">Éditer</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">Aucun incident trouvé pour ces filtres.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="small text-muted">Affichage de {{ $from }} à {{ $to }} sur {{ $incidents->total() }} incidents</span>
            {{ $incidents->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        (() => {
            const typeSelect = document.getElementById('incident-filter-type');
            const causeSelect = document.getElementById('incident-filter-cause');

            if (!typeSelect || !causeSelect) {
                return;
            }

            const allCauses = @json($causes->map(fn ($cause) => ['id' => $cause->id, 'libelle' => $cause->libelle])->values());
            const endpointTemplate = causeSelect.dataset.endpointTemplate;
            const initialCauseId = String(causeSelect.dataset.selectedCause || '');

            const setOptions = (items, selectedId = '') => {
                const options = [{ value: '', text: 'Toutes les causes' }, ...items.map((item) => ({
                    value: String(item.id),
                    text: item.libelle,
                }))];

                if (causeSelect.tomselect) {
                    const control = causeSelect.tomselect;
                    control.clear(true);
                    control.clearOptions();
                    control.addOptions(options);
                    control.refreshOptions(false);
                    control.setValue(String(selectedId || ''), true);
                    return;
                }

                causeSelect.innerHTML = '';
                options.forEach((option) => {
                    const element = document.createElement('option');
                    element.value = option.value;
                    element.textContent = option.text;
                    element.selected = option.value === String(selectedId || '');
                    causeSelect.appendChild(element);
                });
            };

            const loadCauses = async (typeId, selectedId = '') => {
                if (!typeId) {
                    setOptions(allCauses, selectedId);
                    return;
                }

                try {
                    const response = await fetch(endpointTemplate.replace('__TYPE__', encodeURIComponent(typeId)), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Impossible de charger les causes.');
                    }

                    const causes = await response.json();
                    setOptions(causes, selectedId);
                } catch (_error) {
                    setOptions([], '');
                }
            };

            typeSelect.addEventListener('change', () => {
                loadCauses(typeSelect.value, '');
            });

            if (typeSelect.value) {
                loadCauses(typeSelect.value, initialCauseId);
            } else {
                setOptions(allCauses, initialCauseId);
            }
        })();
    </script>
</x-app-layout>
