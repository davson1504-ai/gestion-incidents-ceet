<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Déclarer un incident</h1>
            <p class="text-muted mb-0">Saisissez les informations de déclaration et d’affectation pour ouvrir un nouvel incident.</p>
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

            <form method="POST" action="{{ route('incidents.store') }}" data-incident-form>
                @csrf
                @include('incidents._form')
            </form>
        </div>
    </div>
</x-app-layout>
