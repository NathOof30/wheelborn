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

        <!-- Scripts & Styles -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen flex flex-col cosmos-field">
        <div class="min-h-screen flex flex-col relative z-10">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="glass border-b border-white/[0.04]">
                    <div class="max-w-6xl mx-auto py-5 px-6 sm:px-8 lg:px-10">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content — respiration verticale -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer contemplatif -->
            <footer class="border-t border-white/[0.04] py-10 text-center">
                <p class="text-xs text-slate-600 tracking-wide">
                    WHEELBORN — Projet Universitaire Full-Stack
                </p>
                <p class="text-[10px] text-slate-700 mt-1">
                    Laravel 11 · Livewire 3 · Tailwind CSS
                </p>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
