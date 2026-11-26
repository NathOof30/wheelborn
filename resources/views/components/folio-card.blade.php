@props([
    'card',
    'showOwnerActions' => false,
    'animateStamp' => false,
    'recycleMethod' => 'recycleCharacter',
    'favoriteMethod' => 'toggleFavorite',
    'lockMethod' => 'toggleLock',
    'folioKey' => null,
])

@php
    $presenter = app(\App\View\Presenters\FolioCardPresenter::class);
    $iconRegistry = app(\App\Support\CodexIconRegistry::class);
    $folio = $presenter->present($card);

    $synergies = $folio['synergies'] ?? [];
    $synergyCount = count($synergies);
    $hasUnstable = collect($synergies)->contains('is_unstable', true);
    $synergyTooltipTitle = $synergyCount === 1
        ? (string) ($synergies[0]['name'] ?? 'Synergie')
        : $synergyCount.' Synergies actives';
    $synergyTooltipDesc = collect($synergies)
        ->map(fn (array $s): string => $synergyCount > 1
            ? ($s['name'] ?? '').' : '.($s['description'] ?? '')
            : (string) ($s['description'] ?? ''))
        ->filter()
        ->implode(' • ');
    $synergyEyebrow = $hasUnstable ? 'SYNERGIE · INSTABLE' : 'SYNERGIE · ACCORD';

    $hpVal = (int) ($folio['hp'] ?? 0);
    $hpMax = max(140, $hpVal);
    $hpPct = min(100, (int) round(($hpVal / $hpMax) * 100));

    $strVal = (int) ($folio['strength'] ?? 0);
    $strMax = max(100, $strVal);
    $strPct = min(100, (int) round(($strVal / $strMax) * 100));

    $spdVal = (int) ($folio['speed'] ?? 0);
    $spdMax = max(100, $spdVal);
    $spdPct = min(100, (int) round(($spdVal / $spdMax) * 100));

    $defVal = (int) ($folio['defense'] ?? 0);
    $defMax = max(100, $defVal);
    $defPct = min(100, (int) round(($defVal / $defMax) * 100));

    $abilities = $folio['abilities'] ?? [];
    $wins = (int) ($folio['wins'] ?? 0);
    $losses = (int) ($folio['losses'] ?? 0);
    $battles = $wins + $losses;
    $winrate = $folio['winrate'] ?? ($battles > 0 ? (int) round(($wins / $battles) * 100) : null);

    $isLuckySpin = ! empty($folio['is_lucky_spin']);
    $isFavorite = ! empty($folio['is_favorite']);
    $isLocked = ! empty($folio['is_locked']);
    $isActiveFighter = ! empty($folio['is_active_fighter']);
    $isProtected = $isLocked || $isFavorite || $isActiveFighter;
    $isTeleportAura = ! empty($folio['layers']['is_teleportation_aura']);
    $stampClass = $animateStamp ? 'wax-stamp-drop' : '';
    $resolvedKey = $folioKey ?: 'folio-'.$folio['id'];
    $safeNameForConfirm = str_replace(["'", '"', "\n", "\r"], ['’', '', ' ', ' '], (string) $folio['name']);
@endphp

<article
    {{ $attributes->merge(['class' => 'folio-card']) }}
    data-folio-id="{{ $folio['id'] }}"
    data-folio-key="{{ $resolvedKey }}"
    data-rarity="{{ $folio['rarity'] }}"
    data-interactive="{{ $showOwnerActions ? 'true' : 'false' }}"
    tabindex="0"
    aria-roledescription="carte recto-verso"
    aria-label="{{ $folio['name'] }} — cliquer pour retourner"
>
    <div class="folio-card-inner">
        {{-- RECTO --}}
        <div class="folio-face folio-face--front" aria-hidden="false">
            <div class="folio-head">
                <div class="folio-title-block">
                    <h3 class="folio-name">{{ $folio['name'] }}</h3>
                    @if ($synergyCount > 0)
                        <span
                            class="syn-seal-badge {{ $hasUnstable ? 'is-unstable' : '' }}"
                            tabindex="0"
                            data-no-flip
                            data-tooltip-eyebrow="{{ $synergyEyebrow }}"
                            data-tooltip-title="{{ $synergyTooltipTitle }}"
                            data-tooltip-desc="{{ $synergyTooltipDesc }}"
                            data-tooltip-tone="{{ $hasUnstable ? 'unstable' : 'synergy' }}"
                            aria-label="{{ $synergyTooltipTitle }}"
                        >
                            <span aria-hidden="true">{{ $hasUnstable ? '⚡' : '✦' }}</span>
                            @if ($synergyCount > 1)
                                <small class="syn-seal-count tabular-nums">{{ $synergyCount }}</small>
                            @endif
                        </span>
                    @endif
                </div>
                <span class="power-medallion tabular-nums {{ $stampClass }}" title="Power Score">{{ $folio['power_score'] }}</span>
            </div>

            <div class="codex-portrait-frame">
                {!! $iconRegistry->renderPowerSigil($folio['power_id'], $folio['power_pigment_hex']) !!}

                @if ($isLuckySpin)
                    <span
                        class="folio-lucky-mark"
                        data-lucky-mark
                        data-no-flip
                        tabindex="0"
                        data-tooltip-eyebrow="SUPER SPIN"
                        data-tooltip-title="Pull chanceux"
                        data-tooltip-desc="Invoqué lors d'un Super Spin 10/10 aux chances d'élite garanties."
                        data-tooltip-tone="lucky"
                        aria-label="Pull chanceux (Super Spin)"
                    >
                        <svg viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M10 1.5 L12.1 7.9 L18.5 10 L12.1 12.1 L10 18.5 L7.9 12.1 L1.5 10 L7.9 7.9 Z" fill="currentColor"/>
                        </svg>
                    </span>
                @endif

                @if ($folio['has_portrait'] && ! empty($folio['layers']['race']))
                    <div class="codex-doll {{ $folio['layers']['is_pale_aura'] ? 'is-contrast-halo' : '' }}" style="--halo-pigment: {{ $folio['power_pigment_soft'] }};">
                        <div class="codex-doll-halo"></div>
                        <img class="codex-doll-layer codex-doll-layer--race" src="{{ $folio['layers']['race'] }}" alt="{{ $folio['race_name'] }}" loading="lazy">
                        @if (! empty($folio['layers']['weapon']))
                            <img class="codex-doll-layer codex-doll-layer--weapon" src="{{ $folio['layers']['weapon'] }}" alt="{{ $folio['weapon_name'] }}" loading="lazy">
                        @endif
                        @if (! empty($folio['layers']['aura']))
                            <img class="codex-doll-layer codex-doll-layer--aura {{ $folio['layers']['is_pale_aura'] ? 'is-pale-aura' : '' }} {{ $isTeleportAura ? 'is-teleport-aura' : '' }}" src="{{ $folio['layers']['aura'] }}" alt="Aura {{ $folio['power_name'] }}" loading="lazy">
                        @endif
                    </div>
                @else
                    <div class="heraldic-blason" data-blason-race="{{ $folio['race_id'] }}">
                        <span class="heraldic-initial">{{ $folio['race_initial'] }}</span>
                        {!! $iconRegistry->renderInlineIcon('races', $folio['race_id'], 'heraldic-rune', $folio['race_name']) !!}
                    </div>
                @endif
            </div>

            <div class="folio-pills" aria-label="Attributs">
                <span class="trait-pill" title="Race : {{ $folio['race_name'] }}">
                    {!! $iconRegistry->renderInlineIcon('races', $folio['race_id'], 'trait-pill-icon', $folio['race_name']) !!}
                    <span>{{ $folio['race_name'] }}</span>
                </span>
                <span class="trait-pill" title="Arme : {{ $folio['weapon_name'] }}">
                    {!! $iconRegistry->renderInlineIcon('weapons', $folio['weapon_id'], 'trait-pill-icon', $folio['weapon_name']) !!}
                    <span>{{ $folio['weapon_name'] }}</span>
                </span>
                <span class="trait-pill trait-pill--power" style="--power-hex: {{ $folio['power_pigment_hex'] }};" title="Pouvoir : {{ $folio['power_name'] }}">
                    {!! $iconRegistry->renderInlineIcon('powers', $folio['power_id'], 'trait-pill-icon', $folio['power_name']) !!}
                    <span>{{ $folio['power_name'] }}</span>
                </span>
            </div>

            <div class="folio-foot folio-foot--front">
                <span class="rarity-seal-pill {{ $stampClass }}" style="--seal-hex: {{ $folio['seal_hex'] }};" title="{{ $folio['rarity_label'] }}">
                    <span class="rarity-wax-dot" aria-hidden="true"></span>
                    <span>{{ $folio['rarity_label'] }}</span>
                </span>

                <div class="folio-flags" data-folio-front-flags>
                    @if ($isFavorite)
                        <span class="folio-flag folio-flag--fav" title="Favori" aria-label="Favori">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><polygon points="10,1.8 12.6,7.1 18.4,7.9 14.2,12 15.2,17.8 10,15 4.8,17.8 5.8,12 1.6,7.9 7.4,7.1" fill="currentColor"/></svg>
                        </span>
                    @endif
                    @if ($isLocked)
                        <span class="folio-flag folio-flag--lock" title="Verrouillé" aria-label="Verrouillé">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><rect x="4.5" y="9" width="11" height="8.5" rx="1.8" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7 9 V6.4 C7 4.4 8.3 3 10 3 C11.7 3 13 4.4 13 6.4 V9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="10" cy="13.2" r="1.2" fill="currentColor"/></svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- VERSO --}}
        <div class="folio-face folio-face--back" aria-hidden="true" inert>
            <div class="folio-head">
                <div class="folio-title-block">
                    <h3 class="folio-name">{{ $folio['name'] }}</h3>
                    @if ($synergyCount > 0)
                        <span
                            class="syn-seal-badge {{ $hasUnstable ? 'is-unstable' : '' }}"
                            tabindex="-1"
                            data-no-flip
                            data-tooltip-eyebrow="{{ $synergyEyebrow }}"
                            data-tooltip-title="{{ $synergyTooltipTitle }}"
                            data-tooltip-desc="{{ $synergyTooltipDesc }}"
                            data-tooltip-tone="{{ $hasUnstable ? 'unstable' : 'synergy' }}"
                            aria-label="{{ $synergyTooltipTitle }}"
                        >
                            <span aria-hidden="true">{{ $hasUnstable ? '⚡' : '✦' }}</span>
                            @if ($synergyCount > 1)
                                <small class="syn-seal-count tabular-nums">{{ $synergyCount }}</small>
                            @endif
                        </span>
                    @endif
                </div>
                <span class="power-medallion tabular-nums">{{ $folio['power_score'] }}</span>
            </div>

            <div class="stat-bars tabular-nums">
                <div class="stat-bar-row">
                    <span class="stat-bar-label" title="Points de Vie">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 17 C4.5 13 2.5 9.5 2.5 6.5 C2.5 4 4.5 2.5 6.8 2.5 C8.3 2.5 9.4 3.3 10 4.4 C10.6 3.3 11.7 2.5 13.2 2.5 C15.5 2.5 17.5 4 17.5 6.5 C17.5 9.5 15.5 13 10 17 Z" fill="currentColor"/></svg>
                        <span aria-hidden="true">PV</span>
                        <span class="sr-only">Points de Vie</span>
                    </span>
                    <div class="stat-track"><div class="stat-fill stat-fill--hp" style="width: {{ $hpPct }}%;"></div></div>
                    <span class="stat-val">{{ $hpVal }}<small>/{{ $hpMax }}</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label" title="Force">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L13 6 V13 H7 V6 Z M5 13 H15 M10 13 V18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                        <span aria-hidden="true">FOR</span>
                        <span class="sr-only">Force</span>
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ $strPct }}%;"></div></div>
                    <span class="stat-val">{{ $strVal }}<small>/{{ $strMax }}</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label" title="Vitesse">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><polygon points="11.5,2 4.5,11 9.5,11 8.5,18 15.5,9 10.5,9" fill="currentColor"/></svg>
                        <span aria-hidden="true">VIT</span>
                        <span class="sr-only">Vitesse</span>
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ $spdPct }}%;"></div></div>
                    <span class="stat-val">{{ $spdVal }}<small>/{{ $spdMax }}</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label" title="Défense">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L16.5 4.8 V10 C16.5 14 13.5 16.8 10 18 C6.5 16.8 3.5 14 3.5 10 V4.8 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>
                        <span aria-hidden="true">DEF</span>
                        <span class="sr-only">Défense</span>
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ $defPct }}%;"></div></div>
                    <span class="stat-val">{{ $defVal }}<small>/{{ $defMax }}</small></span>
                </div>
            </div>

            <ul class="verso-abilities" aria-label="Capacités et attaques">
                @foreach ($abilities as $ability)
                    @php
                        $rowClasses = ['verso-ability-row', 'verso-ability-row--'.$ability['kind']];
                        if (! empty($ability['is_unstable'])) {
                            $rowClasses[] = 'is-unstable';
                        }
                        if (! empty($ability['is_mythic'])) {
                            $rowClasses[] = 'is-mythic';
                        }
                    @endphp
                    <li
                        class="{{ implode(' ', $rowClasses) }}"
                        tabindex="-1"
                        data-no-flip
                        data-tooltip-eyebrow="{{ $ability['tooltip_eyebrow'] }}"
                        data-tooltip-title="{{ $ability['tooltip_title'] }}"
                        data-tooltip-desc="{{ $ability['tooltip_desc'] }}"
                        data-tooltip-tone="{{ $ability['tooltip_tone'] ?? 'default' }}"
                        @if (! empty($ability['accent_hex']))
                            data-tooltip-hex="{{ $ability['accent_hex'] }}"
                            style="--ability-accent: {{ $ability['accent_hex'] }};"
                        @endif
                    >
                        <span class="verso-ability-icon" aria-hidden="true">
                            @if (! empty($ability['category']) && ! empty($ability['icon_id']))
                                {!! $iconRegistry->renderInlineIcon($ability['category'], $ability['icon_id'], 'ability-svg-icon', $ability['label']) !!}
                            @else
                                <span class="ability-glyph">{{ ! empty($ability['is_unstable']) ? '⚡' : '✦' }}</span>
                            @endif
                        </span>
                        <span class="verso-ability-name">{{ $ability['label'] }}</span>
                        <span class="verso-ability-meta">{{ $ability['meta'] }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="folio-foot folio-foot--back">
                @if ($battles > 0)
                    <span class="folio-record tabular-nums" title="{{ $wins }} victoires · {{ $losses }} défaites ({{ $winrate }}%)">
                        {{ $wins }}V · {{ $losses }}D <small>{{ $winrate }}%</small>
                    </span>
                @elseif (! $showOwnerActions)
                    <span class="folio-record folio-record--empty">Inédit</span>
                @else
                    <span class="folio-record folio-record--empty">0 duel</span>
                @endif

                @if ($showOwnerActions)
                    <div class="folio-verso-actions" data-no-flip>
                        <button
                            type="button"
                            wire:click.stop="{{ $favoriteMethod }}('{{ $folio['id'] }}')"
                            wire:loading.attr="disabled"
                            class="folio-act-btn folio-act-btn--fav {{ $isFavorite ? 'is-active' : '' }}"
                            data-no-flip
                            tabindex="-1"
                            aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
                            aria-label="{{ $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                            title="{{ $isFavorite ? 'Retirer des favoris' : 'Favori (max 3)' }}"
                        >
                            <svg viewBox="0 0 20 20" aria-hidden="true"><polygon points="10,1.8 12.6,7.1 18.4,7.9 14.2,12 15.2,17.8 10,15 4.8,17.8 5.8,12 1.6,7.9 7.4,7.1" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        </button>
                        <button
                            type="button"
                            wire:click.stop="{{ $lockMethod }}('{{ $folio['id'] }}')"
                            wire:loading.attr="disabled"
                            class="folio-act-btn folio-act-btn--lock {{ $isLocked ? 'is-active' : '' }}"
                            data-no-flip
                            tabindex="-1"
                            aria-pressed="{{ $isLocked ? 'true' : 'false' }}"
                            aria-label="{{ $isLocked ? 'Déverrouiller' : 'Verrouiller' }}"
                            title="{{ $isLocked ? 'Déverrouiller' : 'Verrouiller' }}"
                        >
                            <svg viewBox="0 0 20 20" aria-hidden="true"><rect x="4.5" y="9" width="11" height="8.5" rx="1.8" fill="{{ $isLocked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.7"/><path d="M7 9 V6.4 C7 4.4 8.3 3 10 3 C11.7 3 13 4.4 13 6.4 V9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button>
                        @if ($isProtected)
                            <button
                                type="button"
                                class="folio-act-transmute tabular-nums is-protected"
                                data-no-flip
                                tabindex="-1"
                                disabled
                                title="Protégé (verrouillé, favori ou actif en arène)"
                            >
                                <span>Protégé</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click.stop="{{ $recycleMethod }}('{{ $folio['id'] }}')"
                                wire:loading.attr="disabled"
                                wire:confirm="Voulez-vous vraiment recycler {{ $safeNameForConfirm }} pour +{{ $folio['recycle_value'] }} 🪙 ?"
                                class="folio-act-transmute tabular-nums"
                                data-no-flip
                                tabindex="-1"
                                title="Recycler pour +{{ $folio['recycle_value'] }} 🪙"
                            >
                                <span>+{{ $folio['recycle_value'] }} 🪙</span>
                            </button>
                        @endif
                    </div>
                @else
                    <span class="recycle-pill tabular-nums" title="Valeur de recyclage">
                        <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7.5" fill="none" stroke="currentColor" stroke-width="1.9"/><polygon points="10,5.5 13,10 10,14.5 7,10" fill="currentColor"/></svg>
                        +{{ $folio['recycle_value'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>
