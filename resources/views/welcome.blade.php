@php
    $showcaseCards = [
        [
            'id' => 'welcome-valerian',
            'name' => 'Valérian Brise-Fer',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => 'common',
            'hp' => 118,
            'max_hp' => 118,
            'strength' => 86,
            'speed' => 74,
            'defense' => 78,
            'power_score' => 92,
            'rarity' => 'legendary',
            'is_lucky_spin' => true,
            'recycle_value' => 260,
            'wins' => 14,
            'losses' => 3,
        ],
        [
            'id' => 'welcome-sylvaelle',
            'name' => 'Sylvaëlle Foudre-Arc',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'lightning',
            'power_tier' => 'rare',
            'hp' => 104,
            'max_hp' => 104,
            'strength' => 82,
            'speed' => 94,
            'defense' => 62,
            'power_score' => 86,
            'rarity' => 'epic',
            'is_lucky_spin' => false,
            'recycle_value' => 165,
            'wins' => 9,
            'losses' => 4,
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WHEELBORN — Forgez votre destin</title>
    <meta name="description" content="WHEELBORN : Jeu de cartes à combattants aléatoires. Faites tourner la roue, forgez des guerriers uniques, combattez dans l'arène.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Crimson+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="codex-body font-body antialiased min-h-screen flex flex-col">
    {{-- En-tête minimaliste cuir & laiton --}}
    <header class="codex-navbar">
        <div class="codex-navbar-inner px-3 sm:px-5 gap-2">
            <a href="/" class="codex-brand gap-1.5 sm:gap-2.5" aria-label="WHEELBORN — Accueil">
                <svg class="codex-brand-crest w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 36 36" aria-hidden="true">
                    <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="1.6"/>
                    <circle cx="18" cy="18" r="10.2" fill="none" stroke="currentColor" stroke-width="1.2" stroke-dasharray="2.4 2"/>
                    <circle cx="18" cy="18" r="5" fill="#8A1A14" stroke="currentColor" stroke-width="1.3"/>
                    <path d="M18 1.5 L20.2 6.5 L15.8 6.5 Z" fill="currentColor"/>
                </svg>
                <span class="codex-brand-name text-xl sm:text-2xl">Wheelborn</span>
            </a>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-brass btn-brass--sm px-3 sm:px-4">
                            Ouvrir le Jeu
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="font-display text-[11px] sm:text-sm font-bold text-parchment-shade hover:text-parchment-bright px-2 sm:px-3 py-1.5 transition">
                            Connexion
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-wax btn-wax--sm px-2.5 sm:px-3.5 text-[11px] sm:text-xs">
                                S'inscrire
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    {{-- Couverture de Grimoire --}}
    <main class="codex-stage flex-1">
        <div class="codex-shell">
            <aside class="codex-spine" aria-hidden="true">
                <span class="spine-band"></span>
                <span class="spine-band"></span>
                <span class="spine-band"></span>
                <span class="spine-band"></span>
                <span class="spine-band"></span>
                <div class="codex-clasp codex-clasp--top"></div>
                <div class="codex-clasp codex-clasp--bottom"></div>
            </aside>

            <div class="codex-vellum space-y-12">
                {{-- HERO : COUVERTURE ENLUMINÉE --}}
                <section class="relative overflow-hidden rounded-xl border-2 border-brass/70 bg-parchment-bright/80 px-6 sm:px-10 py-12 sm:py-16 text-center shadow-inner">
                    {{-- Volvelle décorative en rotation lente --}}
                    <div class="pointer-events-none absolute -right-24 -top-24 sm:-right-16 sm:-top-20 w-72 h-72 sm:w-96 sm:h-96 opacity-[0.13] text-brass-dark" aria-hidden="true">
                        <svg viewBox="0 0 240 240" class="w-full h-full animate-spin-slow">
                            <circle cx="120" cy="120" r="112" fill="none" stroke="currentColor" stroke-width="2"/>
                            <circle cx="120" cy="120" r="90" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5 5"/>
                            <circle cx="120" cy="120" r="64" fill="none" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="120" cy="120" r="34" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 4"/>
                            @for ($deg = 0; $deg < 360; $deg += 30)
                                <line x1="120" y1="8" x2="120" y2="232" stroke="currentColor" stroke-width="1" transform="rotate({{ $deg }} 120 120)"/>
                            @endfor
                        </svg>
                    </div>

                    <div class="relative z-10 max-w-2xl mx-auto space-y-6">
                        <p class="font-display text-xs uppercase font-bold tracking-[0.22em] text-wax">
                            Codex des Sceaux &amp; Duels d'Arène
                        </p>

                        <h1 class="font-display text-4xl sm:text-5xl md:text-6xl font-bold text-ink leading-[1.08] tracking-tight">
                            Créez votre <span class="text-wax">combattant.</span>
                        </h1>

                        <p class="font-body text-lg sm:text-xl text-ink-sepia leading-relaxed max-w-xl mx-auto">
                            Faites tourner la Volvelle pour fusionner races, armes et pouvoirs élémentaires, révéler des synergies secrètes et gravir les ligues de l'Arène.
                        </p>

                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-wax px-8 py-3.5 text-sm sm:text-base">
                                    Ouvrir le Jeu
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="btn-wax px-8 py-3.5 text-sm sm:text-base">
                                    Forger mon destin
                                </a>
                                <a href="{{ route('login') }}" class="btn-cartouche px-7 py-3.5 text-sm">
                                    Connexion
                                </a>
                            @endauth
                        </div>
                    </div>
                </section>

                {{-- VITRINE INTERACTIVE : 2 VRAIES CARTES UNIVERSELLES FLIPPABLES --}}
                <section class="space-y-6">
                    <div class="text-center max-w-xl mx-auto space-y-1.5">
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-ink">
                            Des combattants gravés dans le parchemin
                        </h2>
                        <p class="font-body text-base italic text-ink-muted">
                            Cliquez sur une carte pour la retourner et découvrir ses passifs et synergies actives.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-start justify-center gap-8">
                        @foreach ($showcaseCards as $demoCard)
                            <x-folio-card
                                :card="$demoCard"
                                :showOwnerActions="false"
                                :folioKey="$demoCard['id']"
                            />
                        @endforeach
                    </div>
                </section>

                <hr class="codex-divider max-w-md mx-auto">

                {{-- TROIS PILIERS DU DESTIN --}}
                <section class="space-y-6">
                    <div class="text-center max-w-xl mx-auto space-y-1.5">
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-ink">
                            Trois piliers du destin
                        </h2>
                        <p class="font-body text-base italic text-ink-muted">
                            Le hasard forge, la stratégie tranche.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <article class="codex-panel codex-panel--framed p-6 text-center space-y-3">
                            <div class="mx-auto w-12 h-12 rounded-full bg-parchment-shade/80 border border-brass-dark/50 flex items-center justify-center text-wax">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="2 2"/>
                                    <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-bold text-ink">Roue Aléatoire</h3>
                            <p class="font-body text-sm text-ink-sepia leading-relaxed">
                                8 Races, 8 Armes et 8 Pouvoirs élémentaires. Pity garanti toutes les 10 tentatives.
                            </p>
                        </article>

                        <article class="codex-panel codex-panel--framed p-6 text-center space-y-3">
                            <div class="mx-auto w-12 h-12 rounded-full bg-parchment-shade/80 border border-brass-dark/50 flex items-center justify-center text-wax">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-bold text-ink">Duels &amp; Élo</h3>
                            <p class="font-body text-sm text-ink-sepia leading-relaxed">
                                Combats tactiques et classement FIDE : gravissez les ligues de Bronze jusqu'à Maître.
                            </p>
                        </article>

                        <article class="codex-panel codex-panel--framed p-6 text-center space-y-3">
                            <div class="mx-auto w-12 h-12 rounded-full bg-parchment-shade/80 border border-brass-dark/50 flex items-center justify-center text-wax">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 13 H18 L21 9 H14 L13 7 H6 Z M9 13 V19 M15 13 V19 M6 19 H18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="font-display text-lg font-bold text-ink">Recyclage &amp; Atelier</h3>
                            <p class="font-body text-sm text-ink-sepia leading-relaxed">
                                Recyclez vos combattants en pièces d'or pour débloquer des améliorations permanentes.
                            </p>
                        </article>
                    </div>
                </section>
            </div>

            <div class="codex-gilded-edge" aria-hidden="true"></div>
        </div>
    </main>

    {{-- Colophon sobre --}}
    <footer class="codex-colophon">
        <span>WHEELBORN — Nathanaël &amp; Mathéo</span>
    </footer>

    {{-- Portal Tooltip Parchemin unique sur <body> pour les cartes vitrine --}}
    <div id="codex-tooltip-portal" class="codex-tooltip" role="tooltip" hidden></div>
</body>
</html>
