<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h4 mb-0">Creation d'un incident</h1>
            <p class="text-muted mb-0">Saisir les informations de declaration et de suivi</p>
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

            <form method="POST" action="{{ route('incidents.store') }}">
                @csrf
                @include('incidents._form')
            </form>
        </div>
    </div>
</x-app-layout>
