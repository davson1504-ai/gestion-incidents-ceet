<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1e3a5f">

        <title>{{ config('app.name', 'Gestion Incidents CEET') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --ceet-sidebar-width: 280px;
                --ceet-topbar-height: 72px;
            }

            html,
            body {
                height: 100%;
            }

            .ceet-main-wrapper {
                margin-left: var(--ceet-sidebar-width);
                min-height: 100vh;
                padding-top: calc(var(--ceet-topbar-height) + 1rem);
            }

            @media (min-width: 992px) {
                body {
                    overflow: hidden;
                }

                .ceet-main-wrapper {
                    height: 100vh;
                    overflow-y: auto;
                    overflow-x: hidden;
                }
            }

            @media (max-width: 991.98px) {
                .ceet-main-wrapper {
                    margin-left: 0;
                    min-height: auto;
                    padding-top: calc(var(--ceet-topbar-height) + 0.75rem);
                }
            }
        </style>
    </head>
    <body>
        @include('layouts.navigation')

        <main class="ceet-main-wrapper pb-4">
            <div class="container-fluid px-3 px-lg-4">
                @isset($header)
                    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        {{ $header }}
                    </div>
                @endisset

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </body>
</html>