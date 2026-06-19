<section class="ceet-card border-danger-subtle">
    <header class="mb-4">
        <h2 class="h5 fw-bold text-danger mb-1">Supprimer le compte</h2>
        <p class="text-muted mb-0">
            Cette action est définitive. Confirmez avec votre mot de passe avant suppression.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Supprimer définitivement ce compte ?');">
        @csrf
        @method('DELETE')

        <div class="mb-4">
            <x-input-label for="delete_password" value="Mot de passe" />
            <x-text-input id="delete_password" name="password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->userDeletion->get('password')" />
        </div>

        <x-danger-button>Supprimer le compte</x-danger-button>
    </form>
</section>
