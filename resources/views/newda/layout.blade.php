<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle ?? 'Wheelborn' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Crimson+Pro:ital,wght@0,500;0,600;0,700;1,500&family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/newda.css') }}">
    <script src="{{ asset('js/newda.js') }}" defer></script>
</head>
<body class="codex-body" data-codex-page="{{ $pageId }}">

<header class="codex-navbar" role="banner">
    <div class="codex-navbar-inner">
        <a href="{{ route('newda.invocation') }}" class="codex-brand" aria-label="Wheelborn — Tirage">
            <svg class="codex-brand-crest" viewBox="0 0 36 36" aria-hidden="true">
                <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="2"/>
                <circle cx="18" cy="18" r="5" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M18 3 V13 M18 23 V33 M3 18 H13 M23 18 H33 M7.4 7.4 L14.5 14.5 M21.5 21.5 L28.6 28.6 M28.6 7.4 L21.5 14.5 M14.5 21.5 L7.4 28.6" stroke="currentColor" stroke-width="1.6"/>
            </svg>
            <span class="codex-brand-name">Wheelborn</span>
        </a>

        <nav class="codex-nav" aria-label="Navigation principale">
            <a href="{{ route('newda.invocation') }}"
               class="codex-nav-link {{ $pageId === 'invocation' ? 'is-active' : '' }}"
               @if ($pageId === 'invocation') aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.9"/>
                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M12 3 V9 M12 15 V21 M3 12 H9 M15 12 H21" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Tirage</span>
            </a>

            <a href="{{ route('newda.grimoire') }}"
               class="codex-nav-link {{ $pageId === 'grimoire' ? 'is-active' : '' }}"
               @if ($pageId === 'grimoire') aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5.5 C7 4.5 10 5 12 6.5 C14 5 17 4.5 20 5.5 V18.5 C17 17.5 14 18 12 19.5 C10 18 7 17.5 4 18.5 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <line x1="12" y1="6.5" x2="12" y2="19.5" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Grimoire</span>
                <span class="nav-count tabular-nums" id="nav-grimoire-count">
                    {{ $connectedMeta !== null ? $connectedMeta['folios_count'] : 6 }}
                </span>
            </a>

            <a href="{{ route('newda.arena') }}"
               class="codex-nav-link {{ $pageId === 'arena' ? 'is-active' : '' }}"
               @if ($pageId === 'arena') aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Arène</span>
            </a>

            <a href="{{ route('newda.leaderboard') }}"
               class="codex-nav-link {{ $pageId === 'leaderboard' ? 'is-active' : '' }}"
               @if ($pageId === 'leaderboard') aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 17 L6 8 L10 12 L12 6 L14 12 L18 8 L20 17 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <line x1="5" y1="20" x2="19" y2="20" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                </svg>
                <span>Rang</span>
            </a>

            <a href="{{ route('newda.workshop') }}"
               class="codex-nav-link {{ $pageId === 'workshop' ? 'is-active' : '' }}"
               @if ($pageId === 'workshop') aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 10 H17 L20 13 H15 L17 19 H7 L9 13 H4 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <path d="M9 6 H15 V10 H9 Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Atelier</span>
            </a>
        </nav>

        <div class="codex-hud tabular-nums" aria-label="Ressources du joueur">
            <span class="hud-chip" id="hud-chip-ink" title="Gouttes d'encre ({{ $hud['spin_charges'] }}/{{ $hud['spin_max'] }})">
                <svg class="hud-icon" viewBox="0 0 18 22" aria-hidden="true">
                    <path d="M9 1.5 C9 1.5 2.5 10 2.5 14.2 C2.5 17.8 5.4 20.5 9 20.5 C12.6 20.5 15.5 17.8 15.5 14.2 C15.5 10 9 1.5 9 1.5 Z" fill="currentColor"/>
                </svg>
                <span id="hud-ink-text">{{ $hud['spin_charges'] }}/{{ $hud['spin_max'] }}</span>
            </span>

            <span class="hud-chip {{ $hud['pity_counter'] >= $hud['pity_threshold'] ? 'is-lucky' : '' }}" id="hud-chip-pity" title="Grand Rituel ({{ $hud['pity_counter'] }}/{{ $hud['pity_threshold'] }})">
                <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true">
                    <polygon points="10,1.5 12.6,7.4 18.5,10 12.6,12.6 10,18.5 7.4,12.6 1.5,10 7.4,7.4" fill="currentColor"/>
                </svg>
                <span id="hud-pity-text">{{ $hud['pity_counter'] }}/{{ $hud['pity_threshold'] }}</span>
            </span>

            <span class="hud-chip hud-chip--gold" id="hud-chip-coins" title="Écus d'or">
                <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true">
                    <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                    <polygon points="10,5.5 13,10 10,14.5 7,10" fill="currentColor"/>
                </svg>
                <span id="hud-coins">{{ number_format($hud['coins'], 0, ',', ' ') }}</span>
            </span>

            <span class="hud-chip hud-chip--hide-sm" title="{{ $hud['league_label'] }} · {{ number_format($hud['elo'], 0, ',', ' ') }} Élo">
                <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M10 2 L17 5 V10.5 C17 14.5 13.8 17.3 10 18.5 C6.2 17.3 3 14.5 3 10.5 V5 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                </svg>
                <span id="hud-elo">{{ number_format($hud['elo'], 0, ',', ' ') }}</span>
            </span>

            <span class="hud-chip hud-chip--hide-md" title="Niveau {{ $hud['level'] }} · {{ $hud['level_title'] }}">
                <span id="hud-level">Nv.{{ $hud['level'] }}</span>
            </span>
        </div>
    </div>
</header>

<div class="codex-stage">
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
            @yield('content')
        </div>

        <div class="codex-gilded-edge" aria-hidden="true"></div>
    </main>
</div>

<div class="sandbox-dock">
    <button
        type="button"
        id="btn-sandbox-toggle"
        class="sandbox-trigger"
        aria-expanded="false"
        aria-controls="sandbox-drawer"
        title="Bac à sable"
    >
        <svg class="sandbox-gear-icon" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M12 2.5 V5.2 M12 18.8 V21.5 M2.5 12 H5.2 M18.8 12 H21.5 M5.3 5.3 L7.2 7.2 M16.8 16.8 L18.7 18.7 M18.7 5.3 L16.8 7.2 M7.2 16.8 L5.3 18.7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Bac à sable</span>
    </button>

    <aside id="sandbox-drawer" class="sandbox-drawer" aria-label="Outils bac à sable" hidden>
        <div class="sandbox-drawer-head">
            <span class="sandbox-drawer-title">Bac à sable</span>
            <button type="button" class="sandbox-close" id="btn-sandbox-close" aria-label="Fermer">&times;</button>
        </div>

        <div class="sandbox-group">
            <span class="sandbox-group-label">Tirage</span>
            <div class="sandbox-btn-row">
                <button type="button" class="sandbox-chip is-active" data-sandbox-pool="all">8 Races</button>
                <button type="button" class="sandbox-chip" data-sandbox-pool="human">Humain WebP</button>
            </div>
        </div>

        <div class="sandbox-group">
            <span class="sandbox-group-label">Ressources</span>
            <div class="sandbox-btn-row">
                <button type="button" class="sandbox-chip sandbox-chip--gold" id="btn-charge-pity">Pity 10/10</button>
                <button type="button" class="sandbox-chip" id="btn-refill-ink">Encrier 3/3</button>
                <button type="button" class="sandbox-chip" id="btn-add-coins">+500 Écus</button>
            </div>
        </div>

        @if ($connectedMeta !== null)
            <div class="sandbox-group">
                <span class="sandbox-group-label">Collection</span>
                <div class="sandbox-btn-row">
                    <button type="button" class="sandbox-chip is-active" data-source-toggle="connected">
                        Mon Grimoire (<span data-connected-count>{{ $connectedMeta['folios_count'] }}</span>)
                    </button>
                    <button type="button" class="sandbox-chip" data-source-toggle="showcase">
                        Vitrine (<span data-showcase-count>6</span>)
                    </button>
                </div>
            </div>
        @endif

        <div class="sandbox-footer">
            <button type="button" class="sandbox-reset-btn" id="btn-sandbox-reset">
                Réinitialiser la session
            </button>
        </div>
    </aside>
</div>

<script id="codex-bootstrap-data" type="application/json">@json($codexPayload)</script>
</body>
</html>
