@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirmation du mot de passe - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=hanken-grotesk:400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="ceet-reset-page">
        <section class="ceet-reset-main" aria-labelledby="confirm-password-title">
            <div class="ceet-reset-brand">
                <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="ceet-reset-logo" aria-label="CEET Incidents">
                    <x-application-logo aria-hidden="true" />
                </a>
                <h1>CEET Incidents</h1>
                <p>Contrôle de sécurité requis</p>
            </div>

            <div class="ceet-reset-panel">
                <header class="ceet-reset-heading">
                    <h2 id="confirm-password-title">Confirmer votre mot de passe</h2>
                    <p>Cette zone est protégée. Saisissez votre mot de passe pour continuer.</p>
                </header>

                @if($errors->any())
                    <div class="ceet-alert ceet-alert-danger" role="alert">
                        Mot de passe incorrect ou champ requis manquant.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.confirm') }}" class="ceet-reset-form">
                    @csrf

                    <label class="ceet-field" for="password">
                        <span>Mot de passe</span>
                        <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
                        @error('password')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <button type="submit" class="ceet-reset-submit">
                        Confirmer
                    </button>
                </form>

                <div class="ceet-reset-back">
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}">
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>

            <footer class="ceet-reset-footer">Accès sécurisé</footer>
        </section>
    </main>
</body>
</html>
