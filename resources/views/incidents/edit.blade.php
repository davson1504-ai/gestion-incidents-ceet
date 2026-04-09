<x-app-layout>
    <x-slot name="header">
        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-0">Edition incident {{ $incident->code_incident }}</h1>
                <p class="text-muted mb-0">Mettre a jour les informations et le statut de resolution</p>
            </div>
            <span class="badge" style="background-color: {{ $incident->statut?->couleur ?? '#6c757d' }}">
                {{ $incident->statut?->libelle ?? 'N/A' }}
            </span>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Le formulaire contient des erreurs.</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('incidents.update', $incident) }}">
                @csrf
                @method('PUT')
                @include('incidents._form', ['incident' => $incident])
            </form>
        </div>
    </div>
</x-app-layout>
