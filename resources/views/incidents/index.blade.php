@php
    $statusChartLabels = collect($stats['byStatus'])->pluck('label');
    $statusChartData = collect($stats['byStatus'])->pluck('total');
    $statusChartColors = collect($stats['byStatus'])->pluck('color');

    $priorityChartLabels = collect($stats['byPriorite'])->pluck('label');
    $priorityChartData = collect($stats['byPriorite'])->pluck('total');
    $priorityChartColors = collect($stats['byPriorite'])->pluck('color');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-0">Gestion des incidents</h1>
                <p class="text-muted mb-0">Pilotage, filtrage et suivi du cycle de vie des incidents</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('incidents.export', request()->query()) }}" class="btn btn-outline-secondary">
                    Export CSV
                </a>
                @can('incidents.create')
                    <a href="{{ route('incidents.create') }}" class="btn btn-primary">Nouvel incident</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('incidents.index') }}">
                <div class="col-12 col-md-3">
                    <label class="form-label">Departement</label>
                    <select name="departement_id" class="form-select js-tom-select" data-placeholder="Tous les departements">
                        <option value="">Tous</option>
                        @foreach($departements as $dep)
                            <option value="{{ $dep->id }}" @selected($filters['departement_id'] == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status_id" class="form-select js-tom-select" data-placeholder="Tous les statuts">
                        <option value="">Tous</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut->id }}" @selected($filters['status_id'] == $statut->id)>{{ $statut->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Priorite</label>
                    <select name="priorite_id" class="form-select js-tom-select" data-placeholder="Toutes les priorites">
                        <option value="">Toutes</option>
                        @foreach($priorites as $priorite)
                            <option value="{{ $priorite->id }}" @selected($filters['priorite_id'] == $priorite->id)>{{ $priorite->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Type incident</label>
                    <select id="incident-filter-type" name="type_incident_id" class="form-select js-tom-select" data-placeholder="Tous les types">
                        <option value="">Tous</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected($filters['type_incident_id'] == $type->id)>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Cause</label>
                    <select
                        id="incident-filter-cause"
                        name="cause_id"
                        class="form-select js-tom-select"
                        data-placeholder="Toutes les causes"
                        data-selected-cause="{{ $filters['cause_id'] }}"
                        data-endpoint-template="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}"
                    >
                        <option value="">Toutes</option>
                        @foreach($causes as $cause)
                            <option value="{{ $cause->id }}" @selected($filters['cause_id'] == $cause->id)>{{ $cause->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Operateur</label>
                    <select name="operateur_id" class="form-select js-tom-select" data-placeholder="Tous les operateurs">
                        <option value="">Tous</option>
                        @foreach($operateurs as $operateur)
                            <option value="{{ $operateur->id }}" @selected($filters['operateur_id'] == $operateur->id)>{{ $operateur->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Recherche (code ou titre)</label>
                    <input type="text" name="q" class="form-control" placeholder="INC-2026..., titre..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filtrer</button>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Incidents en cours</p>
                    <div class="metric-value text-warning">{{ number_format($stats['openCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Incidents clotures</p>
                    <div class="metric-value text-success">{{ number_format($stats['closedCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Duree moyenne (min)</p>
                    <div class="metric-value text-info">{{ number_format($stats['avgDuration'] ?? 0, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Resultats filtres</p>
                    <div class="metric-value text-primary">{{ number_format($incidents->total()) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Repartition par statut</div>
                <div class="card-body">
                    <canvas id="chartStatus" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Repartition par priorite</div>
                <div class="card-body">
                    <canvas id="chartPriorite" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Liste des incidents</span>
            <span class="badge text-bg-secondary">{{ $incidents->total() }} resultat(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-mobile-cards">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Titre</th>
                        <th class="d-none d-md-table-cell">Departement</th>
                        <th>Statut</th>
                        <th class="d-none d-sm-table-cell">Priorite</th>
                        <th class="d-none d-lg-table-cell">Debut</th>
                        <th class="d-none d-lg-table-cell">Duree (min)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $incident)
                        <tr>
                            <td data-label="Code"><span class="fw-semibold text-primary">{{ $incident->code_incident }}</span></td>
                            <td data-label="Titre">{{ \Illuminate\Support\Str::limit($incident->titre, 50) }}</td>
                            <td data-label="Departement" class="d-none d-md-table-cell">{{ $incident->departement?->nom ?? '-' }}</td>
                            <td data-label="Statut">
                                <span class="badge" style="background-color: {{ $incident->statut?->couleur ?? '#6c757d' }}">
                                    {{ $incident->statut?->libelle ?? 'N/A' }}
                                </span>
                            </td>
                            <td data-label="Priorite" class="d-none d-sm-table-cell">
                                <span class="badge" style="background-color: {{ $incident->priorite?->couleur ?? '#adb5bd' }}">
                                    {{ $incident->priorite?->libelle ?? 'N/A' }}
                                </span>
                            </td>
                            <td data-label="Debut" class="d-none d-lg-table-cell">{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td data-label="Duree (min)" class="d-none d-lg-table-cell">{{ $incident->duree_minutes ?? '-' }}</td>
                            <td data-label="Actions" class="text-end">
                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                    <a href="{{ route('incidents.show', $incident) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                                    @can('incidents.update')
                                        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-sm btn-outline-primary">Editer</a>
                                    @endcan
                                    @can('incidents.delete')
                                        <form action="{{ route('incidents.destroy', $incident) }}" method="POST" onsubmit="return confirm('Supprimer cet incident ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Aucun incident trouve pour ce filtre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $incidents->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const createChart = (id, config) => {
            const element = document.getElementById(id);
            if (element) {
                new Chart(element, config);
            }
        };

        createChart('chartStatus', {
            type: 'bar',
            data: {
                labels: @json($statusChartLabels),
                datasets: [{
                    data: @json($statusChartData),
                    backgroundColor: @json($statusChartColors),
                    borderRadius: 6
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        createChart('chartPriorite', {
            type: 'doughnut',
            data: {
                labels: @json($priorityChartLabels),
                datasets: [{
                    data: @json($priorityChartData),
                    backgroundColor: @json($priorityChartColors)
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });

        const typeSelect = document.getElementById('incident-filter-type');
        const causeSelect = document.getElementById('incident-filter-cause');

        if (typeSelect && causeSelect) {
            const allCauses = @json($causes->map(fn ($cause) => ['id' => $cause->id, 'libelle' => $cause->libelle])->values());
            const endpointTemplate = causeSelect.dataset.endpointTemplate;
            const initialCauseId = String(causeSelect.dataset.selectedCause || '');

            const setOptions = (items, selectedId = '') => {
                const options = [{ value: '', text: 'Toutes' }, ...items.map((item) => ({
                    value: String(item.id),
                    text: item.libelle
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
                    if (option.value === String(selectedId || '')) {
                        element.selected = true;
                    }
                    causeSelect.appendChild(element);
                });
            };

            const loadCauses = async (typeId, selected = '') => {
                if (!typeId) {
                    setOptions(allCauses, selected);
                    return;
                }

                try {
                    const endpoint = endpointTemplate.replace('__TYPE__', encodeURIComponent(typeId));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load causes');
                    }

                    const causes = await response.json();
                    setOptions(causes, selected);
                } catch (error) {
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
        }
    </script>
</x-app-layout>
