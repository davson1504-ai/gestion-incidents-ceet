<section class="ceet-card">
    <header class="mb-4">
        <h2 class="h5 fw-bold mb-1">Mot de passe</h2>
        <p class="text-muted mb-0">Utilisez un mot de passe long et unique pour protéger votre accès.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="ceet-form-grid">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <x-input-label for="current_password" value="Mot de passe actuel" />
            <x-text-input id="current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" value="Confirmation" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>Mettre à jour</x-primary-button>

            @if (session('status') === 'password-updated')
                <span class="text-success small fw-semibold">Mot de passe modifié.</span>
            @endif
        </div>
    </form>
</section>
