<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WHEELBORN — Jeu de Cartes & Combats Aléatoires</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,600,700,800|inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans selection:bg-amber-500 selection:text-black">
    <!-- Navigation Bar -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="text-2xl">🎡</span>
                <span class="font-extrabold text-xl tracking-wider bg-gradient-to-r from-amber-400 via-indigo-400 to-cyan-400 bg-clip-text text-transparent">
                    WHEELBORN
                </span>
            </a>

            <div class="flex items-center gap-3 text-sm font-semibold">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-amber-400 text-slate-950 hover:bg-amber-300 transition">
                            Jouer (Dashboard)
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Connexion
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition">
                                S'inscrire
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col justify-center items-center px-4 py-16 text-center max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-xs text-amber-400 font-semibold mb-6">
            <span>✨</span> Projet Universitaire Laravel 11 & Livewire 3
        </div>

        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white mb-6">
            Créez votre combattant.<br>
            <span class="bg-gradient-to-r from-amber-400 via-pink-400 to-cyan-400 bg-clip-text text-transparent">
                Affrontez le destin.
            </span>
        </h1>

        <p class="text-slate-400 text-base sm:text-lg max-w-2xl mb-10">
            Faites tourner la roue pour fusionner des races, armes et pouvoirs élémentaires. Débloquez des synergies secrètes, combattez dans l'arène et grimpez les ligues d'Élo.
        </p>

        <div class="flex flex-col sm:flex-row items-center gap-4 mb-16">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 font-extrabold text-base uppercase tracking-wider hover:scale-105 transition shadow-lg shadow-amber-500/20">
                    🎡 Ouvrir le Jeu
                </a>
            @else
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-indigo-500 to-cyan-500 text-white font-extrabold text-base uppercase tracking-wider hover:scale-105 transition shadow-lg shadow-cyan-500/20">
                    🚀 Créer un compte joueur
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 font-bold hover:bg-slate-800 transition">
                    Déjà inscrit ? Connexion
                </a>
            @endauth
        </div>

        <!-- 3 Simple Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left w-full">
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800">
                <div class="text-3xl mb-3">🎡</div>
                <h3 class="font-bold text-white text-lg mb-2">Roue Aléatoire</h3>
                <p class="text-sm text-slate-400">8 Races, 8 Armes et 8 Pouvoirs. Pity system garanti toutes les 10 tentatives.</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800">
                <div class="text-3xl mb-3">⚔️</div>
                <h3 class="font-bold text-white text-lg mb-2">Duels & Élo</h3>
                <p class="text-sm text-slate-400">Système de combat FIDE. De la ligue Bronze à la ligue Maître selon vos victoires.</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800">
                <div class="text-3xl mb-3">♻️</div>
                <h3 class="font-bold text-white text-lg mb-2">Recyclage & Atelier</h3>
                <p class="text-sm text-slate-400">Recyclez les combattants superflus en pièces d'or pour débloquer des améliorations permanentes.</p>
            </div>
        </div>
    </main>

    <!-- Simple Footer -->
    <footer class="border-t border-slate-900 py-6 text-center text-xs text-slate-600">
        WHEELBORN — Projet Universitaire Full-Stack • Laravel 11 + Livewire 3 + Tailwind CSS
    </footer>
</body>
</html>
