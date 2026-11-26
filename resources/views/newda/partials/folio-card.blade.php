@php
    /** @var array<string, mixed> $folio */
    /** @var \App\Support\NewDaIconRegistry $iconRegistry */
    $synergies = $folio['synergies'] ?? [];
    $featuredSyn = collect($synergies)->firstWhere('is_unstable', true) ?? ($synergies[0] ?? null);
    $extraSynCount = max(0, count($synergies) - 1);
    $hpPct = min(100, (int) round(($folio['hp'] / 140) * 100));
@endphp
<article class="folio-card" data-folio-id="{{ $folio['id'] }}" data-rarity="{{ $folio['rarity'] }}">
    <div class="folio-card-inner">
        <div class="folio-face folio-face--front">
            <div class="folio-head">
                <div class="folio-title-block">
                    <h3 class="folio-name">{{ $folio['name'] }}</h3>
                    <div class="folio-flags">
                        @if ($folio['is_favorite'])
                            <span class="folio-flag folio-flag--fav" title="Favori" aria-label="Favori">★</span>
                        @endif
                        @if ($folio['is_locked'])
                            <span class="folio-flag folio-flag--lock" title="Verrouillé" aria-label="Verrouillé">⛨</span>
                        @endif
                    </div>
                </div>
                <span class="power-medallion tabular-nums" title="Power Score">{{ $folio['power_score'] }}</span>
            </div>

            <div class="codex-portrait-frame">
                {!! $iconRegistry->renderPowerSigil($folio['power_id'], $folio['power_pigment_hex']) !!}

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

            <div class="folio-foot">
                <div class="folio-foot-left">
                    <span class="rarity-seal-pill" style="--seal-hex: {{ $folio['seal_hex'] }};" title="{{ $folio['rarity_label'] }}">
                        <span class="rarity-dot" aria-hidden="true"></span>
                        <span>{{ $folio['rarity_label'] }}</span>
                    </span>
                    @if ($featuredSyn !== null)
                        <span class="syn-chip {{ ! empty($featuredSyn['is_unstable']) ? 'syn-chip--unstable' : '' }}" title="{{ $featuredSyn['name'] }}">
                            {{ ! empty($featuredSyn['is_unstable']) ? '⚡' : '✦' }} {{ $featuredSyn['name'] }}@if ($extraSynCount > 0) +{{ $extraSynCount }}@endif
                        </span>
                    @endif
                </div>
                <button type="button" class="folio-flip-icon" data-flip-folio aria-label="Voir les statistiques de {{ $folio['name'] }}" title="Stats &amp; Verso">
                    ↻
                </button>
            </div>
        </div>

        <div class="folio-face folio-face--back">
            <div class="folio-head">
                <h3 class="folio-name">{{ $folio['name'] }}</h3>
                <span class="power-medallion tabular-nums">{{ $folio['power_score'] }}</span>
            </div>

            <div class="stat-bars tabular-nums">
                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 17 C4.5 13 2.5 9.5 2.5 6.5 C2.5 4 4.5 2.5 6.8 2.5 C8.3 2.5 9.4 3.3 10 4.4 C10.6 3.3 11.7 2.5 13.2 2.5 C15.5 2.5 17.5 4 17.5 6.5 C17.5 9.5 15.5 13 10 17 Z" fill="currentColor"/></svg>
                        PV
                    </span>
                    <div class="stat-track"><div class="stat-fill stat-fill--hp" style="width: {{ $hpPct }}%;"></div></div>
                    <span class="stat-val">{{ $folio['hp'] }}<small>/140</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L13 6 V13 H7 V6 Z M5 13 H15 M10 13 V18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                        FOR
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ min(100, $folio['strength']) }}%;"></div></div>
                    <span class="stat-val">{{ $folio['strength'] }}<small>/100</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><polygon points="11.5,2 4.5,11 9.5,11 8.5,18 15.5,9 10.5,9" fill="currentColor"/></svg>
                        VIT
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ min(100, $folio['speed']) }}%;"></div></div>
                    <span class="stat-val">{{ $folio['speed'] }}<small>/100</small></span>
                </div>

                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L16.5 4.8 V10 C16.5 14 13.5 16.8 10 18 C6.5 16.8 3.5 14 3.5 10 V4.8 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>
                        DEF
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width: {{ min(100, $folio['defense']) }}%;"></div></div>
                    <span class="stat-val">{{ $folio['defense'] }}<small>/100</small></span>
                </div>
            </div>

            <div class="verso-tags">
                <span class="verso-tag">{{ $folio['race_passive'] }}</span>
                <span class="verso-tag">{{ $folio['weapon_passive'] }}</span>
                @foreach ($synergies as $syn)
                    <span class="verso-tag {{ ! empty($syn['is_unstable']) ? 'verso-tag--unstable' : 'verso-tag--synergy' }}">
                        {{ ! empty($syn['is_unstable']) ? '⚡' : '✦' }} {{ $syn['name'] }}
                    </span>
                @endforeach
            </div>

            <div class="folio-foot">
                <span class="recycle-pill tabular-nums" title="Valeur de transmutation">
                    <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7.5" fill="none" stroke="currentColor" stroke-width="1.9"/><polygon points="10,5.5 13,10 10,14.5 7,10" fill="currentColor"/></svg>
                    +{{ $folio['recycle_value'] }}
                </span>
                <button type="button" class="folio-flip-icon" data-flip-folio aria-label="Retourner au recto de {{ $folio['name'] }}" title="Recto">
                    ↺
                </button>
            </div>
        </div>
    </div>
</article>
