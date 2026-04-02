<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Éditer départ {{ $departement->code }}</h1>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.departements.update', $departement) }}">
                @csrf
                @method('PUT')
                @include('catalogues.departements.partials.form', ['departement' => $departement])
            </form>
        </div>
    </div>
</x-app-layout>
