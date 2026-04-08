@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Catalogues - Priorites</h1>
            @can('catalogues.manage')
                <a href="{{ route('catalogues.priorites.create') }}" class="btn btn-primary">Nouvelle priorite</a>
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
                    <th>Niveau</th>
                    <th>Actif</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($priorites as $priorite)
                    <tr>
                        <td class="fw-semibold">{{ $priorite->code }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $priorite->couleur ?? '#6c757d' }};">
                                {{ $priorite->libelle }}
                            </span>
                        </td>
                        <td>{{ Str::limit($priorite->description, 80) }}</td>
                        <td>{{ $priorite->niveau }}</td>
                        <td>
                            <span class="badge {{ $priorite->is_active ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{ $priorite->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('catalogues.manage')
                                <a href="{{ route('catalogues.priorites.edit', $priorite) }}" class="btn btn-sm btn-outline-primary">Editer</a>
                                <form method="POST" action="{{ route('catalogues.priorites.destroy', $priorite) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
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
            {{ $priorites->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-app-layout>

