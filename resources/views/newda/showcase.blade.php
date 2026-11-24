@php
    $renderPowerSigilSvg = static function (string $powerId, string $hex, string $class = 'codex-sigil-watermark'): string {
        $paths = match ($powerId) {
            'fire' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="0.9" stroke-dasharray="3 3"/><polygon points="60,10 70,42 104,42 76,62 86,96 60,76 34,96 44,62 16,42 50,42" fill="none" stroke="currentColor" stroke-width="1.3"/><polygon points="60,18 72,48 102,60 72,72 60,102 48,72 18,60 48,48" fill="none" stroke="currentColor" stroke-width="1.1"/>',
            'poison' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="36" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="20" fill="none" stroke="currentColor" stroke-width="1.2" stroke-dasharray="5 3"/><path d="M60 12 C86 12,102 36,92 60 C82 84,54 96,36 82 C20 70,22 44,40 34 C56 25,74 36,72 54 C70 68,54 74,48 64" fill="none" stroke="currentColor" stroke-width="1.5"/>',
            'ice' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,12 102,84 18,84" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,108 18,36 102,36" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="14" fill="none" stroke="currentColor" stroke-width="1.2"/>',
            'lightning' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="24" y="24" width="72" height="72" fill="none" stroke="currentColor" stroke-width="1.2"/><rect x="24" y="24" width="72" height="72" transform="rotate(45 60 60)" fill="none" stroke="currentColor" stroke-width="1.2"/><path d="M66 16 L46 58 L64 58 L52 104" fill="none" stroke="currentColor" stroke-width="1.8"/>',
            'regeneration' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="44" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="76" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M60 22 C72 40,72 80,60 98 C48 80,48 40,60 22 Z" fill="none" stroke="currentColor" stroke-width="1.3"/>',
            'gravity' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(-24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="16" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="60" cy="60" r="5" fill="currentColor"/>',
            'teleportation' => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="46" cy="52" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="74" cy="68" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M26 94 L94 26 M84 26 L94 26 L94 36" fill="none" stroke="currentColor" stroke-width="1.3"/>',
            default => '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.3" stroke-dasharray="6 4"/><circle cx="60" cy="60" r="38" fill="none" stroke="currentColor" stroke-width="1.1" stroke-dasharray="3 4"/><path d="M18 60 Q60 26 102 60 Q60 94 18 60 Z" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="11" fill="none" stroke="currentColor" stroke-width="1.3"/><line x1="24" y1="96" x2="96" y2="24" stroke="currentColor" stroke-width="1.2"/>',
        };

        return '<svg class="'.$class.'" viewBox="0 0 120 120" style="color: '.$hex.';" aria-hidden="true">'.$paths.'</svg>';
    };

    $renderRaceRuneSvg = static function (string $glyph): string {
        $paths = match ($glyph) {
            'horns' => '<path d="M12 10 C8 22,16 32,24 34 C32 32,40 22,36 10 C32 18,28 21,24 21 C20 21,16 18,12 10 Z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="24" cy="30" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>',
            'gear' => '<circle cx="24" cy="24" r="11" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="24" cy="24" r="4.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M24 6 V11 M24 37 V42 M6 24 H11 M37 24 H42 M11 11 L15 15 M33 33 L37 37 M37 11 L33 15 M15 33 L11 37" stroke="currentColor" stroke-width="2"/>',
            'leaf' => '<path d="M12 36 C12 18,24 10,36 10 C36 24,28 36,12 36 Z M12 36 L30 18" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            'helix' => '<path d="M14 10 C34 16,14 32,34 38 M34 10 C14 16,34 32,14 38" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            'monolith' => '<polygon points="24,8 36,16 34,38 14,38 12,16" fill="none" stroke="currentColor" stroke-width="1.8"/><line x1="24" y1="8" x2="24" y2="38" stroke="currentColor" stroke-width="1.2"/>',
            'chalice' => '<path d="M14 12 H34 L29 25 C28 28,20 28,19 25 Z M24 27 V38 M17 38 H31" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            'astrolabe' => '<circle cx="24" cy="24" r="14" fill="none" stroke="currentColor" stroke-width="1.5"/><ellipse cx="24" cy="24" rx="14" ry="6" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="24" cy="24" r="2.5" fill="currentColor"/>',
            default => '<circle cx="24" cy="24" r="14" fill="none" stroke="currentColor" stroke-width="1.5"/><polygon points="24,8 28,24 24,40 20,24" fill="none" stroke="currentColor" stroke-width="1.4"/>',
        };

        return '<svg class="heraldic-rune" viewBox="0 0 48 48" aria-hidden="true">'.$paths.'</svg>';
    };

    $defaultBenchLayers = $codexPayload['paperDollMap']['sword']['lightning'] ?? [
        'race' => asset('images/characters/races/human.webp'),
        'weapon' => asset('images/characters/weapons/sword.webp'),
        'aura' => asset('images/characters/weapons/sword_auras/sword_lightning.webp'),
        'is_invisibility' => false,
        'is_pale_aura' => true,
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Codex Wheelborn — Prototype DA « Sceaux Arcaniques »</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=IM+Fell+English:ital@0;1&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/newda.css') }}">
    <script src="{{ asset('js/newda.js') }}" defer></script>
</head>
<body class="codex-body">

<div class="codex-stage">
    <main class="codex-shell" aria-label="Codex Wheelborn">
        {{-- Reliure Cuir Gauche & Fermoirs Laiton --}}
        <aside class="codex-spine" aria-hidden="true">
            <span class="spine-band"></span>
            <span class="spine-band"></span>
            <span class="spine-band"></span>
            <span class="spine-band"></span>
            <span class="spine-band"></span>
            <div class="codex-clasp codex-clasp--top"></div>
            <div class="codex-clasp codex-clasp--bottom"></div>
        </aside>

        {{-- Pages Vélin --}}
        <div class="codex-vellum">
            {{-- Marginalia discrètes réservées au Codex global --}}
            <span class="codex-marginalia codex-marginalia--top-right" aria-hidden="true">F° 01r — Édition d'Essai</span>
            <span class="codex-marginalia codex-marginalia--fleuron" aria-hidden="true">❦</span>
            <svg class="codex-marginalia codex-marginalia--inkblot" viewBox="0 0 60 60" aria-hidden="true">
                <path d="M28 12 C38 10,48 20,44 32 C41 42,26 50,18 40 C11 31,17 14,28 12 Z M47 41 C51 41,53 45,50 48 C47 50,44 46,47 41 Z" fill="currentColor"/>
            </svg>

            {{-- EN-TÊTE & HUD MANUSCRIT --}}
            <header class="codex-header">
                <div class="codex-top-meta">
                    <span class="codex-pill codex-pill--accent">Prototype DA · /newda · Non référencé · Lecture seule</span>

                    @if ($connectedSnapshot !== null)
                        <div class="volvelle-controls-group" role="group" aria-label="Source du Grimoire">
                            <button type="button" class="btn-cartouche is-active" data-source-toggle="showcase">
                                Mode Vitrine (6)
                            </button>
                            <button type="button" class="btn-cartouche" data-source-toggle="connected">
                                Mon Grimoire ({{ $connectedSnapshot['folios_count'] }})
                            </button>
                        </div>
                    @else
                        <span class="codex-pill">Mode Bac à Sable · Zéro écriture BDD</span>
                    @endif
                </div>

                <h1 class="codex-title">Codex Wheelborn</h1>
                <p class="codex-subtitle">Grimoire des Sceaux Arcaniques &amp; des Plans Croisés — Adaptation de la Direction Artistique</p>

                <section class="hud-bar" aria-label="État du Copiste">
                    <div class="hud-item">
                        <span class="hud-label">Gouttes d'Encre</span>
                        <div class="hud-value tabular-nums">
                            <span class="ink-drops" id="hud-ink-drops" aria-hidden="true">
                                @for ($i = 0; $i < $hud['spin_max']; $i++)
                                    <svg class="ink-drop {{ $i >= $hud['spin_charges'] ? 'is-spent' : '' }}" viewBox="0 0 16 20">
                                        <path d="M8 1.5 C8 1.5 2 9.5 2 13.5 C2 16.8 4.7 19 8 19 C11.3 19 14 16.8 14 13.5 C14 9.5 8 1.5 8 1.5 Z"/>
                                    </svg>
                                @endfor
                            </span>
                            <span id="hud-ink-text">{{ $hud['spin_charges'] }}/{{ $hud['spin_max'] }}</span>
                        </div>
                    </div>

                    <div class="hud-item">
                        <span class="hud-label">Alignement Grand Sceau</span>
                        <div class="hud-value tabular-nums">
                            <span class="pity-runes" id="hud-pity-runes" aria-hidden="true">
                                @php $isHudLuckyReady = $hud['pity_counter'] >= $hud['pity_threshold']; @endphp
                                @for ($i = 0; $i < $hud['pity_threshold']; $i++)
                                    <span class="pity-pip {{ $isHudLuckyReady ? 'is-ready' : ($i < $hud['pity_counter'] ? 'is-lit' : '') }}"></span>
                                @endfor
                            </span>
                            <span id="hud-pity-text">{{ $hud['pity_counter'] }}/{{ $hud['pity_threshold'] }}</span>
                        </div>
                    </div>

                    <div class="hud-item">
                        <span class="hud-label">Écus d'Or</span>
                        <div class="hud-value tabular-nums" id="hud-coins">
                            {{ number_format($hud['coins'], 0, ',', ' ') }} écus
                        </div>
                    </div>

                    <div class="hud-item">
                        <span class="hud-label">Ligue d'Arène</span>
                        <div class="hud-value tabular-nums">
                            {{ $hud['league_label'] }} · {{ number_format($hud['elo'], 0, ',', ' ') }} Élo
                        </div>
                    </div>

                    <div class="hud-item">
                        <span class="hud-label">Rang Copiste</span>
                        <div class="hud-value">
                            Niv. {{ $hud['level'] }} · {{ $hud['level_title'] }}
                        </div>
                    </div>
                </section>
            </header>

            {{-- ================================================================
                 CHAPITRE I — JUSTIFICATION DU CONCEPT
                 ================================================================ --}}
            <section id="chapitre-concept" class="codex-chapter" aria-labelledby="title-concept">
                <div class="chapter-head">
                    <div>
                        <span class="chapter-numeral">Chapitre I</span>
                        <h2 id="title-concept" class="chapter-title">Le Codex des Plans Croisés</h2>
                    </div>
                    <span class="chapter-folio-ref">F° 02r · Justification de l'Univers</span>
                </div>

                <p class="illuminated-intro">
                    <span class="drop-cap" aria-hidden="true">L</span>
                    'Ordre de la Roue ne forge pas de cartes plastifiées : ses archivistes manient une <strong>Volvelle</strong> — trois disques concentriques de parchemin rivetés au laiton — pour arracher des entités aux failles dimensionnelles et les consigner sur des <em>Folios Scellés</em>. Lorsque les anneaux s'alignent sur un automate du Plan de Fer (<em>Robot</em>), un visiteur des sphères extérieures (<em>Alien</em>) ou une arquebuse d'éther (<em>Pistolet</em>), le copiste conserve le nom exact du jeu tout en l'inscrivant dans la langue de son grimoire.
                </p>

                <div class="concept-grid">
                    <div class="parchment-panel">
                        <h3 class="panel-heading">Transposition des Mécaniques WHEELBORN</h3>
                        <dl class="scribe-list">
                            <div class="scribe-row"><dt>Roue d'Invocation</dt><span class="scribe-dots"></span><dd>Sceau-Volvelle (3 anneaux)</dd></div>
                            <div class="scribe-row"><dt>Cartes Héros</dt><span class="scribe-dots"></span><dd>Folios Scellés Recto/Verso</dd></div>
                            <div class="scribe-row"><dt>Charges (3/3)</dt><span class="scribe-dots"></span><dd>Gouttes d'Encre</dd></div>
                            <div class="scribe-row"><dt>Jauge Pity (10/10)</dt><span class="scribe-dots"></span><dd>Alignement des 10 Runes</dd></div>
                            <div class="scribe-row"><dt>Recyclage</dt><span class="scribe-dots"></span><dd>Transmutation en Écus</dd></div>
                            <div class="scribe-row"><dt>Verrouillage / Favoris</dt><span class="scribe-dots"></span><dd>Sceau de Garde · Table d'Honneur</dd></div>
                            <div class="scribe-row"><dt>Synergies</dt><span class="scribe-dots"></span><dd>Accord de Sceau / Fissure Instable</dd></div>
                        </dl>
                    </div>

                    <div class="parchment-panel">
                        <h3 class="panel-heading">Cachets de Rareté (4 Paliers)</h3>
                        <dl class="scribe-list">
                            @foreach ($rarities as $rarity)
                                <div class="scribe-row">
                                    <dt style="color: {{ $rarity['seal_hex'] }};">{{ $rarity['label'] }}</dt>
                                    <span class="scribe-dots"></span>
                                    <dd>{{ $rarity['seal_name'] }} · {{ $rarity['seal_border'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="folio-note" style="margin-top: 12px;">
                            Les étoiles ambiguës disparaissent au profit de la matière du cachet et du filet d'enluminure. Le multiplicateur de transmutation réel (×1,00 à ×1,50) est conservé.
                        </p>
                    </div>
                </div>

                <div class="compendium-grid">
                    <div class="parchment-panel">
                        <h3 class="panel-heading">
                            <span>Les 8 Races du Codex</span>
                            <span class="chapter-folio-ref">RaceData</span>
                        </h3>
                        @foreach ($races as $race)
                            <div class="gloss-entry">
                                <div>
                                    <span class="gloss-name">{{ $race['name'] }}</span>
                                    <span class="gloss-sub">{{ $race['codex_gloss'] }}</span>
                                </div>
                                <span class="gloss-tag">
                                    {{ $race['has_portrait'] ? 'Portrait WebP' : 'Blason gravé' }} · {{ $race['weight'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="parchment-panel">
                        <h3 class="panel-heading">
                            <span>Les 8 Armes Consignées</span>
                            <span class="chapter-folio-ref">WeaponData</span>
                        </h3>
                        @foreach ($weapons as $weapon)
                            <div class="gloss-entry">
                                <div>
                                    <span class="gloss-name">{{ $weapon['name'] }}</span>
                                    <span class="gloss-sub">{{ $weapon['codex_gloss'] }}</span>
                                </div>
                                <span class="gloss-tag">
                                    {{ $weapon['has_asset'] ? 'Calque WebP' : 'Glyphe gravé' }} · poids {{ $weapon['weight'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="parchment-panel">
                    <h3 class="panel-heading">
                        <span>Pigments Alchimiques &amp; Filigranes SVG (8 Pouvoirs)</span>
                        <span class="chapter-folio-ref">Zéro néon · Encres historiques</span>
                    </h3>
                    <div class="pigments-grid">
                        @foreach ($powers as $power)
                            <div class="pigment-swatch">
                                {!! $renderPowerSigilSvg($power['id'], $power['pigment_hex'], 'pigment-sigil-mini') !!}
                                <div>
                                    <div class="pigment-title">{{ $power['name'] }} <span class="gloss-tag">({{ $power['weight'] }}%)</span></div>
                                    <div class="pigment-meta">{{ $power['pigment_name'] }} · {{ $power['tier_label'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ================================================================
                 CHAPITRE II — LE SCEAU-VOLVELLE D'INVOCATION
                 ================================================================ --}}
            <section id="chapitre-volvelle" class="codex-chapter" aria-labelledby="title-volvelle">
                <div class="chapter-head">
                    <div>
                        <span class="chapter-numeral">Chapitre II</span>
                        <h2 id="title-volvelle" class="chapter-title">Le Sceau-Volvelle d'Invocation</h2>
                    </div>
                    <span class="chapter-folio-ref">F° 14v · Générateur Interactif Sandbox</span>
                </div>

                <div class="volvelle-controls">
                    <div class="volvelle-controls-group">
                        <button type="button" class="btn-cartouche is-active" id="btn-pool-all">
                            Toutes les Races (8)
                        </button>
                        <button type="button" class="btn-cartouche" id="btn-pool-human">
                            Mode Illustré (Humain WebP)
                        </button>
                    </div>

                    <div class="volvelle-controls-group">
                        <button type="button" class="btn-cartouche btn-cartouche--gold" id="btn-charge-pity">
                            Charger le Grand Rituel (10/10)
                        </button>
                        <button type="button" class="btn-cartouche" id="btn-refill-ink">
                            Remplir l'Encrier (3/3)
                        </button>
                    </div>
                </div>

                <div class="volvelle-layout">
                    <div>
                        <div class="volvelle-stage">
                            {{-- Index Laiton fixe à midi --}}
                            <svg class="volvelle-pointer" viewBox="0 0 28 36" aria-hidden="true">
                                <polygon points="14,35 2,5 14,11 26,5" fill="#B8923A" stroke="#4A3A18" stroke-width="1.6"/>
                                <circle cx="14" cy="9" r="3.2" fill="#F2DEAE" stroke="#4A3A18" stroke-width="1.2"/>
                            </svg>

                            {{-- Volvelle SVG 3 anneaux concentriques (remplis par newda.js) --}}
                            <svg id="volvelle-svg" class="volvelle-svg" viewBox="0 0 500 500" role="img" aria-label="Sceau-Volvelle à trois anneaux concentriques : Pouvoirs, Armes et Races">
                                <defs>
                                    <radialGradient id="volvelleRim" cx="50%" cy="50%" r="50%">
                                        <stop offset="88%" stop-color="#EBE0C8"/>
                                        <stop offset="96%" stop-color="#C8B88F"/>
                                        <stop offset="100%" stop-color="#8A7048"/>
                                    </radialGradient>
                                </defs>
                                <circle cx="250" cy="250" r="244" fill="url(#volvelleRim)" stroke="#4A3A18" stroke-width="3"/>
                                <g id="ring-powers" class="volvelle-ring volvelle-ring--powers"></g>
                                <circle cx="250" cy="250" r="184" fill="none" stroke="#4A3A18" stroke-width="2.2"/>
                                <g id="ring-weapons" class="volvelle-ring volvelle-ring--weapons"></g>
                                <circle cx="250" cy="250" r="126" fill="none" stroke="#4A3A18" stroke-width="2.2"/>
                                <g id="ring-races" class="volvelle-ring volvelle-ring--races"></g>
                                <circle cx="250" cy="250" r="66" fill="#D9CBA8" stroke="#4A3A18" stroke-width="2.4"/>
                            </svg>

                            {{-- Rivet central : Sceau de Cire rond 1:1 unique --}}
                            <div class="volvelle-center-trigger">
                                <button type="button" id="btn-invoke-seal" class="btn-wax-seal" aria-label="Invoquer une nouvelle entité">
                                    <span>Invoquer</span>
                                    <span style="font-size: 0.62rem; opacity: 0.85;">✦ Sceau ✦</span>
                                </button>
                            </div>
                        </div>

                        <div class="volvelle-legend">
                            <span>Extérieur : Pouvoirs (arcs réels 4%–30%)</span>
                            <span>·</span>
                            <span>Médian : Armes</span>
                            <span>·</span>
                            <span>Intérieur : Races</span>
                        </div>
                    </div>

                    <div class="ritual-stage" id="ritual-stage" aria-live="polite">
                        <p class="ritual-status" id="ritual-status">Pressez le Sceau de Cire central pour aligner les trois anneaux.</p>

                        <div id="ritual-content" class="ritual-idle">
                            <svg class="ritual-idle-circle" viewBox="0 0 120 120" aria-hidden="true">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.3" stroke-dasharray="4 4"/>
                                <circle cx="60" cy="60" r="36" fill="none" stroke="currentColor" stroke-width="1"/>
                                <polygon points="60,14 68,52 106,60 68,68 60,106 52,68 14,60 52,52" fill="none" stroke="currentColor" stroke-width="1.2"/>
                            </svg>
                            <p class="folio-note" style="font-size: 0.94rem;">
                                <strong>Feuillet vierge prêt à l'impression.</strong><br>
                                Activez « Grand Rituel (10/10) » pour voir les pouvoirs Communs (<em>Feu</em>, <em>Poison</em>) disparaître en direct de l'anneau extérieur.
                            </p>
                        </div>

                        <div class="ritual-actions" id="ritual-actions" hidden>
                            <button type="button" class="btn-brass" id="btn-seal-folio">
                                Sceller dans le Grimoire
                            </button>
                            <button type="button" class="btn-cartouche" id="btn-transmute-folio">
                                Transmuter (+0 écus)
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ================================================================
                 CHAPITRE III — FOLIOS SCELLÉS & BANC D'ESSAI PAPER DOLL
                 ================================================================ --}}
            <section id="chapitre-folios" class="codex-chapter" aria-labelledby="title-folios">
                <div class="chapter-head">
                    <div>
                        <span class="chapter-numeral">Chapitre III</span>
                        <h2 id="title-folios" class="chapter-title">Folios Scellés &amp; Banc d'Essai Paper Doll</h2>
                    </div>
                    <span class="chapter-folio-ref">F° 28r · Contraste Vélin &amp; Cartes Flip 3D</span>
                </div>

                {{-- Banc d'essai Paper Doll interactif --}}
                <div class="parchment-panel bench-box">
                    <h3 class="panel-heading">
                        <span>Banc d'Essai Paper Doll — Diagnostic Auras Pâles sur Vélin</span>
                        <span class="chapter-folio-ref">Humain WebP × Armes × 8 Pouvoirs</span>
                    </h3>

                    <div class="bench-grid">
                        <div class="bench-stage" id="bench-stage">
                            <div class="codex-portrait-frame" style="max-width: 230px;">
                                <div id="bench-sigil-slot" style="position: absolute; inset: 0;">
                                    {!! $renderPowerSigilSvg('lightning', '#A67C20') !!}
                                </div>
                                <div class="codex-doll is-contrast-halo" id="bench-doll" style="--halo-pigment: rgba(166, 124, 32, 0.28);">
                                    <div class="codex-doll-halo"></div>
                                    @if ($defaultBenchLayers['race'])
                                        <img id="bench-img-race" class="codex-doll-layer codex-doll-layer--race" src="{{ $defaultBenchLayers['race'] }}" alt="Silhouette Humain">
                                    @endif
                                    <img id="bench-img-weapon" class="codex-doll-layer codex-doll-layer--weapon" src="{{ $defaultBenchLayers['weapon'] ?? '' }}" alt="Arme Paper Doll" @if (empty($defaultBenchLayers['weapon'])) hidden @endif>
                                    <img id="bench-img-aura" class="codex-doll-layer codex-doll-layer--aura {{ $defaultBenchLayers['is_pale_aura'] ? 'is-pale-aura' : '' }}" src="{{ $defaultBenchLayers['aura'] ?? '' }}" alt="Aura Paper Doll" @if (empty($defaultBenchLayers['aura'])) hidden @endif>
                                </div>
                            </div>
                        </div>

                        <div class="bench-controls">
                            <div class="bench-control-row">
                                <label for="bench-weapon" class="hud-label">Arme</label>
                                <select id="bench-weapon" class="bench-select">
                                    @foreach ($weapons as $weapon)
                                        <option value="{{ $weapon['id'] }}" @selected($weapon['id'] === 'sword')>
                                            {{ $weapon['name'] }}{{ $weapon['has_asset'] ? ' (WebP)' : ' (Sans asset)' }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="bench-power" class="hud-label">Pouvoir</label>
                                <select id="bench-power" class="bench-select">
                                    @foreach ($powers as $power)
                                        <option value="{{ $power['id'] }}" @selected($power['id'] === 'lightning')>
                                            {{ $power['name'] }} — {{ $power['pigment_name'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="bench-rarity" class="hud-label">Rareté</label>
                                <select id="bench-rarity" class="bench-select">
                                    @foreach ($rarities as $rarity)
                                        <option value="{{ $rarity['value'] }}" @selected($rarity['value'] === 'epic')>
                                            {{ $rarity['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="bench-control-row">
                                <button type="button" class="btn-cartouche is-active" id="btn-bench-backdrop">
                                    Fond : Vélin Codex (#EBE0C8)
                                </button>
                                <button type="button" class="btn-cartouche is-active" id="btn-bench-halo">
                                    Halo Contraste Auras Pâles : Actif
                                </button>
                            </div>

                            <div class="bench-diagnostic" id="bench-diagnostic">
                                <strong>Diagnostic PaperDollService :</strong> <code>human.webp</code> + <code>sword.webp</code> + <code>sword_lightning.webp</code>. Aura pâle détectée : aucun <code>mix-blend-screen</code> ; halo sépia + contour d'encre actifs.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Galerie 3D Recto / Verso --}}
                <div class="gallery-toolbar">
                    <div class="gallery-filters" role="group" aria-label="Filtrer les Folios par rareté">
                        <button type="button" class="btn-cartouche is-active" data-rarity-filter="all">Tous</button>
                        @foreach ($rarities as $rarity)
                            <button type="button" class="btn-cartouche" data-rarity-filter="{{ $rarity['value'] }}">
                                {{ $rarity['label'] }}
                            </button>
                        @endforeach
                    </div>
                    <span class="chapter-folio-ref" id="gallery-count-label">{{ count($showcaseFolios) }} Folios consignés</span>
                </div>

                <div class="folios-grid" id="folios-grid">
                    @foreach ($showcaseFolios as $folio)
                        <article class="folio-card" data-folio-id="{{ $folio['id'] }}" data-rarity="{{ $folio['rarity'] }}">
                            <div class="folio-card-inner">
                                {{-- RECTO --}}
                                <div class="folio-face folio-face--front">
                                    <div class="folio-topbar">
                                        <div class="folio-ref-group">
                                            <span class="folio-ref">FOLIO N° {{ $folio['folio_number'] }}</span>
                                            @if ($folio['is_favorite'])
                                                <span class="folio-badge-mini" title="Table d'Honneur">★ Honneur</span>
                                            @endif
                                            @if ($folio['is_locked'])
                                                <span class="folio-badge-mini folio-badge-mini--lock" title="Sceau de Garde">⛨ Garde</span>
                                            @endif
                                        </div>
                                        <span class="power-medallion tabular-nums" title="Power Score">{{ $folio['power_score'] }}</span>
                                    </div>

                                    <div class="folio-title-row">
                                        <span class="folio-lettrine" aria-hidden="true">{{ $folio['initial'] }}</span>
                                        <div>
                                            <h3 class="folio-name">{{ $folio['name'] }}</h3>
                                            <div class="folio-sub">{{ $folio['race_name'] }} · {{ $folio['weapon_name'] }}</div>
                                        </div>
                                    </div>

                                    <div class="codex-portrait-frame">
                                        {!! $renderPowerSigilSvg($folio['power_id'], $folio['power_pigment_hex']) !!}

                                        @if ($folio['has_portrait'] && ! empty($folio['layers']['race']))
                                            <div class="codex-doll {{ $folio['layers']['is_pale_aura'] ? 'is-contrast-halo' : '' }}" style="--halo-pigment: {{ $folio['power_pigment_soft'] }};">
                                                <div class="codex-doll-halo"></div>
                                                <img class="codex-doll-layer codex-doll-layer--race" src="{{ $folio['layers']['race'] }}" alt="{{ $folio['race_name'] }}" loading="lazy">
                                                @if (! empty($folio['layers']['weapon']))
                                                    <img class="codex-doll-layer codex-doll-layer--weapon" src="{{ $folio['layers']['weapon'] }}" alt="{{ $folio['weapon_name'] }}" loading="lazy">
                                                @endif
                                                @if (! empty($folio['layers']['aura']))
                                                    <img class="codex-doll-layer codex-doll-layer--aura {{ $folio['layers']['is_pale_aura'] ? 'is-pale-aura' : '' }}" src="{{ $folio['layers']['aura'] }}" alt="Aura {{ $folio['power_name'] }}" loading="lazy">
                                                @endif
                                            </div>
                                        @else
                                            <div class="heraldic-blason" data-blason-race="{{ $folio['race_id'] }}">
                                                <span class="heraldic-initial">{{ $folio['race_initial'] }}</span>
                                                {!! $renderRaceRuneSvg($folio['race_glyph']) !!}
                                                <span class="heraldic-caption">Blason {{ $folio['race_name'] }}</span>
                                                <span class="heraldic-origin">{{ $folio['weapon_name'] }} · {{ $folio['power_name'] }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <dl class="scribe-list" style="margin-top: 8px;">
                                        <div class="scribe-row"><dt>Race</dt><span class="scribe-dots"></span><dd>{{ $folio['race_name'] }}</dd></div>
                                        <div class="scribe-row"><dt>Arme</dt><span class="scribe-dots"></span><dd>{{ $folio['weapon_name'] }}</dd></div>
                                        <div class="scribe-row"><dt>Pouvoir</dt><span class="scribe-dots"></span><dd style="color: {{ $folio['power_pigment_hex'] }};">{{ $folio['power_name'] }}</dd></div>
                                    </dl>

                                    @if (! empty($folio['synergies']))
                                        @php
                                            $featuredSyn = collect($folio['synergies'])->firstWhere('is_unstable', true) ?? $folio['synergies'][0];
                                            $extraSynCount = count($folio['synergies']) - 1;
                                        @endphp
                                        <div class="folio-synergy-strip {{ ! empty($featuredSyn['is_unstable']) ? 'folio-synergy-strip--unstable' : '' }}">
                                            <span>{{ ! empty($featuredSyn['is_unstable']) ? 'Fissure Instable' : 'Accord de Sceau' }}</span>
                                            <span>{{ $featuredSyn['name'] }}@if ($extraSynCount > 0) (+{{ $extraSynCount }})@endif</span>
                                        </div>
                                    @endif

                                    <div class="folio-footer">
                                        <p class="folio-note">{{ $folio['note'] }}</p>
                                        <span class="rarity-wax-chip" style="--seal-hex: {{ $folio['seal_hex'] }};" title="{{ $folio['seal_name'] }}">
                                            {{ $folio['rarity_label'] }}
                                        </span>
                                    </div>

                                    <button type="button" class="folio-flip-btn" data-flip-folio>
                                        Consulter le Verso ↻
                                    </button>
                                </div>

                                {{-- VERSO --}}
                                <div class="folio-face folio-face--back">
                                    <div class="folio-topbar">
                                        <span class="folio-ref">VERSO · FOLIO N° {{ $folio['folio_number'] }}</span>
                                        <span class="power-medallion tabular-nums">{{ $folio['power_score'] }}</span>
                                    </div>

                                    <div class="ink-stat-list tabular-nums">
                                        <div>
                                            <div class="ink-stat-head"><span>Vitalité (PV)</span><span>{{ $folio['hp'] }} / 140</span></div>
                                            <div class="ink-meter"><div class="ink-meter-fill ink-meter-fill--hp" style="width: {{ min(100, round($folio['hp'] / 140 * 100)) }}%;"></div></div>
                                        </div>
                                        <div>
                                            <div class="ink-stat-head"><span>Force</span><span>{{ $folio['strength'] }} / 100</span></div>
                                            <div class="ink-meter"><div class="ink-meter-fill" style="width: {{ min(100, $folio['strength']) }}%;"></div></div>
                                        </div>
                                        <div>
                                            <div class="ink-stat-head"><span>Vitesse</span><span>{{ $folio['speed'] }} / 100</span></div>
                                            <div class="ink-meter"><div class="ink-meter-fill" style="width: {{ min(100, $folio['speed']) }}%;"></div></div>
                                        </div>
                                        <div>
                                            <div class="ink-stat-head"><span>Défense</span><span>{{ $folio['defense'] }} / 100</span></div>
                                            <div class="ink-meter"><div class="ink-meter-fill" style="width: {{ min(100, $folio['defense']) }}%;"></div></div>
                                        </div>
                                    </div>

                                    <div class="verso-section">
                                        <strong>Passifs :</strong> {{ $folio['race_passive'] }} — {{ $folio['race_passive_desc'] }}
                                        <br>{{ $folio['weapon_passive'] }} — {{ $folio['weapon_passive_desc'] }}
                                    </div>

                                    @if (! empty($folio['synergies']))
                                        <div class="verso-section">
                                            @foreach ($folio['synergies'] as $syn)
                                                <div>
                                                    <strong style="color: {{ ! empty($syn['is_unstable']) ? '#8A1A14' : '#8A6E20' }};">
                                                        {{ ! empty($syn['is_unstable']) ? 'Fissure Arcanique' : 'Accord de Sceau' }} · {{ $syn['name'] }} :
                                                    </strong>
                                                    {{ $syn['description'] }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <dl class="scribe-list" style="margin-top: auto;">
                                        <div class="scribe-row"><dt>Transmutation</dt><span class="scribe-dots"></span><dd class="tabular-nums">+{{ $folio['recycle_value'] }} écus</dd></div>
                                        <div class="scribe-row"><dt>Cachet</dt><span class="scribe-dots"></span><dd>{{ $folio['seal_name'] }}</dd></div>
                                    </dl>

                                    <button type="button" class="folio-flip-btn" data-flip-folio>
                                        Retourner au Recto ↺
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div id="gallery-empty-state" class="narrative-state narrative-state--empty" style="margin-top: 16px;" hidden>
                    <p id="gallery-empty-title" style="margin: 0; font-family: var(--font-scribe); font-size: 1.05rem;">
                        <strong>Folio vierge</strong> — Aucune entité n'a encore été scellée dans ce chapitre.
                    </p>
                    <p id="gallery-empty-desc" class="folio-note">Pressez le Sceau-Volvelle au Chapitre II pour imprimer votre premier héros dans le Grimoire.</p>
                    <a id="gallery-empty-cta" href="#chapitre-volvelle" class="btn-brass">Ouvrir le Sceau-Volvelle</a>
                    <button type="button" id="gallery-reset-filter-btn" class="btn-cartouche" hidden>Afficher toutes les raretés</button>
                </div>
            </section>

            {{-- ================================================================
                 CHAPITRE IV — DUEL SUR L'ÉTABLI
                 ================================================================ --}}
            <section id="chapitre-duel" class="codex-chapter" aria-labelledby="title-duel">
                <div class="chapter-head">
                    <div>
                        <span class="chapter-numeral">Chapitre IV</span>
                        <h2 id="title-duel" class="chapter-title">Duel sur l'Établi</h2>
                    </div>
                    <span class="chapter-folio-ref">F° 41v · Confrontation des Sceaux</span>
                </div>

                <div class="duel-selectors">
                    <div class="bench-control-row">
                        <label for="duel-left-select" class="hud-label">Folio Gauche</label>
                        <select id="duel-left-select" class="bench-select">
                            @foreach ($showcaseFolios as $index => $folio)
                                <option value="{{ $folio['id'] }}" @selected($index === 0)>{{ $folio['name'] }} ({{ $folio['power_score'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bench-control-row">
                        <label for="duel-right-select" class="hud-label">Folio Droit</label>
                        <select id="duel-right-select" class="bench-select">
                            @foreach ($showcaseFolios as $index => $folio)
                                <option value="{{ $folio['id'] }}" @selected($index === 4)>{{ $folio['name'] }} ({{ $folio['power_score'] }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="duel-workbench">
                    <div class="duel-arena-grid">
                        <div class="duel-folio" id="duel-card-left"></div>

                        <div class="duel-vs-medallion" aria-hidden="true">VS</div>

                        <div class="duel-folio" id="duel-card-right"></div>
                    </div>

                    <div class="duel-actions">
                        <button type="button" class="btn-brass" id="btn-run-duel">
                            Briser les Sceaux — Simuler le Duel
                        </button>
                        <button type="button" class="btn-cartouche" id="btn-reset-duel">
                            Réinitialiser l'Établi
                        </button>
                    </div>

                    <div class="duel-chronicle" id="duel-chronicle" aria-live="polite">
                        <p><em>Les deux parchemins reposent face à face sur le chêne sombre. Brisez leurs sceaux pour libérer leurs pouvoirs.</em></p>
                    </div>
                </div>
            </section>

            {{-- ================================================================
                 CHAPITRE V — ATELIER ALCHIMIQUE, BOUTONS V2 & ÉTATS NARRATIFS
                 ================================================================ --}}
            <section id="chapitre-atelier" class="codex-chapter" aria-labelledby="title-atelier">
                <div class="chapter-head">
                    <div>
                        <span class="chapter-numeral">Chapitre V</span>
                        <h2 id="title-atelier" class="chapter-title">Atelier Alchimique, Boutons V2 &amp; États</h2>
                    </div>
                    <span class="chapter-folio-ref">F° 56r · Composants &amp; Parcours Débutant</span>
                </div>

                {{-- Vitrine des 3 familles de boutons V2 --}}
                <div class="buttons-showcase">
                    <div class="button-demo-card">
                        <span class="hud-label">1. Action Principale</span>
                        <button type="button" class="btn-brass">Sceller le Destin</button>
                        <p class="folio-note">Plaque de Laiton à rivets, reprise des fermoirs du Codex. Idéale pour « Lancer le combat », « Sceller », « Confirmer ».</p>
                    </div>

                    <div class="button-demo-card">
                        <span class="hud-label">2. Actions Secondaires &amp; Filtres</span>
                        <button type="button" class="btn-cartouche">Filtrer par Rareté</button>
                        <p class="folio-note">Cartouche Enluminé à double filet et losanges d'angle. Se teinte de cinabre ou d'or transparent au survol.</p>
                    </div>

                    <div class="button-demo-card">
                        <span class="hud-label">3. Action Rituelle Unique (1:1)</span>
                        <button type="button" class="btn-wax-seal" style="width: 90px; font-size: 0.72rem;">
                            <span>Invoquer</span>
                        </button>
                        <p class="folio-note">Vrai Sceau de Cire strictement circulaire. Réservé à un seul bouton emblématique par écran.</p>
                    </div>
                </div>

                {{-- Atelier Alchimique (GameConfig::workshop()) --}}
                <div class="workshop-grid">
                    <div class="parchment-panel">
                        <h3 class="panel-heading">
                            <span>{{ $workshop['rare_magnet']['name'] }}</span>
                            <span class="chapter-folio-ref">+{{ $workshop['rare_magnet']['bonus_per_level'] }}% Mythique / palier</span>
                        </h3>
                        <p class="folio-note" style="font-size: 0.88rem;">
                            Déplace à chaque niveau {{ $workshop['rare_magnet']['bonus_per_level'] }} points de pourcentage des pouvoirs Communs vers le Cercle Mythique. Cliquez un palier pour prévisualiser le coût réel :
                        </p>
                        <div class="workshop-tiers" id="workshop-magnet-tiers">
                            @foreach ($workshop['rare_magnet']['costs'] as $idx => $cost)
                                <button type="button" class="tier-chip {{ $idx === 0 ? 'is-bought' : '' }}" data-magnet-tier="{{ $idx + 1 }}" data-magnet-cost="{{ $cost }}">
                                    Palier {{ $idx + 1 }} · {{ number_format($cost, 0, ',', ' ') }} écus
                                </button>
                            @endforeach
                        </div>
                        <p class="folio-note" id="workshop-magnet-readout">
                            Simulation palier 1 : <strong>+3%</strong> vers Gravité / Téléportation / Invisibilité (coût réel : 200 écus).
                        </p>
                    </div>

                    <div class="parchment-panel">
                        <h3 class="panel-heading">
                            <span>Autres Améliorations de l'Atelier</span>
                            <span class="chapter-folio-ref">GameConfig::workshop()</span>
                        </h3>
                        <dl class="scribe-list tabular-nums">
                            <div class="scribe-row">
                                <dt>{{ $workshop['hero_forge']['name'] }}</dt>
                                <span class="scribe-dots"></span>
                                <dd>+{{ $workshop['hero_forge']['bonus_hp'] }} PV / +{{ $workshop['hero_forge']['bonus_str'] }} Force ({{ $workshop['hero_forge']['costs'][0] }}–{{ end($workshop['hero_forge']['costs']) }} écus)</dd>
                            </div>
                            <div class="scribe-row">
                                <dt>{{ $workshop['arena_bonus']['name'] }}</dt>
                                <span class="scribe-dots"></span>
                                <dd>×{{ implode(' / ×', $workshop['arena_bonus']['multipliers']) }} gains arène</dd>
                            </div>
                            <div class="scribe-row">
                                <dt>{{ $workshop['emergency_spin']['name'] }}</dt>
                                <span class="scribe-dots"></span>
                                <dd>{{ $workshop['emergency_spin']['costs'][0] }} puis {{ $workshop['emergency_spin']['costs'][1] }} écus / jour</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- 3 États Narratifs (dont expérience débutant player@wheelborn.test) --}}
                <div class="states-grid">
                    <div class="narrative-state narrative-state--empty">
                        <span class="hud-label">État Vide · Débutant (player@wheelborn.test)</span>
                        <p style="margin: 0; font-family: var(--font-scribe); font-size: 0.96rem;">
                            « Folio vierge — Aucune entité n'a encore été scellée dans ce chapitre. »
                        </p>
                        <a href="#chapitre-volvelle" class="btn-brass" style="padding: 8px 18px; font-size: 0.8rem;">
                            Invoquer mon 1er Héros
                        </a>
                    </div>

                    <div class="narrative-state">
                        <span class="hud-label">Chargement · Invocation</span>
                        <svg class="loading-sigil-svg" viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="trace-loop" cx="60" cy="60" r="46" fill="none" stroke="currentColor" stroke-width="1.6"/>
                            <polygon class="trace-loop" points="60,18 96,82 24,82" fill="none" stroke="currentColor" stroke-width="1.4"/>
                            <polygon class="trace-loop" points="60,102 24,38 96,38" fill="none" stroke="currentColor" stroke-width="1.4"/>
                        </svg>
                        <p style="margin: 0; font-family: var(--font-scribe); font-style: italic;">
                            « Traçage du sceau en cours… »
                        </p>
                    </div>

                    <div class="narrative-state narrative-state--error">
                        <span class="hud-label" style="color: var(--wax);">Erreur · Rupture</span>
                        <p style="margin: 0; font-family: var(--font-scribe); font-size: 0.96rem;">
                            « Le sceau s'est brisé — L'invocation a échoué. »
                        </p>
                        <span class="folio-note" style="color: var(--wax);">Cadre cinabre double-filet, sans modale rouge fluo.</span>
                    </div>
                </div>
            </section>

            <footer class="codex-footer">
                <span>Codex Wheelborn — Prototype DA autonome (Option B : <code>public/css/newda.css</code> &amp; <code>public/js/newda.js</code>)</span>
                <span>Aucune écriture en base de données · En-tête <code>X-Robots-Tag: noindex, nofollow</code></span>
            </footer>
        </div>

        {{-- Tranche dorée droite --}}
        <div class="codex-gilded-edge" aria-hidden="true"></div>
    </main>

    {{-- 5 Signets de Cuir ancrés au bord droit du Codex (rubans supérieurs sous 1180px) --}}
    <nav class="codex-bookmarks" aria-label="Chapitres du Codex">
        <a class="bookmark-tab is-active" href="#chapitre-concept" data-chapter-link="chapitre-concept">
            <span class="bookmark-roman">I.</span> Concept
        </a>
        <a class="bookmark-tab" href="#chapitre-volvelle" data-chapter-link="chapitre-volvelle">
            <span class="bookmark-roman">II.</span> Volvelle
        </a>
        <a class="bookmark-tab" href="#chapitre-folios" data-chapter-link="chapitre-folios">
            <span class="bookmark-roman">III.</span> Folios
        </a>
        <a class="bookmark-tab" href="#chapitre-duel" data-chapter-link="chapitre-duel">
            <span class="bookmark-roman">IV.</span> Duel
        </a>
        <a class="bookmark-tab" href="#chapitre-atelier" data-chapter-link="chapitre-atelier">
            <span class="bookmark-roman">V.</span> Atelier
        </a>
    </nav>
</div>

<script type="application/json" id="codex-bootstrap-data">@json($codexPayload)</script>
</body>
</html>
