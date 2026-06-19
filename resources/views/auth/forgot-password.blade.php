<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Mot de passe oublié - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-reset-page">
    <main class="ceet-reset-main" aria-labelledby="reset-title">
        <section class="ceet-reset-brand" aria-label="CEET Incidents">
            <div class="ceet-reset-logo">
                <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET">
            </div>

            <h1>CEET Incidents</h1>
            <p>Electrical Management System</p>
        </section>

        <section class="ceet-reset-panel">
            <div class="ceet-reset-heading">
                <h2 id="reset-title">Réinitialiser le mot de passe</h2>
                <p>Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation sécurisé.</p>
            </div>

            @if ($errors->any())
                <div class="ceet-alert ceet-alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="ceet-alert ceet-alert-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="ceet-reset-form" id="forgot-password-form">
                @csrf

                <div class="ceet-field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="nom@entreprise.tg"
                        autocomplete="email"
                        autofocus
                        required
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                </div>

                <button type="submit" class="ceet-reset-submit" id="forgot-password-submit">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="ceet-reset-back">
                <a href="{{ route('login') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                    Retour à la connexion
                </a>
            </div>
        </section>

        <footer class="ceet-reset-footer">
            Sécurité infrastructure CEET © 2024
        </footer>
    </main>

    <script>
        const resetForm = document.getElementById('forgot-password-form');
        const resetBtn = document.getElementById('forgot-password-submit');

        if (resetForm && resetBtn) {
            resetForm.addEventListener('submit', () => {
                resetBtn.disabled = true;
                resetBtn.textContent = 'Traitement...';
            });
        }
    </script>
</body>
</html>
