<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Connexion - CEET Incidents</title>

    @vite(['resources/css/pages/login.css', 'resources/js/app.js'])
</head>

<body class="ceet-login-page">
    <main class="ceet-login-main" aria-labelledby="login-title">
        <section class="ceet-login-brand" aria-label="CEET Incidents">
            <div class="ceet-login-logo">
                <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET">
            </div>

            <h1>CEET Incidents</h1>
            <p>Gestion des incidents électriques</p>
        </section>

        <section class="ceet-login-panel">
            <div class="ceet-login-heading">
                <h2 id="login-title">Connexion</h2>
                <span aria-hidden="true"></span>
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

            <form action="{{ route('login') }}" method="POST" class="ceet-login-form" id="login-form">
                @csrf

                <div class="ceet-field">
                    <label for="email">Email</label>
                    <div class="ceet-input-wrap">
                        <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="votre.nom@ceet.tg"
                            autocomplete="email"
                            autofocus
                            required
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        >
                    </div>
                </div>

                <div class="ceet-field">
                    <label for="password">Mot de passe</label>
                    <div class="ceet-input-wrap">
                        <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        >
                    </div>
                </div>

                <button type="submit" class="ceet-login-submit" id="login-submit">
                    <span>Se connecter</span>
                    <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                </button>

                @if (Route::has('password.request'))
                    <div class="ceet-login-secondary">
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    </div>
                @endif
            </form>
        </section>

        <footer class="ceet-login-footer">
            <div class="ceet-login-meta">
                <span>
                    <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                    Accès sécurisé
                </span>
                <i aria-hidden="true"></i>
                <span>
                    <span class="material-symbols-outlined" aria-hidden="true">language</span>
                    Version 2.4.0
                </span>
            </div>
            <p>© 2026 Compagnie Énergie Électrique du Togo. Tous droits réservés.</p>
        </footer>
    </main>

    <div class="ceet-login-bg" aria-hidden="true">
        <span></span>
        <span></span>
    </div>

    <script>
        const loginForm = document.getElementById('login-form');
        const loginBtn = document.getElementById('login-submit');

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', () => {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="material-symbols-outlined ceet-spin" aria-hidden="true">progress_activity</span><span>Authentification...</span>';
            });
        }
    </script>
</body>
</html>
