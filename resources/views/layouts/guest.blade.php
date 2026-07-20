<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#fcf8fa">

        <title>{{ config('app.name', 'Gestion Incidents CEET') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="ceet-auth-page">
        <main class="ceet-auth-shell">
            <section class="ceet-auth-card">
                <div class="ceet-auth-card-body">
                    <div class="ceet-auth-logo">
                        <a href="/" aria-label="Accueil CEET">
                            <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET">
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </section>
        </main>
    </body>
</html>
