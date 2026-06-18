<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Connexion - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/pages/login.css', 'resources/js/app.js'])

    <style>
        :root {
            --ceet-login-bg: #f8f9fb;
            --ceet-login-surface: #ffffff;
            --ceet-login-border: #cfd5dd;
            --ceet-login-text: #111827;
            --ceet-login-muted: #59677a;
            --ceet-login-ink: #050506;
        }

        html,
        body {
            min-height: 100%;
        }

        body.ceet-login-page {
            min-height: 100vh !important;
            display: grid !important;
            place-items: center !important;
            padding: 24px !important;
            margin: 0 !important;
            overflow-x: hidden !important;
            background: var(--ceet-login-bg) !important;
            color: var(--ceet-login-text) !important;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        }

        .ceet-login-main {
            width: min(100%, 448px) !important;
        }

        .ceet-login-brand {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            margin-bottom: 18px !important;
            text-align: center !important;
        }

        .ceet-login-logo {
            width: 72px !important;
            height: 72px !important;
            display: grid !important;
            place-items: center !important;
            margin-bottom: 16px !important;
            border: 1px solid var(--ceet-login-border) !important;
            border-radius: 8px !important;
            background: var(--ceet-login-surface) !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .08) !important;
        }

        .ceet-login-logo img {
            width: 56px !important;
            height: 56px !important;
            object-fit: contain !important;
        }

        .ceet-login-brand h1 {
            margin: 0 0 4px !important;
            color: var(--ceet-login-ink) !important;
            font-size: 28px !important;
            font-weight: 900 !important;
            line-height: 1.1 !important;
        }

        .ceet-login-brand p {
            margin: 0 !important;
            color: var(--ceet-login-muted) !important;
            font-size: 14px !important;
        }

        .ceet-login-panel {
            padding: 38px !important;
            border: 1px solid var(--ceet-login-border) !important;
            border-radius: 8px !important;
            background: var(--ceet-login-surface) !important;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .10) !important;
        }

        .ceet-login-heading {
            margin-bottom: 28px !important;
        }

        .ceet-login-heading h2 {
            margin: 0 !important;
            color: var(--ceet-login-ink) !important;
            font-size: 22px !important;
            font-weight: 900 !important;
            line-height: 1.2 !important;
        }

        .ceet-login-heading span {
            display: block !important;
            width: 34px !important;
            height: 4px !important;
            margin-top: 10px !important;
            background: var(--ceet-login-ink) !important;
        }

        .ceet-login-form {
            display: grid !important;
            gap: 20px !important;
        }

        .ceet-field {
            display: grid !important;
            gap: 8px !important;
        }

        .ceet-field label {
            color: var(--ceet-login-muted) !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            letter-spacing: .07em !important;
            text-transform: uppercase !important;
        }

        .ceet-input-wrap {
            position: relative !important;
        }

        .ceet-input-wrap .material-symbols-outlined {
            position: absolute !important;
            top: 50% !important;
            left: 14px !important;
            color: #8a96a8 !important;
            transform: translateY(-50%) !important;
        }

        .ceet-input-wrap input {
            width: 100% !important;
            min-height: 50px !important;
            padding: 12px 15px 12px 44px !important;
            border: 1px solid var(--ceet-login-border) !important;
            border-radius: 7px !important;
            background: #fff !important;
            color: var(--ceet-login-text) !important;
            box-shadow: none !important;
            font: inherit !important;
            font-size: 15px !important;
        }

        .ceet-input-wrap input:focus {
            outline: none !important;
            border-color: #111827 !important;
            box-shadow: 0 0 0 3px rgba(17, 24, 39, .10) !important;
        }

        .ceet-login-submit {
            width: 100% !important;
            min-height: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            margin-top: 4px !important;
            border: 1px solid #000 !important;
            border-radius: 7px !important;
            background: #000 !important;
            color: #fff !important;
            font: inherit !important;
            font-size: 15px !important;
            font-weight: 900 !important;
            cursor: pointer !important;
        }

        .ceet-login-secondary {
            display: flex !important;
            justify-content: center !important;
            padding-top: 4px !important;
        }

        .ceet-login-secondary a {
            color: #4b5563 !important;
            font-size: 13px !important;
            text-decoration: underline !important;
            text-underline-offset: 4px !important;
        }

        .ceet-login-footer {
            margin-top: 26px !important;
            text-align: center !important;
        }

        .ceet-login-meta {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 14px !important;
            margin-bottom: 12px !important;
        }

        .ceet-login-meta span {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            color: #667085 !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            letter-spacing: .06em !important;
            text-transform: uppercase !important;
        }

        .ceet-login-meta i {
            width: 4px !important;
            height: 4px !important;
            border-radius: 999px !important;
            background: #cfd5dd !important;
        }

        .ceet-login-footer p {
            margin: 0 !important;
            color: #667085 !important;
            font-size: 13px !important;
        }

        @media (max-width: 575.98px) {
            body.ceet-login-page {
                padding: 16px !important;
            }

            .ceet-login-panel {
                padding: 28px 22px !important;
            }

            .ceet-login-meta {
                flex-wrap: wrap !important;
            }
        }
    </style>
</head>

<body class="ceet-login-page">
    <main class="ceet-login-main" aria-labelledby="login-title">
        <section class="ceet-login-brand" aria-label="CEET Incidents">
            <div class="ceet-login-logo">
                <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET">
            </div>

            <h1>CEET Incidents</h1>
            <p>Electrical Management System</p>
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
            <p>© 2024 Compagnie Énergie Électrique du Togo. Tous droits réservés.</p>
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
