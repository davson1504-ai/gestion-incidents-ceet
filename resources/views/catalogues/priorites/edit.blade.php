<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Editer priorite {{ $priorite->code }}</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.priorites.update', $priorite) }}">
                @csrf
                @method('PUT')
                @include('catalogues.priorites.partials.form', ['priorite' => $priorite])
            </form>
        </div>
    </div>
</x-app-layout>

