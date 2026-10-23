<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WHEELBORN — Forgez votre destin</title>
    <meta name="description" content="WHEELBORN : Jeu de cartes à combattants aléatoires. Faites tourner la roue, forgez des guerriers uniques, combattez dans l'arène.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,600,700,800|inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex flex-col cosmos-field">

    {{-- ═══════════════════════════════════════════════════════════
         NAVIGATION MINIMALE — FLOTTANTE, PRESQUE INVISIBLE
         ═══════════════════════════════════════════════════════════ --}}
    <header class="fixed top-0 inset-x-0 z-50">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <span class="text-2xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-12">🎡</span>
                <span class="font-display font-extrabold text-lg tracking-[0.12em] uppercase heading-majestic">
                    WHEELBORN
                </span>
            </a>

            <div class="flex items-center gap-3 text-sm font-medium">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-2xl bg-gold/90 text-cosmos-deep font-bold hover:bg-gold-light transition-all duration-300 hover:shadow-lg hover:shadow-gold/20">
                            Jouer
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-200 transition-all duration-300">
                            Connexion
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-2xl glass text-slate-200 font-medium hover:text-white hover:bg-white/[0.08] transition-all duration-300">
                                S'inscrire
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════════
         HERO — LE VIDE CONTEMPLATIF
         Grande respiration verticale, texte majestueux centré,
         le vide autour donne la sensation de grandeur.
         ═══════════════════════════════════════════════════════════ --}}
    <main class="flex-1 relative z-10">
        <section class="min-h-screen flex flex-col justify-center items-center px-6 text-center">

            {{-- Orbe décorative subtile —  un halo doré lointain --}}
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-gold/[0.03] blur-[120px] pointer-events-none animate-breathe"></div>

            {{-- Badge discret --}}
            <div class="animate-fade-in-up">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-[11px] text-slate-400 font-medium tracking-wider uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold animate-pulse"></span>
                    Projet Universitaire · Laravel 11 & Livewire 3
                </span>
            </div>

            {{-- Respiration --}}
            <div class="h-10 sm:h-14"></div>

            {{-- Titre majestueux --}}
            <h1 class="animate-fade-in-up animate-delay-200">
                <span class="block font-display font-extrabold text-5xl sm:text-6xl md:text-7xl lg:text-8xl tracking-tight text-white/95 leading-[0.95]">
                    Créez votre
                </span>
                <span class="block font-display font-extrabold text-5xl sm:text-6xl md:text-7xl lg:text-8xl tracking-tight leading-[0.95] mt-2 sm:mt-3">
                    <span class="heading-majestic">combattant.</span>
                </span>
            </h1>

            {{-- Respiration --}}
            <div class="h-8 sm:h-10"></div>

            {{-- Sous-titre — contemplatif --}}
            <p class="text-contemplative text-center mx-auto text-base sm:text-lg animate-fade-in-up animate-delay-300">
                Faites tourner la roue pour fusionner des races, armes et pouvoirs élémentaires.
                Découvrez des synergies secrètes. Affrontez le destin dans l'arène.
            </p>

            {{-- Grande respiration avant le CTA --}}
            <div class="h-12 sm:h-16"></div>

            {{-- CTA --}}
            <div class="flex flex-col sm:flex-row items-center gap-4 animate-fade-in-up animate-delay-400">
                @auth
                    <a href="{{ route('dashboard') }}" class="group px-10 py-4 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-extrabold text-sm uppercase tracking-[0.15em] transition-all duration-500 hover:shadow-2xl hover:shadow-gold/25 hover:scale-[1.03] active:scale-95">
                        <span class="flex items-center gap-3">
                            <span class="text-lg group-hover:rotate-180 transition-transform duration-700">🎡</span>
                            Ouvrir le Jeu
                        </span>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="group px-10 py-4 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-extrabold text-sm uppercase tracking-[0.15em] transition-all duration-500 hover:shadow-2xl hover:shadow-gold/25 hover:scale-[1.03] active:scale-95">
                        <span class="flex items-center gap-3">
                            <span class="text-lg group-hover:rotate-180 transition-transform duration-700">🎡</span>
                            Forger mon destin
                        </span>
                    </a>
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-2xl glass text-slate-300 font-medium text-sm hover:text-white hover:bg-white/[0.06] transition-all duration-300">
                        Déjà inscrit ? Connexion
                    </a>
                @endauth
            </div>

            {{-- Indicateur de scroll subtil --}}
            <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-fade-in animate-delay-700">
                <div class="flex flex-col items-center gap-2 text-slate-600">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-medium">Découvrir</span>
                    <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════
             SECTION FEATURES — RESPIRÉE
             ═══════════════════════════════════════════════════════════ --}}
        <section class="section-wide relative">
            {{-- Séparateur lumineux --}}
            <div class="divider-glow max-w-md mx-auto mb-20 sm:mb-26"></div>

            <div class="max-w-5xl mx-auto px-6">
                {{-- Sous-titre de section --}}
                <div class="text-center mb-16 sm:mb-22">
                    <h2 class="font-display font-extrabold text-2xl sm:text-3xl text-white/90 tracking-wide">
                        Trois piliers du destin
                    </h2>
                    <p class="text-contemplative text-center mx-auto mt-4 text-sm sm:text-base">
                        Chaque partie est une nouvelle histoire. Le hasard forge, la stratégie tranche.
                    </p>
                </div>

                {{-- Feature cards — flottantes, aérées --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10">
                    {{-- Card 1 : Roue --}}
                    <div class="group glass-card rounded-3xl p-8 sm:p-10 text-center transition-all duration-500 hover:bg-white/[0.04] hover:border-gold/20 hover:shadow-xl hover:shadow-gold/5 hover:-translate-y-1">
                        <div class="text-4xl mb-6 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12">🎡</div>
                        <h3 class="font-display font-bold text-white text-lg mb-3 tracking-wide">Roue Aléatoire</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            8 Races, 8 Armes et 8 Pouvoirs élémentaires. Pity system garanti toutes les 10 tentatives.
                        </p>
                    </div>

                    {{-- Card 2 : Duels --}}
                    <div class="group glass-card rounded-3xl p-8 sm:p-10 text-center transition-all duration-500 hover:bg-white/[0.04] hover:border-rose-500/20 hover:shadow-xl hover:shadow-rose-500/5 hover:-translate-y-1">
                        <div class="text-4xl mb-6 transition-transform duration-500 group-hover:scale-110">⚔️</div>
                        <h3 class="font-display font-bold text-white text-lg mb-3 tracking-wide">Duels & Élo</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Système de combat FIDE. De la ligue Bronze à la ligue Maître selon vos victoires.
                        </p>
                    </div>

                    {{-- Card 3 : Recyclage --}}
                    <div class="group glass-card rounded-3xl p-8 sm:p-10 text-center transition-all duration-500 hover:bg-white/[0.04] hover:border-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1">
                        <div class="text-4xl mb-6 transition-transform duration-500 group-hover:scale-110">♻️</div>
                        <h3 class="font-display font-bold text-white text-lg mb-3 tracking-wide">Recyclage & Atelier</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Recyclez les combattants en pièces d'or pour débloquer des améliorations permanentes.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════
             RESPIRATION FINALE — avant le footer
             ═══════════════════════════════════════════════════════════ --}}
        <div class="h-20 sm:h-30"></div>
    </main>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/[0.04] py-10 text-center">
        <p class="text-xs text-slate-600 tracking-wide">
            WHEELBORN — Projet Universitaire Full-Stack
        </p>
        <p class="text-[10px] text-slate-700 mt-1">
            Nathanaël & Mathéo · Laravel 11 · Livewire 3 · Tailwind CSS
        </p>
    </footer>
</body>
</html>
