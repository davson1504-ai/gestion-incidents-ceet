@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Catalogues - Statuts</h1>
            @can('catalogues.manage')
                <a href="{{ route('catalogues.statuts.create') }}" class="btn btn-primary">Nouveau statut</a>
            @endcan
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Libelle</th>
                    <th>Description</th>
                    <th>Ordre</th>
                    <th>Final</th>
                    <th>Actif</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($statuts as $statut)
                    <tr>
                        <td class="fw-semibold">{{ $statut->code }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $statut->couleur ?? '#6c757d' }};">
                                {{ $statut->libelle }}
                            </span>
                        </td>
                        <td>{{ Str::limit($statut->description, 80) }}</td>
                        <td>{{ $statut->ordre }}</td>
                        <td>
                            <span class="badge {{ $statut->is_final ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $statut->is_final ? 'Oui' : 'Non' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $statut->is_active ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{ $statut->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('catalogues.manage')
                                <a href="{{ route('catalogues.statuts.edit', $statut) }}" class="btn btn-sm btn-outline-primary">Editer</a>
                                <form method="POST" action="{{ route('catalogues.statuts.destroy', $statut) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf
                                    @method('DELETE')
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
            {{ $statuts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-app-layout>

