<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Nouvel utilisateur</h1>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form')
            </form>
        </div>
    </div>
</x-app-layout>

