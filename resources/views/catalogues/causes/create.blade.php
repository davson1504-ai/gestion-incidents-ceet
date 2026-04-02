<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Nouvelle cause</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.causes.store') }}">
                @csrf
                @include('catalogues.causes.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>
