<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Éditer {{ $departement->code }}</h1>
            <p class="text-muted mb-0">Mettez à jour la charge, le poste de répartition, le transformateur et les métadonnées d’exploitation.</p>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.departements.update', $departement) }}">
                @csrf
                @method('PUT')
                @include('catalogues.departements.partials.form', ['departement' => $departement])
            </form>
        </div>
    </div>
</x-app-layout>
