<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WHEELBORN') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,600,700,800|inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen cosmos-field">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 relative z-10">

            {{-- Logo WHEELBORN —  grand, contemplatif --}}
            <a href="/" class="group mb-12 flex flex-col items-center gap-4 animate-fade-in-up">
                <span class="text-5xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12">🎡</span>
                <span class="font-display font-extrabold text-2xl tracking-[0.15em] uppercase heading-majestic">
                    WHEELBORN
                </span>
            </a>

            {{-- Card d'authentification — glassmorphism --}}
            <div class="w-full sm:max-w-md glass-card rounded-3xl px-8 py-10 shadow-2xl shadow-black/20 animate-fade-in-up animate-delay-200">
                {{ $slot }}
            </div>

            {{-- Tagline discret --}}
            <p class="mt-10 text-xs text-slate-600 tracking-wide animate-fade-in animate-delay-500">
                Forger votre destin commence ici.
            </p>
        </div>
    </body>
</html>
