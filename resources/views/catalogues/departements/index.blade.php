<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">Catalogues — Départs</h1>
                <small class="text-muted">Gestion des départs (poste, périmètre)</small>
            </div>
            @can('catalogues.manage')
                <a href="{{ route('catalogues.departements.create') }}" class="btn btn-primary">Nouveau départ</a>
            @endcan
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Périmètre</th>
                        <th>Poste répartition</th>
                        <th>Poste source</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($departements as $dep)
                    <tr>
                        <td class="fw-semibold">{{ $dep->code }}</td>
                        <td>{{ $dep->nom }}</td>
                        <td>{{ $dep->zone }}</td>
                        <td>{{ $dep->poste_repartition }}</td>
                        <td>{{ $dep->poste_source }}</td>
                        <td class="text-end">
                            @can('catalogues.manage')
                                <a href="{{ route('catalogues.departements.edit', $dep) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                                <form method="POST" action="{{ route('catalogues.departements.destroy', $dep) }}" class="d-inline" onsubmit="return confirm('Supprimer ce départ ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $departements->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-app-layout>
