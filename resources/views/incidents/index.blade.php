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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">Incidents</h1>
                <small class="text-muted">Tableau de bord et liste filtrable</small>
            </div>
            @can('incidents.create')
                <a href="{{ route('incidents.create') }}" class="btn btn-primary">
                    Nouvel incident
                </a>
            @endcan
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($departements as $dep)
                            <option value="{{ $dep->id }}" @selected($filters['departement_id'] == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut->id }}" @selected($filters['status_id'] == $statut->id)>{{ $statut->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priorité</label>
                    <select name="priorite_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($priorites as $priorite)
                            <option value="{{ $priorite->id }}" @selected($filters['priorite_id'] == $priorite->id)>{{ $priorite->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type_incident_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected($filters['type_incident_id'] == $type->id)>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Recherche (code ou titre)</label>
                    <input type="text" name="q" class="form-control" placeholder="INC-2026... / Titre"
                           value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Incidents ouverts</div>
                    <div class="h4 mb-0">{{ $stats['openCount'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Incidents clôturés</div>
                    <div class="h4 mb-0">{{ $stats['closedCount'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Durée moyenne (minutes)</div>
                    <div class="h4 mb-0">{{ number_format($stats['avgDuration'] ?? 0, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total filtré</div>
                    <div class="h4 mb-0">{{ $incidents->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Répartition par statut</div>
                <div class="card-body">
                    <canvas id="chartStatus" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Répartition par priorité</div>
                <div class="card-body">
                    <canvas id="chartPriorite" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Titre</th>
                            <th>Département</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th>Début</th>
                            <th>Durée (min)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            <tr>
                                <td class="fw-semibold">{{ $incident->code_incident }}</td>
                                <td>{{ $incident->titre }}</td>
                                <td>{{ $incident->departement?->nom }}</td>
                                <td>
                                    <span class="badge"
                                          style="background-color: {{ $incident->statut?->couleur ?? '#6c757d' }}; color:#fff;">
                                        {{ $incident->statut?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge"
                                          style="background-color: {{ $incident->priorite?->couleur ?? '#e9ecef' }};">
                                        {{ $incident->priorite?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ optional($incident->date_debut)->format('d/m/Y H:i') }}</td>
                                <td>{{ $incident->duree_minutes ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary">Voir</a>
                                        @can('incidents.update')
                                            <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-primary">Éditer</a>
                                        @endcan
                                        @can('incidents.delete')
                                            <form action="{{ route('incidents.destroy', $incident) }}" method="POST" onsubmit="return confirm('Supprimer cet incident ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger">Supprimer</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">Aucun incident trouvé.</td>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const statusCtx = document.getElementById('chartStatus');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: {!! $statusChartLabels->toJson() !!},
                datasets: [{
                    label: 'Incidents',
                    data: {!! $statusChartData->toJson() !!},
                    backgroundColor: {!! $statusChartColors->toJson() !!},
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
            }
        });

        const prioriteCtx = document.getElementById('chartPriorite');
        new Chart(prioriteCtx, {
            type: 'doughnut',
            data: {
                labels: {!! $priorityChartLabels->toJson() !!},
                datasets: [{
                    data: {!! $priorityChartData->toJson() !!},
                    backgroundColor: {!! $priorityChartColors->toJson() !!},
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</x-app-layout>
