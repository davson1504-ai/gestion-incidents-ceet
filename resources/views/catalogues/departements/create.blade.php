<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Nouveau départ</h1>
            <p class="text-muted mb-0">Ajoutez un départ CEET avec ses caractéristiques techniques et sa charge maximale.</p>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.departements.store') }}">
                @csrf
                @include('catalogues.departements.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>
