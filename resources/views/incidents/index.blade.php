@php
    $statusChartLabels = $stats['byStatus']->pluck('label');
    $statusChartData   = $stats['byStatus']->pluck('total');
    $statusChartColors = $stats['byStatus']->pluck('color');

    $priorityChartLabels = $stats['byPriorite']->pluck('label');
    $priorityChartData   = $stats['byPriorite']->pluck('total');
    $priorityChartColors = $stats['byPriorite']->pluck('color');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
            <div>
                <h1 class="h4 mb-0">🚨 Incidents</h1>
                <small class="text-muted">Liste et tableau de bord filtrable</small>
            </div>
            @can('incidents.create')
                <a href="{{ route('incidents.create') }}" class="btn btn-primary btn-sm">
                    + Nouvel incident
                </a>
            @endcan
        </div>
    </x-slot>

    {{-- ── Filtres ──────────────────────────────────────────────────────── --}}
    <div class="card mb-4 shadow-sm filter-card">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('incidents.index') }}">
                <div class="col-6 col-md-3">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($departements as $dep)
                            <option value="{{ $dep->id }}" @selected($filters['departement_id'] == $dep->id)>
                                {{ $dep->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut->id }}" @selected($filters['status_id'] == $statut->id)>
                                {{ $statut->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Priorité</label>
                    <select name="priorite_id" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($priorites as $priorite)
                            <option value="{{ $priorite->id }}" @selected($filters['priorite_id'] == $priorite->id)>
                                {{ $priorite->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type_incident_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected($filters['type_incident_id'] == $type->id)>
                                {{ $type->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Recherche (code ou titre)</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="INC-2026… / Titre"
                           value="{{ $filters['q'] }}">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm flex-fill">🔍 Filtrer</button>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">✕</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── KPIs ──────────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm kpi-card border-0 border-start border-4 border-warning h-100">
                <div class="card-body text-center py-3">
                    <div class="h3 fw-bold text-warning mb-0">{{ $stats['openCount'] }}</div>
                    <div class="text-muted small">En cours</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm kpi-card border-0 border-start border-4 border-success h-100">
                <div class="card-body text-center py-3">
                    <div class="h3 fw-bold text-success mb-0">{{ $stats['closedCount'] }}</div>
                    <div class="text-muted small">Clôturés</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm kpi-card border-0 border-start border-4 border-info h-100">
                <div class="card-body text-center py-3">
                    <div class="h3 fw-bold text-info mb-0">
                        {{ number_format($stats['avgDuration'] ?? 0, 0, ',', ' ') }}
                    </div>
                    <div class="text-muted small">Durée moy. (min)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm kpi-card border-0 border-start border-4 border-dark h-100">
                <div class="card-body text-center py-3">
                    <div class="h3 fw-bold mb-0">{{ $incidents->total() }}</div>
                    <div class="text-muted small">Total filtré</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Graphiques (masqués sur très petit écran) ────────────────────── --}}
    <div class="row g-3 mb-4 d-none d-md-flex">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold border-0">Répartition par statut</div>
                <div class="card-body">
                    <canvas id="chartStatus" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold border-0">Répartition par priorité</div>
                <div class="card-body">
                    <canvas id="chartPriorite" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tableau des incidents ─────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-mobile-cards">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Titre</th>
                            <th class="d-none d-md-table-cell">Département</th>
                            <th>Statut</th>
                            <th class="d-none d-sm-table-cell">Priorité</th>
                            <th class="d-none d-lg-table-cell">Début</th>
                            <th class="d-none d-lg-table-cell">Durée (min)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            <tr>
                                <td data-label="Code">
                                    <span class="fw-semibold text-primary">
                                        {{ $incident->code_incident }}
                                    </span>
                                </td>
                                <td data-label="Titre">
                                    {{ \Illuminate\Support\Str::limit($incident->titre, 40) }}
                                </td>
                                <td data-label="Département" class="d-none d-md-table-cell">
                                    {{ $incident->departement?->nom ?? '—' }}
                                </td>
                                <td data-label="Statut">
                                    <span class="badge"
                                          style="background-color:{{ $incident->statut?->couleur ?? '#6c757d' }}">
                                        {{ $incident->statut?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td data-label="Priorité" class="d-none d-sm-table-cell">
                                    <span class="badge"
                                          style="background-color:{{ $incident->priorite?->couleur ?? '#e9ecef' }}">
                                        {{ $incident->priorite?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td data-label="Début" class="d-none d-lg-table-cell text-nowrap">
                                    {{ optional($incident->date_debut)->format('d/m/Y H:i') }}
                                </td>
                                <td data-label="Durée (min)" class="d-none d-lg-table-cell">
                                    {{ $incident->duree_minutes ?? '—' }}
                                </td>
                                <td data-label="Actions" class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                                        <a href="{{ route('incidents.show', $incident) }}"
                                           class="btn btn-outline-secondary btn-sm">
                                            👁️
                                        </a>
                                        @can('incidents.update')
                                            <a href="{{ route('incidents.edit', $incident) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                ✏️
                                            </a>
                                        @endcan
                                        @can('incidents.delete')
                                            <form action="{{ route('incidents.destroy', $incident) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Supprimer cet incident ?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">🗑️</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Aucun incident trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $incidents->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- ── Chart.js ─────────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('chartStatus'), {
            type: 'bar',
            data: {
                labels: {!! $statusChartLabels->toJson() !!},
                datasets: [{
                    data: {!! $statusChartData->toJson() !!},
                    backgroundColor: {!! $statusChartColors->toJson() !!},
                    borderRadius: 4,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales:  { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        new Chart(document.getElementById('chartPriorite'), {
            type: 'doughnut',
            data: {
                labels: {!! $priorityChartLabels->toJson() !!},
                datasets: [{
                    data: {!! $priorityChartData->toJson() !!},
                    backgroundColor: {!! $priorityChartColors->toJson() !!},
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    </script>

</x-app-layout>