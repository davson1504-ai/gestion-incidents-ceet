<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Editer statut {{ $statut->code }}</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.statuts.update', $statut) }}">
                @csrf
                @method('PUT')
                @include('catalogues.statuts.partials.form', ['statut' => $statut])
            </form>
        </div>
    </div>
</x-app-layout>

