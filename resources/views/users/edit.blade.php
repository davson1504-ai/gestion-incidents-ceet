<x-app-layout>
    <div class="ceet-page ceet-page-shell ceet-user-form-page">
        <header class="ceet-page-header">
            <div>
                <span class="ceet-page-kicker">Utilisateurs</span>
                <h1 class="ceet-page-title">Modifier utilisateur</h1>
                <p class="ceet-page-subtitle">Mettre à jour les informations, le rôle et l’état du compte.</p>
            </div>

            <div class="ceet-page-actions">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </header>

        <section class="ceet-card">
            <form method="POST" action="{{ route('users.update', $userToEdit) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['userToEdit' => $userToEdit, 'selectedRole' => $selectedRole])
            </form>
        </section>
    </div>
</x-app-layout>
