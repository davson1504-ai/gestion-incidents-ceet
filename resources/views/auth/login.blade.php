<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion | CEET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --ceet-red: #ef2433;
            --ceet-yellow: #f8d90a;
            --ceet-orange: #f78f1e;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            overflow-x: hidden;
            background: #fff;
        }

        .login-wrapper {
            width: 100%;
            min-height: 100dvh;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: stretch;
            justify-content: stretch;
        }

        .login-shell {
            width: 100%;
            min-height: 100dvh;
            max-width: none;
            background: #fff;
            border: 0;
            box-shadow: none;
            border-radius: 0;
            overflow: hidden;
        }

        .login-shell.container-fluid {
            margin: 0;
            padding: 0;
        }

        .login-shell > .row {
            min-height: 100dvh;
            margin: 0;
        }

        .left-pane {
            background: linear-gradient(180deg, var(--ceet-yellow) 0%, #ffba1a 52%, var(--ceet-orange) 100%);
            color: #4c4c4c;
            padding: 2.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
        }

        .left-inner {
            width: 100%;
            max-width: 460px;
            text-align: center;
        }

        .logo-circle {
            width: 94px;
            height: 94px;
            border-radius: 50%;
            margin: 0 auto 1.2rem;
            background: radial-gradient(circle at 30% 30%, #ffd34f 0%, #f7b700 75%);
            box-shadow: inset 0 0 0 12px rgba(243, 149, 12, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-circle img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 50%;
            background: #ffd756;
            padding: 0.25rem;
        }

        .left-title {
            color: #ef3f2f;
            font-weight: 800;
            margin-bottom: 0.9rem;
            font-size: clamp(1.8rem, 2.7vw, 2.55rem);
        }

        .left-text {
            color: #6e5b35;
            line-height: 1.62;
            font-size: 1.12rem;
            margin: 0 auto 1.6rem;
            max-width: 430px;
        }

        .mini-stat {
            background: rgba(255, 249, 236, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 0.72rem;
            padding: 0.7rem 0.45rem;
            box-shadow: 0 6px 14px rgba(145, 80, 10, 0.12);
            text-align: center;
        }

        .mini-stat .value {
            color: #ef3f2f;
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
        }

        .mini-stat .label {
            color: #8d8d8d;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-size: 0.72rem;
            font-weight: 700;
            margin-top: 0.2rem;
        }

        .right-pane {
            padding: 2rem 1.6rem 1.5rem;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
        }

        .right-inner {
            width: 100%;
            max-width: 430px;
            text-align: center;
        }

        .ceet-brand-pill {
            width: fit-content;
            margin: 0 auto 1.1rem;
            border: 2px solid #f2d34f;
            border-radius: 999px;
            background: #fffdf6;
            padding: 0.42rem 1.05rem 0.42rem 0.58rem;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }

        .ceet-brand-pill img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #ffe178 0%, #ffc234 100%);
            box-shadow: inset 0 0 0 4px rgba(243, 149, 12, 0.18);
            padding: 0.18rem;
        }

        .ceet-brand-pill span {
            color: #de2434;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.01em;
        }

        .operator-title {
            font-size: 2.05rem;
            font-weight: 800;
            color: #1b1b1b;
            margin-bottom: 0.28rem;
        }

        .operator-sub {
            color: #888;
            margin-bottom: 1.45rem;
            font-size: 1.04rem;
        }

        .auth-card {
            text-align: left;
            border: 1px solid #efefef;
            border-radius: 0.95rem;
            box-shadow: 0 10px 20px rgba(25, 25, 25, 0.05);
        }

        .auth-card .card-body {
            padding: 1.25rem;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.15rem;
            color: #222;
        }

        .auth-sub {
            color: #848484;
            font-size: 0.92rem;
            margin-bottom: 0.95rem;
        }

        .field-label {
            font-size: 0.94rem;
            font-weight: 700;
            color: #353535;
        }

        .field-link {
            font-size: 0.84rem;
            color: #d34455;
            text-decoration: none;
            font-weight: 600;
        }

        .field-link:hover {
            text-decoration: underline;
            color: #bc1f31;
        }

        .form-control {
            border: 1px solid #e3e3e3;
            border-radius: 0.58rem;
            min-height: 46px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #f59a2c;
            box-shadow: 0 0 0 0.17rem rgba(245, 154, 44, 0.18);
        }

        .form-check-input:checked {
            background-color: var(--ceet-red);
            border-color: var(--ceet-red);
        }

        .btn-login {
            border: 0;
            border-radius: 0.58rem;
            background: linear-gradient(90deg, var(--ceet-red) 0%, #ff2637 100%);
            color: #fff;
            font-weight: 700;
            min-height: 46px;
            transition: transform 0.14s ease, box-shadow 0.14s ease, filter 0.14s ease;
        }

        .btn-login:hover {
            color: #fff;
            filter: brightness(0.98);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(220, 36, 52, 0.24);
        }

        .secure-note {
            color: #8f8f8f;
            text-align: center;
            font-size: 0.83rem;
            margin-top: 0.7rem;
        }

        .right-footer {
            margin-top: 1.2rem;
            color: #888;
            font-size: 0.8rem;
        }

        .right-footer .version {
            text-align: right;
        }

        .legal {
            color: #9b9b9b;
            font-size: 0.74rem;
            margin-top: 1.3rem;
            text-align: center;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .login-shell {
                min-height: unset;
            }

            .login-shell > .row,
            .left-pane,
            .right-pane {
                min-height: auto;
            }

            .left-pane {
                padding: 2rem 1.25rem;
            }

            .left-text {
                font-size: 1rem;
            }

            .right-pane {
                padding: 1.5rem 1rem 1.25rem;
            }

            .operator-title {
                font-size: 1.75rem;
            }

            .auth-title {
                font-size: 1.7rem;
            }

            .logo-circle {
                width: 84px;
                height: 84px;
            }

            .logo-circle img {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <main class="login-wrapper">
        <section class="login-shell container-fluid">
            <div class="row g-0 h-100">
                <div class="col-lg-6 left-pane">
                    <div class="left-inner">
                        <div class="logo-circle">
                            <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                        </div>

                        <h1 class="left-title">Gestion des Incidents Réseau</h1>
                        <p class="left-text">
                            Bienvenue sur le portail sécurisé de la CEET. Gérez efficacement les incidents, optimisez la maintenance et assurez la continuité du service électrique sur l'ensemble du territoire.
                        </p>

                        <div class="row g-2 justify-content-center">
                            <div class="col-6 col-sm-5">
                                <div class="mini-stat">
                                    <div class="value">74</div>
                                    <div class="label">Départs suivis</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-5">
                                <div class="mini-stat">
                                    <div class="value">24/7</div>
                                    <div class="label">Surveillance</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 right-pane">
                    <div class="right-inner">
                        <div class="ceet-brand-pill" aria-label="Marque CEET">
                            <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                            <span>CEET</span>
                        </div>

                        <h2 class="operator-title">Accès Opérateur</h2>
                        <p class="operator-sub">Saisissez vos identifiants pour accéder au système</p>

                        <div class="card auth-card">
                            <div class="card-body">
                                <h3 class="auth-title">Connexion</h3>
                                <p class="auth-sub">Utilisez votre compte professionnel CEET</p>

                                @if (session('status'))
                                    <div class="alert alert-success py-2" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" novalidate>
                                    @csrf

                                    <div class="mb-3">
                                        <label for="email" class="field-label mb-1">Utilisateur ou Email</label>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="nom.prenom@ceet.tg"
                                        >
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label for="password" class="field-label mb-0">Mot de passe</label>
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="field-link">Mot de passe oublié ?</a>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autocomplete="current-password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="••••••••"
                                        >
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-3">
                                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                        <label class="form-check-label" for="remember_me">Rester connecté</label>
                                    </div>

                                    <button type="submit" class="btn btn-login w-100">
                                        Se connecter
                                    </button>
                                </form>

                                <div class="secure-note">◌ Accès restreint au personnel autorisé</div>
                            </div>
                        </div>

                        <div class="row right-footer">
                            <div class="col text-start">◌ Aide & Support</div>
                            <div class="col version">v2.4.0-STABLE</div>
                        </div>

                        <div class="legal">
                            © 2026 Compagnie Énergie Électrique du Togo. Tous droits réservés.<br>
                            MENTIONS LÉGALES · CONFIDENTIALITÉ
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
