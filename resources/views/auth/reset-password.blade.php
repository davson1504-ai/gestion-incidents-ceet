<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Nouveau mot de passe - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-reset-page">
    <main class="ceet-reset-main" aria-labelledby="reset-password-title">
        <section class="ceet-reset-brand" aria-label="CEET Incidents">
            <div class="ceet-reset-logo">
                <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET">
            </div>

            <h1>CEET Incidents</h1>
            <p>Electrical Management System</p>
        </section>

        <section class="ceet-reset-panel">
            <div class="ceet-reset-heading">
                <h2 id="reset-password-title">Créer un nouveau mot de passe</h2>
                <p>Choisissez un mot de passe sécurisé pour récupérer votre accès.</p>
            </div>

            @if ($errors->any())
                <div class="ceet-alert ceet-alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.store') }}" method="POST" class="ceet-reset-form">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="ceet-field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $request->email) }}"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="ceet-field">
                    <label for="password">Nouveau mot de passe</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="ceet-field">
                    <label for="password_confirmation">Confirmation</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="ceet-reset-submit">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <div class="ceet-reset-back">
                <a href="{{ route('login') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                    Retour à la connexion
                </a>
            </div>
        </section>
    </main>
</body>
</html>
