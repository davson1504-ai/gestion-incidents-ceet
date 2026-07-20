<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vérification email - CEET Incidents</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="ceet-reset-page">
        <section class="ceet-reset-main" aria-labelledby="verify-email-title">
            <div class="ceet-reset-brand">
                <a href="{{ url('/') }}" class="ceet-reset-logo" aria-label="CEET Incidents">
                    <x-application-logo aria-hidden="true" />
                </a>
                <h1>CEET Incidents</h1>
                <p>Validation de compte</p>
            </div>

            <div class="ceet-reset-panel">
                <header class="ceet-reset-heading">
                    <h2 id="verify-email-title">Vérifiez votre adresse email</h2>
                    <p>Un lien de vérification a été envoyé à votre adresse email. Utilisez ce lien pour activer l'accès complet à la plateforme.</p>
                </header>

                @if (session('status') === 'verification-link-sent')
                    <div class="ceet-alert ceet-alert-success" role="status">
                        Un nouveau lien de vérification vient d'être envoyé.
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="ceet-reset-form">
                    @csrf
                    <button type="submit" class="ceet-reset-submit">
                        Renvoyer le lien de vérification
                    </button>
                </form>

                <div class="ceet-reset-back">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ceet-reset-submit" style="background:#ffffff;color:#191c1e;border-color:#c6c6cd;">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>

            <footer class="ceet-reset-footer">Email requis</footer>
        </section>
    </main>
</body>
</html>
