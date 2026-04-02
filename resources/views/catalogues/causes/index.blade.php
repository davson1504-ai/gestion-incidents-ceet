<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">Catalogues — Causes</h1>
            </div>
            @can('catalogues.manage')
                <a href="{{ route('catalogues.causes.create') }}" class="btn btn-primary">Nouvelle cause</a>
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
                        <th>Libellé</th>
                        <th>Type</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($causes as $cause)
                    <tr>
                        <td class="fw-semibold">{{ $cause->code }}</td>
                        <td>{{ $cause->libelle }}</td>
                        <td>{{ $cause->typeIncident?->libelle }}</td>
                        <td class="text-end">
                            @can('catalogues.manage')
                                <a href="{{ route('catalogues.causes.edit', $cause) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                                <form method="POST" action="{{ route('catalogues.causes.destroy', $cause) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
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
            {{ $causes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-app-layout>
