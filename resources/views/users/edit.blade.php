<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Modifier utilisateur</h1>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $userToEdit) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['userToEdit' => $userToEdit, 'selectedRole' => $selectedRole])
            </form>
        </div>
    </div>
</x-app-layout>

