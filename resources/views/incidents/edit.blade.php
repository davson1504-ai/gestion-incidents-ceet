<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Éditer l'incident {{ $incident->code_incident }}</h1>
            <span class="badge bg-secondary">{{ $incident->statut?->libelle }}</span>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('incidents.update', $incident) }}">
                @csrf
                @method('PUT')
                @include('incidents._form', ['incident' => $incident])
            </form>
        </div>
    </div>
</x-app-layout>
