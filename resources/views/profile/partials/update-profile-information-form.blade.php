@php
    $user = $user ?? auth()->user();
@endphp

<section class="ceet-card">
    <header class="mb-4">
        <h2 class="h5 fw-bold mb-1">Informations du profil</h2>
        <p class="text-muted mb-0">Mettez à jour les informations de votre compte CEET.</p>
    </header>

    <form method="POST" action="{{ route('profile.update') }}" class="ceet-form-grid">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <x-input-label for="name" value="Nom complet" />
            <x-text-input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-4">
            <x-input-label for="telephone" value="Téléphone" />
            <x-text-input id="telephone" name="telephone" type="tel" value="{{ old('telephone', $user?->telephone) }}" autocomplete="tel" />
            <x-input-error :messages="$errors->get('telephone')" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>Enregistrer</x-primary-button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-semibold">Profil mis à jour.</span>
            @endif
        </div>
    </form>
</section>
