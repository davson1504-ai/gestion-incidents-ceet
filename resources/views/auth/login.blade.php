<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion | CEET - Gestion des Incidents</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ========================================
           CSS VARIABLES & BASE
           ======================================== */
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
            --ceet-blue-night: #0f172a;
            --ceet-blue-deep: #1e293b;
            --ceet-blue-darker: #1a1f35;
            --ceet-gold: #f59e0b;
            --ceet-white: #ffffff;
            --ceet-gray-light: #f8fafc;
            --ceet-text-dark: #0f172a;
            --ceet-text-muted: #64748b;
            --ceet-border-light: #e2e8f0;
            --ceet-shadow-sm: 0 25px 50px rgba(15,23,42,0.12), 0 0 0 1px rgba(15,23,42,0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            overflow-x: hidden;
            background: var(--ceet-gray-light);
        }

        /* ========================================
           ANIMATIONS GLOBALES
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes rotate-slow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            20% {
                transform: translateX(-6px);
            }
            40% {
                transform: translateX(6px);
            }
            60% {
                transform: translateX(-4px);
            }
            80% {
                transform: translateX(4px);
            }
        }

        /* ========================================
           LAYOUT PRINCIPAL
           ======================================== */
        .login-page {
            display: flex;
            align-items: stretch;
            width: 100%;
            min-height: 100dvh;
            gap: 0;
        }

        /* ========================================
           PANNEAU GAUCHE - Identité CEET
           ======================================== */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--ceet-blue-night) 0%, var(--ceet-blue-deep) 40%, var(--ceet-blue-darker) 70%, var(--ceet-blue-night) 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Éléments décoratifs circulaires */
        .left-panel::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border: 1px solid rgba(239, 36, 51, 0.15);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: rotate-slow 20s linear infinite;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            border: 1px solid rgba(245, 158, 11, 0.1);
            border-radius: 50%;
            bottom: 50px;
            left: -80px;
        }

        .left-panel .deco-circle {
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(239, 36, 51, 0.05);
            border-radius: 50%;
            top: 40%;
            right: 20px;
        }

        /* Contenu du panneau gauche */
        .left-panel-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
        }

        /* Logo et nom CEET */
        .ceet-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        .ceet-logo-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(245, 158, 11, 0.2));
            border: 2px solid rgba(245, 158, 11, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(239, 36, 51, 0.2);
        }

        .ceet-logo-large img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .ceet-name {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--ceet-white);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Texte accroche */
        .left-tagline {
            animation: fadeInUp 0.6s ease 0.3s both;
        }

        .left-tagline h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ceet-white);
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .left-tagline p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.6;
        }

        /* Stats animées */
        .stats-container {
            display: flex;
            gap: 16px;
            width: 100%;
            max-width: 400px;
        }

        .stat-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 16px 12px;
            text-align: center;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.6s ease both;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.4s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.5s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.6s;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--ceet-white);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========================================
           PANNEAU DROIT - Formulaire
           ======================================== */
        .right-panel {
            flex: 1;
            background: var(--ceet-gray-light);
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .right-panel-content {
            width: 100%;
            max-width: 440px;
        }

        /* Card de login */
        .login-card {
            background: var(--ceet-white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--ceet-shadow-sm);
            animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Header de la card */
        .card-header {
            margin-bottom: 28px;
        }

        .ceet-logo-mobile {
            display: none;
            width: fit-content;
            margin: 0 auto 20px;
            padding: 8px 16px;
            background: rgba(239, 36, 51, 0.08);
            border-radius: 50px;
            border: 1px solid rgba(239, 36, 51, 0.2);
        }

        .ceet-logo-mobile img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .card-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ceet-text-dark);
            margin-bottom: 8px;
        }

        .card-header p {
            color: var(--ceet-text-muted);
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .header-divider {
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--ceet-red), var(--ceet-gold));
            border-radius: 2px;
        }

        /* Messages d'erreur */
        .error-banner {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 4px solid var(--ceet-red);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #be123c;
            margin-bottom: 20px;
            animation: shake 0.4s ease;
        }

        .error-icon {
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Champs de formulaire */
        .field-group {
            margin-bottom: 20px;
        }

        .field-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ceet-text-dark);
            margin-bottom: 8px;
        }

        .field-wrapper {
            position: relative;
            background: var(--ceet-gray-light);
            border: 1.5px solid var(--ceet-border-light);
            border-radius: 12px;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .field-wrapper:focus-within {
            border-color: var(--ceet-red);
            box-shadow: 0 0 0 4px rgba(239, 36, 51, 0.08);
            background: var(--ceet-white);
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            padding: 0 4px;
            flex-shrink: 0;
        }

        .field-wrapper:focus-within .field-icon {
            color: var(--ceet-red);
        }

        .field-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 14px 44px 14px 44px;
            font-size: 0.95rem;
            outline: none;
            color: var(--ceet-text-dark);
        }

        .field-input::placeholder {
            color: #cbd5e1;
        }

        .field-focus-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 0%;
            background: linear-gradient(90deg, var(--ceet-red), var(--ceet-gold));
            transition: width 0.3s ease;
        }

        .field-wrapper:focus-within .field-focus-bar {
            width: 100%;
        }

        /* SVG Icons inline */
        .svg-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }

        /* Toggle Password Button */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .toggle-password:hover {
            color: var(--ceet-red);
        }

        .eye-icon, .eye-off-icon {
            width: 18px;
            height: 18px;
        }

        /* Form extras - Remember + Forgot */
        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 12px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 1.5px solid var(--ceet-border-light);
            border-radius: 4px;
            background: var(--ceet-white);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .remember-checkbox:checked {
            background: var(--ceet-red);
            border-color: var(--ceet-red);
        }

        .remember-checkbox:checked::after {
            content: '✓';
            color: var(--ceet-white);
            font-size: 11px;
            font-weight: bold;
        }

        .remember-text {
            font-size: 0.9rem;
            color: var(--ceet-text-dark);
        }

        .forgot-link {
            font-size: 0.9rem;
            color: var(--ceet-red);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--ceet-red-dark);
            text-decoration: underline;
        }

        /* Bouton Submit */
        .submit-btn {
            width: 100%;
            padding: 15px 24px;
            background: linear-gradient(135deg, var(--ceet-red) 0%, var(--ceet-red-dark) 100%);
            color: var(--ceet-white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 36, 51, 0.35);
        }

        .submit-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.85;
            cursor: not-allowed;
        }

        .btn-text {
            display: inline;
        }

        .btn-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--ceet-white);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        .btn-icon {
            display: inline;
            font-size: 1.2rem;
            transition: transform 0.2s;
        }

        .submit-btn:hover:not(:disabled) .btn-icon {
            transform: translateX(4px);
        }

        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-effect 0.6s linear;
            pointer-events: none;
        }

        /* Card footer */
        .card-footer {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            text-align: center;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .footer-text {
            display: block;
            margin-bottom: 8px;
        }

        .footer-links {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            font-size: 0.75rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--ceet-red);
        }

        /* ========================================
           RESPONSIVE DESIGN
           ======================================== */
        @media (max-width: 1024px) {
            .left-panel {
                flex: 0.8;
                padding: 30px;
            }

            .right-panel {
                flex: 1.2;
                padding: 30px;
            }

            .login-card {
                padding: 32px;
            }

            .card-header h1 {
                font-size: 1.75rem;
            }

            .left-panel-content {
                gap: 24px;
            }

            .left-tagline h2 {
                font-size: 1.75rem;
            }

            .left-tagline p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .login-page {
                flex-direction: column;
                min-height: auto;
            }

            .left-panel {
                min-height: 200px;
                flex: none;
                padding: 24px;
                justify-content: flex-start;
                padding-top: 40px;
            }

            .left-panel::before {
                width: 300px;
                height: 300px;
                top: -80px;
                right: -80px;
            }

            .right-panel {
                flex: 1;
                min-height: 100dvh;
                padding: 24px;
            }

            .left-panel-content {
                gap: 16px;
                max-width: 100%;
            }

            .energy-network {
                width: 150px !important;
                height: 150px !important;
                animation: fadeInUp 0.6s ease 0.2s both;
            }

            .left-tagline h2 {
                font-size: 1.3rem;
            }

            .left-tagline p {
                font-size: 0.8rem;
            }

            .stats-container {
                max-width: 100%;
            }

            .stat-card {
                font-size: 0.85rem;
            }

            .stat-value {
                font-size: 1.2rem;
            }

            .ceet-logo-mobile {
                display: flex;
            }

            .login-card {
                padding: 24px;
                border-radius: 16px;
                animation: fadeIn 0.6s ease forwards;
            }

            .card-header h1 {
                font-size: 1.5rem;
            }

            .card-header p {
                font-size: 0.85rem;
            }

            .field-input {
                padding: 12px 40px 12px 40px;
            }

            .submit-btn {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .left-panel {
                min-height: 150px;
                padding: 16px;
                padding-top: 24px;
            }

            .left-panel::before {
                display: none;
            }

            .left-panel::after {
                display: none;
            }

            .left-panel .deco-circle {
                display: none;
            }

            .ceet-header {
                gap: 8px;
            }

            .ceet-name {
                font-size: 1.8rem;
                letter-spacing: 1px;
            }

            .energy-network {
                width: 120px !important;
                height: 120px !important;
            }

            .left-tagline h2 {
                font-size: 1.1rem;
            }

            .left-tagline p {
                font-size: 0.75rem;
            }

            .stats-container {
                flex-wrap: wrap;
            }

            .right-panel {
                padding: 16px;
            }

            .login-card {
                padding: 20px;
            }

            .card-header h1 {
                font-size: 1.3rem;
            }

            .form-extras {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .forgot-link {
                align-self: flex-end;
            }
        }

        /* ========================================
           UTILITIES
           ======================================== */
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <!-- ========================================
             PANNEAU GAUCHE : Identité CEET
             ======================================== -->
        <div class="left-panel">
            <div class="deco-circle"></div>

            <div class="left-panel-content">
                <!-- Header with logo and name -->
                <div class="ceet-header">
                    <div class="ceet-logo-large">
                        <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                    </div>
                    <div class="ceet-name">CEET</div>
                </div>

                <!-- Tagline -->
                <div class="left-tagline">
                    <h2>Supervisez votre réseau</h2>
                    <p>Surveillance et pilotage 24h/24 du réseau de distribution national</p>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-value">74</div>
                        <div class="stat-label">Départs surveillés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Supervision active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Niveaux d'accès</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================
             PANNEAU DROIT : Formulaire
             ======================================== -->
        <div class="right-panel">
            <div class="right-panel-content">
                <!-- Error messages -->
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="error-banner">
                            <span class="error-icon">⚠</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                @endif

                @if (session('status'))
                    <div class="error-banner" style="background: #f0fdf4; border-color: #86efac; border-left-color: #22c55e; color: #166534;">
                        <span class="error-icon">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Login Card -->
                <div class="login-card">
                    <div class="card-header">
                        <div class="ceet-logo-mobile">
                            <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                        </div>
                        <h1>Connexion</h1>
                        <p>Accès réservé au personnel autorisé CEET</p>
                        <div class="header-divider"></div>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

                        <!-- Email Field -->
                        <div class="field-group">
                            <label for="email" class="field-label">Email professionnel</label>
                            <div class="field-wrapper">
                                <span class="field-icon">
                                    <svg class="svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="2" y="4" width="20" height="16" rx="2" />
                                        <path d="m22 6-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 6" />
                                    </svg>
                                </span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="field-input"
                                    placeholder="votre.email@ceet.tg"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                >
                                <span class="field-focus-bar"></span>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="field-group">
                            <label for="password" class="field-label">Mot de passe</label>
                            <div class="field-wrapper">
                                <span class="field-icon">
                                    <svg class="svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="field-input"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="current-password"
                                >
                                <button type="button" id="togglePassword" class="toggle-password" aria-label="Afficher/Masquer le mot de passe">
                                    <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="display:none">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                                <span class="field-focus-bar"></span>
                            </div>
                        </div>

                        <!-- Remember & Forgot Password -->
                        <div class="form-extras">
                            <label class="remember-label">
                                <input type="checkbox" name="remember" class="remember-checkbox" {{ old('remember') ? 'checked' : '' }}>
                                <span class="remember-text">Rester connecté</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn" class="submit-btn">
                            <span class="btn-text">Se connecter</span>
                            <span class="btn-spinner"></span>
                            <span class="btn-icon">→</span>
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="card-footer">
                        <span class="footer-text">© 2026 CEET - Tous droits réservés</span>
                        <div class="footer-links">
                            <a href="#">Aide & Support</a>
                            <span>•</span>
                            <a href="#">Mentions légales</a>
                            <span>•</span>
                            <a href="#">Confidentialité</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
         JAVASCRIPT INLINE
         ======================================== -->
    <script>
        // ===== Ripple effect on button =====
        document.querySelector('.submit-btn')?.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        // ===== Spinner on form submit =====
        document.querySelector('.login-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            if (!btn) return;

            const btnText = btn.querySelector('.btn-text');
            const btnSpinner = btn.querySelector('.btn-spinner');
            const btnIcon = btn.querySelector('.btn-icon');

            btnText.textContent = 'Connexion...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'block';
            btn.disabled = true;
        });

        // ===== Toggle password visibility =====
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');

        if (passwordInput && toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();

                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;

                const eyeIcon = toggleBtn.querySelector('.eye-icon');
                const eyeOffIcon = toggleBtn.querySelector('.eye-off-icon');

                if (type === 'password') {
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                } else {
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                }
            });
        }

        // ===== Auto-focus first field =====
        window.addEventListener('load', () => {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });
    </script>
</body>
</html>
