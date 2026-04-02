<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Nouveau départ</h1>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.departements.store') }}">
                @csrf
                @include('catalogues.departements.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>
