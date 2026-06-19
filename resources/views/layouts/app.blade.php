<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f8fb">

    <title>@yield('title', config('app.name', 'Gestion Incidents CEET'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=hanken-grotesk:400,500,600,700,800|jetbrains-mono:500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('page_css')
    @stack('styles')
</head>

<body class="ceet-app-body" data-ceet-app>
    <a href="#ceet-app-content" class="ceet-skip-link">Aller au contenu principal</a>

    <div class="ceet-app-shell">
        <x-app-sidebar />

        <div class="ceet-app-main">
            <x-app-topbar />

            <main id="ceet-app-content" class="ceet-app-content" tabindex="-1">
                <x-flash />

                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>

    <x-confirm-modal />
    <x-ceet-toasts />

    @yield('page_js')
    @stack('scripts')
</body>
</html>
