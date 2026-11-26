<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WHEELBORN') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Crimson+Pro:ital,wght@0,500;0,600;0,700;1,500&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="codex-body font-body antialiased min-h-screen flex flex-col">
        @include('layouts.navigation')

        <div class="codex-stage flex-1">
            <main class="codex-shell" aria-label="Codex Wheelborn">
                <aside class="codex-spine" aria-hidden="true">
                    <span class="spine-band"></span>
                    <span class="spine-band"></span>
                    <span class="spine-band"></span>
                    <span class="spine-band"></span>
                    <div class="codex-clasp codex-clasp--top"></div>
                    <div class="codex-clasp codex-clasp--bottom"></div>
                </aside>

                <div class="codex-vellum">
                    @isset($header)
                        <div class="codex-chapter-head">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </div>

                <div class="codex-gilded-edge" aria-hidden="true"></div>
            </main>
        </div>

        <footer class="codex-colophon">
            <span>WHEELBORN — Nathanaël &amp; Mathéo</span>
        </footer>

        <div id="codex-tooltip-portal" class="codex-tooltip" role="tooltip" hidden></div>

        @livewireScripts
    </body>
</html>
