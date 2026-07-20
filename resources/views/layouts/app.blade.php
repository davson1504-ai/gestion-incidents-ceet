{{--
    CEET Incidents — Layout principal de l'application
    Structure : sidebar fixe + topbar fixe + zone centrale dynamique
    Toutes les pages authentifiées étendent ce layout.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">

    <title>@yield('title', 'CEET Incidents') — {{ config('app.name', 'CEET Incidents') }}</title>

    {{-- CSS global (shell + composants) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- CSS spécifique à la page --}}
    @yield('page_css')
</head>

<body class="ceet-body {{ request()->is('catalogues*') ? 'ceet-route-catalogues' : '' }}">

    <div class="ceet-app-wrapper">

        {{-- Overlay mobile --}}
        <div class="ceet-app-overlay" id="ceet-sidebar-overlay" aria-hidden="true"></div>

        {{-- Sidebar commune --}}
        <x-app-sidebar />

        {{-- Zone principale (topbar + contenu) --}}
        <div class="ceet-app-main">

            {{-- Topbar commune --}}
            <x-app-topbar />

            {{-- Contenu central --}}
            <main class="ceet-app-content" id="ceet-main-content">
                <div class="ceet-app-content-inner">

                    {{-- Messages flash --}}
                    <div class="ceet-flash-zone">
                        <x-flash />
                    </div>

                    {{-- Contenu de la page --}}
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset

                </div>
            </main>

        </div>

    </div>

    {{-- JS spécifique à la page --}}
    @yield('page_js')

</body>
</html>
