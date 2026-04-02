<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Éditer cause {{ $cause->code }}</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.causes.update', $cause) }}">
                @csrf
                @method('PUT')
                @include('catalogues.causes.partials.form', ['cause' => $cause])
            </form>
        </div>
    </div>
</x-app-layout>
