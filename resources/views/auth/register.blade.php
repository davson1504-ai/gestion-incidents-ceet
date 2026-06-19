@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer un compte - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=hanken-grotesk:400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="ceet-reset-page">
        <section class="ceet-reset-main" aria-labelledby="register-title">
            <div class="ceet-reset-brand">
                <a href="{{ url('/') }}" class="ceet-reset-logo" aria-label="CEET Incidents">
                    <x-application-logo aria-hidden="true" />
                </a>
                <h1>CEET Incidents</h1>
                <p>Création d'un compte utilisateur</p>
            </div>

            <div class="ceet-reset-panel">
                <header class="ceet-reset-heading">
                    <h2 id="register-title">Créer un compte</h2>
                    <p>Renseignez les informations d'accès. La disponibilité de cette page dépend de la configuration d'inscription publique.</p>
                </header>

                @if($errors->any())
                    <div class="ceet-alert ceet-alert-danger" role="alert">
                        Veuillez corriger les champs signalés avant de continuer.
                    </div>
                @endif

                <form method="POST" action="{{ Route::has('register') ? route('register') : '#' }}" class="ceet-reset-form">
                    @csrf

                    <label class="ceet-field" for="name">
                        <span>Nom complet</span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                        @error('name')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="ceet-field" for="email">
                        <span>Adresse email</span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                        @error('email')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="ceet-field" for="password">
                        <span>Mot de passe</span>
                        <input id="password" type="password" name="password" required autocomplete="new-password">
                        @error('password')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="ceet-field" for="password_confirmation">
                        <span>Confirmation du mot de passe</span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                        @error('password_confirmation')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <button type="submit" class="ceet-reset-submit">
                        Créer le compte
                    </button>
                </form>

                <div class="ceet-reset-back">
                    <a href="{{ Route::has('login') ? route('login') : url('/') }}">
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                        Retour à la connexion
                    </a>
                </div>
            </div>

            <footer class="ceet-reset-footer">Compte CEET</footer>
        </section>
    </main>
</body>
</html>
