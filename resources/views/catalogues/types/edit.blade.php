<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Éditer type {{ $type->code }}</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.types.update', $type) }}">
                @csrf
                @method('PUT')
                @include('catalogues.types.partials.form', ['type' => $type])
            </form>
        </div>
    </div>
</x-app-layout>
