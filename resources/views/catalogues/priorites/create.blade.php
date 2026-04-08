<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Nouvelle priorite</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.priorites.store') }}">
                @csrf
                @include('catalogues.priorites.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>

