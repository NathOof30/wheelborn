<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WHEELBORN') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Crimson+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="codex-body font-body antialiased min-h-screen">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
            <a href="/" class="group mb-7 flex flex-col items-center gap-3 text-center">
                <span class="w-16 h-16 rounded-full bg-leather border-2 border-brass flex items-center justify-center shadow-xl transition-transform duration-300 group-hover:scale-105" aria-hidden="true">
                    <svg class="w-10 h-10 text-brass-light" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="1.6"/>
                        <circle cx="18" cy="18" r="10.2" fill="none" stroke="currentColor" stroke-width="1.2" stroke-dasharray="2.4 2"/>
                        <circle cx="18" cy="18" r="5" fill="#8A1A14" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M18 1.5 L20.2 6.5 L15.8 6.5 Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="font-gothic text-3xl tracking-wider text-parchment-bright drop-shadow">
                    Wheelborn
                </span>
            </a>

            <div class="w-full sm:max-w-md rounded-xl p-2 bg-leather border border-brass/50 shadow-2xl">
                <div class="codex-panel codex-panel--framed px-6 sm:px-8 py-8">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-8 font-body text-sm italic text-parchment-shade/85 text-center">
                Ouvrez le Codex et forgez votre destin.
            </p>
        </div>
    </body>
</html>
