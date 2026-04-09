<x-guest-layout>
    <h1 class="h4 mb-1">Connexion</h1>
    <p class="text-muted mb-4">Accedez a la plateforme de gestion des incidents CEET.</p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="form-check mb-3">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Se souvenir de moi</label>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            @if (Route::has('password.request'))
                <a class="small" href="{{ route('password.request') }}">Mot de passe oublie ?</a>
            @endif

            <x-primary-button>
                {{ __('Se connecter') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
