@php
    $selected = $selectedDepartement;

    $chargeBadgeClass = static function ($charge) {
        if ($charge === null) {
            return 'text-bg-light';
        }

        if ($charge > 400) {
            return 'text-bg-danger';
        }

        if ($charge > 200) {
            return 'text-bg-warning';
        }

        return 'text-bg-success';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Catalogue des départs</h1>
            <p class="text-muted mb-0">Référentiel CEET enrichi avec postes, transformateurs et charges maximales d’exploitation.</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Total départs</span>
                    <div class="metric-value mt-2">{{ number_format($stats['totalCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Départs actifs</span>
                    <div class="metric-value mt-2">{{ number_format($stats['activeCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Zones couvertes</span>
                    <div class="metric-value mt-2">{{ number_format($stats['zoneCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Charge cumulée</span>
                    <div class="metric-value mt-2">{{ number_format((float) $stats['totalPowerMw'], 0, ',', ' ') }} A</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('catalogues.departements.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Code, nom, zone, poste de répartition">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2 flex-wrap">
                    <button class="btn btn-danger" type="submit">Rechercher</button>
                    <a href="{{ route('catalogues.departements.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    @can('catalogues.manage')
                        <a href="{{ route('catalogues.departements.create') }}" class="btn btn-outline-danger">Nouveau départ</a>
                    @endcan
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Zone</th>
                                    <th>Poste répartition</th>
                                    <th>Charge max (A)</th>
                                    <th>Transformateur</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departements as $departement)
                                    <tr>
                                        <td class="fw-semibold">{{ $departement->code }}</td>
                                        <td>{{ $departement->nom }}</td>
                                        <td>{{ $departement->zone ?: '-' }}</td>
                                        <td>{{ $departement->poste_repartition ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $chargeBadgeClass($departement->charge_maximale) }}">
                                                {{ $departement->charge_maximale !== null ? number_format((float) $departement->charge_maximale, 0, ',', ' ') . ' A' : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $departement->transformateur ?: '-' }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('catalogues.departements.index', ['selected' => $departement->id] + request()->except('page', 'selected')) }}" class="btn btn-outline-secondary btn-sm">Voir</a>
                                                @can('catalogues.manage')
                                                    <a href="{{ route('catalogues.departements.edit', $departement) }}" class="btn btn-outline-danger btn-sm">Éditer</a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">Aucun départ trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $departements->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    @if($selected)
                        <h2 class="h4 mb-1">{{ $selected->nom }}</h2>
                        <p class="text-muted mb-3">{{ $selected->code }}</p>

                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Poste de répartition</span>
                                <span>{{ $selected->poste_repartition ?: '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Charge maximale</span>
                                <span class="badge {{ $chargeBadgeClass($selected->charge_maximale) }}">
                                    {{ $selected->charge_maximale !== null ? number_format((float) $selected->charge_maximale, 0, ',', ' ') . ' ' . ($selected->charge_unite ?: 'A') : 'N/A' }}
                                </span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Transformateur</span>
                                <span>{{ $selected->transformateur ?: '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Arrivée</span>
                                <span>{{ $selected->arrivee ?: '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Direction exploitation</span>
                                <span>{{ $selected->direction_exploitation ?: '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span>Statut</span>
                                <span class="badge {{ $selected->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $selected->is_active ? 'Actif' : 'Inactif' }}</span>
                            </div>
                        </div>

                        @if($selected->description)
                            <div class="mt-3">
                                <h3 class="h6">Description technique</h3>
                                <p class="text-muted mb-0">{{ $selected->description }}</p>
                            </div>
                        @endif

                        @can('catalogues.manage')
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <a href="{{ route('catalogues.departements.edit', $selected) }}" class="btn btn-danger btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('catalogues.departements.destroy', $selected) }}" onsubmit="return confirm('Supprimer ce départ ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">Supprimer</button>
                                </form>
                            </div>
                        @endcan
                    @else
                        <div class="text-muted">Sélectionnez un départ dans la liste pour afficher ses détails techniques.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
