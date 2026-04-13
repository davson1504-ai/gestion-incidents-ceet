<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Mettre à jour {{ $incident->code_incident }}</h1>
            <p class="text-muted mb-0">Actualisez le statut, les intervenants et la chronologie de l’incident sans perdre la traçabilité existante.</p>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-2">Le formulaire contient des erreurs.</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('incidents.update', $incident) }}" data-incident-form>
                @csrf
                @method('PUT')
                @include('incidents._form', ['incident' => $incident])
            </form>
        </div>
    </div>
</x-app-layout>
