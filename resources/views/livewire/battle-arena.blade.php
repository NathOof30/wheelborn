<div
    x-data="battleAnimation"
    x-on:battle-started.window="play($event.detail)"
    x-on:battle-reset.window="reset()"
    class="space-y-6"
    @if (! $isAdmin && $arenaTickets < $maxTickets) wire:poll.15s="refreshTickets" @endif
>
    @if ($this->fighters->isEmpty())
        <div class="codex-panel codex-panel--framed max-w-xl mx-auto p-8 sm:p-10 text-center space-y-5 my-6">
            <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center bg-parchment-shade/80 border-2 border-brass-dark/40 text-wax">
                <svg class="w-8 h-8" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="space-y-2">
                <h2 class="font-display text-2xl font-bold text-ink">Aucun combattant disponible</h2>
                <p class="font-body text-base text-ink-sepia leading-relaxed">
                    Invoquez d'abord un héros sur la Roue du Destin pour entrer dans l'arène de duel.
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn-wax">
                    <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="3" fill="currentColor"/>
                    </svg>
                    <span>Aller au tirage</span>
                </a>
            </div>
        </div>
    @else
        {{-- ========================================================================= --}}
        {{-- BANDEAU DE COMMANDE : ARÈNE 1V1, TICKETS & COMPTE À REBOURS               --}}
        {{-- ========================================================================= --}}
        <div class="codex-panel p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-wax/10 border border-wax/40 flex items-center justify-center text-wax shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-base font-bold uppercase tracking-wider text-ink">Arène de Duel 1v1</h2>
                    <p class="font-body text-sm italic text-ink-muted">Matchmaking classé &amp; duels contre joueurs et fantômes</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if ($isAdmin)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brass/20 border border-brass-dark/50 font-display text-xs font-bold uppercase tracking-wider text-brass-dark">
                        <span>👑</span>
                        <span>Tickets illimités</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border font-display text-xs font-bold {{ $arenaTickets > 0 ? 'bg-parchment-bright border-brass-dark/50 text-ink' : 'bg-wax/10 border-wax/50 text-wax' }}">
                        <svg class="w-3.5 h-3.5 text-brass-dark" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7 H20 V10 A2 2 0 0 0 20 14 V17 H4 V14 A2 2 0 0 0 4 10 Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ $arenaTickets }}/{{ $maxTickets }}</span>
                    </span>

                    @if ($arenaTickets < $maxTickets)
                        <div
                            wire:key="arena-timer-{{ $arenaTickets }}-{{ $nextTicketSeconds }}"
                            x-data="{
                                remaining: {{ (int) $nextTicketSeconds }},
                                interval: null,
                                formatTime() {
                                    const s = Math.max(0, parseInt(this.remaining, 10) || 0);
                                    const m = Math.floor(s / 60);
                                    const sec = s % 60;
                                    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
                                },
                                init() {
                                    this.interval = setInterval(() => {
                                        if (this.remaining > 0) {
                                            this.remaining--;
                                        } else {
                                            clearInterval(this.interval);
                                            this.interval = null;
                                            $wire.call('refreshTickets');
                                        }
                                    }, 1000);
                                },
                                destroy() {
                                    if (this.interval) clearInterval(this.interval);
                                }
                            }"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-parchment-shade/80 border border-parchment-border font-body text-xs font-semibold text-ink-sepia"
                        >
                            <span>⏳</span>
                            <span>+1 dans <strong x-text="formatTime()" class="font-display text-ink">{{ sprintf('%02d:%02d', floor($nextTicketSeconds / 60), $nextTicketSeconds % 60) }}</strong></span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if ($error)
            <div class="p-3.5 rounded-lg bg-wax/10 border-2 border-wax/50 font-body text-sm font-bold text-wax flex items-center gap-2.5" role="alert">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3 L22 20 H2 Z M12 10 V14 M12 17 V17.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ $error }}</span>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- ÉTABLI DE DUEL : 2 CARTES UNIVERSELLES (316×440) + MÉDAILLON VS           --}}
        {{-- ========================================================================= --}}
        @php
            $challengerWinner = $result && ! $result['draw'] && $result['won'];
            $challengerLoser = $result && ! $result['draw'] && ! $result['won'];
            $opponentWinner = $result && ! $result['draw'] && ! $result['won'];
            $opponentLoser = $result && ! $result['draw'] && $result['won'];
            $isDraw = (bool) ($result['draw'] ?? false);
        @endphp

        <div class="duel-workbench">
            <div class="duel-workbench-grid">
                @foreach ($this->cards as $side => $card)
                    @if ($side === 'opponent')
                        <div class="flex flex-col items-center justify-center py-1 select-none">
                            <div class="duel-vs-medallion" aria-hidden="true">VS</div>
                        </div>
                    @endif

                    @php
                        $source = $card['character'];
                        $cardId = $source instanceof \App\Models\Character
                            ? (string) $source->id
                            : (string) ($source['id'] ?? $side);
                        $isSideWinner = $side === 'challenger' ? $challengerWinner : $opponentWinner;
                        $isSideLoser = $side === 'challenger' ? $challengerLoser : $opponentLoser;
                    @endphp

                    <div
                        wire:key="duel-slot-{{ $side }}-{{ $cardId }}"
                        class="duel-slot"
                        :class="{
                            'is-winner': done && @js($isSideWinner),
                            'is-loser': done && @js($isSideLoser),
                        }"
                    >
                        {{-- En-tête externe du slot : Camp & Actions rapides --}}
                        <div class="duel-slot-bar">
                            @if ($side === 'challenger')
                                <button
                                    type="button"
                                    wire:click="openFighterModal"
                                    @disabled($result)
                                    class="btn-cartouche btn-cartouche--sm"
                                    title="Ouvrir la sélection de combattant"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 7 H18 M14 3 L18 7 L14 11 M20 17 H6 M10 13 L6 17 L10 21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>Changer</span>
                                </button>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-parchment-light/95 border border-forest/40 font-display text-[11px] font-bold uppercase tracking-wider text-forest truncate">
                                    <span class="w-2 h-2 rounded-full bg-forest shrink-0"></span>
                                    <span class="truncate">{{ $card['label'] }}</span>
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="inspectOpponent"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-parchment-light/95 border border-wax/45 font-display text-[11px] font-bold uppercase tracking-wider text-wax hover:bg-parchment-bright transition cursor-pointer min-w-0"
                                    title="Cliquer pour inspecter le profil de l'adversaire"
                                >
                                    <span class="w-2 h-2 rounded-full bg-wax shrink-0"></span>
                                    <span class="truncate">{{ $card['label'] }}</span>
                                    <svg class="w-3.5 h-3.5 shrink-0 opacity-80" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="11" cy="11" r="6" fill="none" stroke="currentColor" stroke-width="2"/>
                                        <path d="M16 16 L20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="rerollOpponent"
                                    wire:loading.attr="disabled"
                                    @disabled($result)
                                    class="btn-cartouche btn-cartouche--sm shrink-0"
                                    title="{{ $this->rerollCost === 0 ? 'Chercher un autre adversaire (1er essai gratuit)' : 'Chercher un autre adversaire (coûte '.$this->rerollCost.' 🪙)' }}"
                                >
                                    <span>Autre</span>
                                    @if ($this->rerollCost === 0)
                                        <span class="px-1.5 py-0.5 rounded bg-forest/15 text-forest text-[10px] font-bold normal-case">Gratuit</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded bg-brass/25 text-ink text-[10px] font-bold normal-case">{{ $this->rerollCost }} 🪙</span>
                                    @endif
                                </button>
                            @endif
                        </div>

                        {{-- Barre de PV animée pendant le duel --}}
                        <div class="duel-hp-hud">
                            <span class="duel-hp-label">PV</span>
                            <div class="space-y-1 min-w-0">
                                <div class="duel-hp-track">
                                    <div
                                        class="duel-hp-fill {{ $side === 'challenger' ? 'duel-hp-fill--player' : '' }}"
                                        x-bind:style="{ width: pct('{{ $side }}', {{ $card['hp'] }}) + '%' }"
                                    ></div>
                                </div>
                                @if ($card['elo'])
                                    <div class="font-display text-[10px] font-bold text-ink-muted leading-none">
                                        {{ $card['elo'] }} Élo
                                    </div>
                                @endif
                            </div>
                            <span class="duel-hp-val">
                                <strong x-text="hp('{{ $side }}', {{ $card['hp'] }})">{{ $card['hp'] }}</strong>/{{ $card['hp'] }}
                            </span>
                        </div>

                        {{-- La VRAIE Carte Universelle Codex (316×440, flippable à tout moment) --}}
                        <div class="duel-card-stage">
                            <x-folio-card
                                :card="$source"
                                :showOwnerActions="false"
                                :folioKey="'arena-'.$side.'-'.$cardId"
                                wire:key="arena-card-{{ $side }}-{{ $cardId }}"
                            />

                            @if ($result)
                                @if ($isDraw)
                                    <div x-show="done" x-cloak class="duel-stamp duel-stamp--draw">ÉGALITÉ</div>
                                @elseif ($isSideWinner)
                                    <div x-show="done" x-cloak class="duel-stamp duel-stamp--win">VICTORIEUX</div>
                                @else
                                    <div x-show="done" x-cloak class="duel-stamp duel-stamp--lose">VAINCU</div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- POSTE TACTIQUE & COMMANDES : COMPARATIF / JOURNAL / BILAN / ACTIONS       --}}
        {{-- ========================================================================= --}}
        <div class="codex-panel codex-panel--framed p-5 sm:p-6 space-y-5">
            {{-- Boutons d'action principaux --}}
            <div class="flex flex-wrap items-center justify-center gap-3 pb-4 border-b border-parchment-border/70">
                @if (! $result)
                    <button
                        type="button"
                        wire:click="fight"
                        wire:loading.attr="disabled"
                        @disabled(! $isAdmin && $arenaTickets === 0)
                        class="btn-wax w-full sm:w-auto sm:min-w-[280px] py-3 text-base"
                    >
                        <span wire:loading.remove wire:target="fight" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Combattre !</span>
                            @if (! $isAdmin)
                                <span class="text-xs opacity-85 font-body normal-case">(-1 🎟️)</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="fight" class="inline-flex items-center gap-2">
                            <span>⏳</span>
                            <span>Combat en cours...</span>
                        </span>
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="rematch"
                        class="btn-cartouche min-w-[160px]"
                        title="Rejouer contre le même adversaire"
                    >
                        <span>Revanche</span>
                    </button>
                    <button
                        type="button"
                        wire:click="newOpponent"
                        class="btn-brass min-w-[190px]"
                        title="Chercher un nouvel adversaire"
                    >
                        <span>Autre adversaire</span>
                    </button>
                @endif
            </div>

            @if (! $result)
                {{-- 1. ÉTAT PRÉ-COMBAT : COMPARATIF DES FORCES --}}
                @if ($comparison)
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-parchment-border/60">
                            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-ink flex items-center gap-2">
                                <svg class="w-4 h-4 text-brass-dark" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 V21 M4 8 L20 8 M6 8 L4 14 H8 Z M18 8 L16 14 H20 Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Comparatif des Forces</span>
                            </h3>

                            @if ($comparison['power_diff'] > 0)
                                <span class="codex-init-badge codex-init-badge--player">
                                    <span>▲</span>
                                    <span>Avantage Héros (+{{ $comparison['power_diff'] }})</span>
                                </span>
                            @elseif ($comparison['power_diff'] < 0)
                                <span class="codex-init-badge codex-init-badge--opponent">
                                    <span>▼</span>
                                    <span>Avantage Bot (+{{ abs($comparison['power_diff']) }})</span>
                                </span>
                            @else
                                <span class="codex-init-badge codex-init-badge--draw">
                                    Équilibre Parfait
                                </span>
                            @endif
                        </div>

                        {{-- Bandeau Duellistes & Initiative --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 px-3.5 py-2.5 rounded-lg bg-parchment-bright/90 border border-parchment-border">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-forest shrink-0"></span>
                                <span class="font-display text-xs sm:text-sm font-bold text-ink truncate max-w-[180px]">{{ $comparison['challenger']['name'] }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-brass/20 border border-brass-dark/40 font-display text-[11px] font-bold text-ink">✦ {{ $comparison['challenger']['power_score'] }}</span>
                            </div>

                            <span class="{{ $comparison['initiative']['badge'] }}">
                                <span>{{ $comparison['initiative']['icon'] }}</span>
                                <span>{{ $comparison['initiative']['label'] }}</span>
                            </span>

                            <div class="flex items-center gap-2 min-w-0 justify-end">
                                <span class="px-1.5 py-0.5 rounded bg-brass/20 border border-brass-dark/40 font-display text-[11px] font-bold text-ink">✦ {{ $comparison['opponent']['power_score'] }}</span>
                                <span class="font-display text-xs sm:text-sm font-bold text-ink-sepia truncate max-w-[180px]">{{ $comparison['opponent']['name'] }}</span>
                                <span class="w-2.5 h-2.5 rounded-full bg-wax shrink-0"></span>
                            </div>
                        </div>

                        {{-- 4 Barres Bicolores (Vert Forêt vs Cire Cramoisie) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3.5">
                            @foreach ($comparison['stats'] as $s)
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-1.5 min-w-[68px]">
                                            <span class="font-display text-sm font-bold {{ $s['diff'] > 0 ? 'text-forest' : ($s['diff'] < 0 ? 'text-ink-muted' : 'text-ink') }}">
                                                {{ $s['c_val'] }}
                                            </span>
                                            @if ($s['diff'] > 0)
                                                <span class="px-1.5 py-0.5 rounded bg-forest/15 border border-forest/40 font-display text-[10px] font-bold text-forest">
                                                    +{{ $s['diff'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="font-display text-[11px] font-bold uppercase tracking-wide text-ink-sepia flex items-center gap-1">
                                            <span>{{ $s['label'] }}</span>
                                        </div>

                                        <div class="flex items-center gap-1.5 min-w-[68px] justify-end">
                                            @if ($s['diff'] < 0)
                                                <span class="px-1.5 py-0.5 rounded bg-wax/10 border border-wax/40 font-display text-[10px] font-bold text-wax">
                                                    +{{ abs($s['diff']) }}
                                                </span>
                                            @endif
                                            <span class="font-display text-sm font-bold {{ $s['diff'] < 0 ? 'text-wax' : ($s['diff'] > 0 ? 'text-ink-muted' : 'text-ink') }}">
                                                {{ $s['o_val'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="relative h-3 rounded-full bg-parchment-shade border border-parchment-border overflow-hidden flex">
                                        <div
                                            class="h-full transition-all duration-300"
                                            style="width: {{ $s['ratio_c'] }}%; background: linear-gradient(90deg, #1E4D33, #2E7A50);"
                                            title="Joueur : {{ $s['c_val'] }} ({{ $s['ratio_c'] }}%)"
                                        ></div>
                                        <div class="w-0.5 h-full bg-brass-light z-10 shrink-0"></div>
                                        <div
                                            class="h-full transition-all duration-300 flex-1"
                                            style="background: linear-gradient(90deg, #B3261E, #6E1813);"
                                            title="Adversaire : {{ $s['o_val'] }} ({{ $s['ratio_o'] }}%)"
                                        ></div>
                                        <div class="absolute inset-y-0 left-1/2 w-px bg-ink/25 pointer-events-none"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Face-à-face des Synergies --}}
                        <div class="pt-3 border-t border-parchment-border/60 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-lg bg-parchment-bright/80 border border-parchment-border space-y-1.5">
                                <div class="font-display text-[11px] font-bold uppercase tracking-wider text-forest flex items-center gap-1.5">
                                    <span>●</span>
                                    <span>Synergies Joueur ({{ count($comparison['challenger']['synergies'] ?? []) }})</span>
                                </div>
                                @if (! empty($comparison['challenger']['synergies']))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($comparison['challenger']['synergies'] as $syn)
                                            <span class="px-2 py-0.5 rounded bg-brass/20 border border-brass-dark/40 font-body text-xs font-bold text-ink">
                                                ✦ {{ $syn }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="font-body italic text-ink-muted block">Aucune synergie active</span>
                                @endif
                            </div>

                            <div class="p-3 rounded-lg bg-parchment-bright/80 border border-parchment-border space-y-1.5 sm:text-right">
                                <div class="font-display text-[11px] font-bold uppercase tracking-wider text-wax flex items-center sm:justify-end gap-1.5">
                                    <span>Synergies Adversaire ({{ count($comparison['opponent']['synergies'] ?? []) }})</span>
                                    <span>●</span>
                                </div>
                                @if (! empty($comparison['opponent']['synergies']))
                                    <div class="flex flex-wrap gap-1.5 sm:justify-end">
                                        @foreach ($comparison['opponent']['synergies'] as $syn)
                                            <span class="px-2 py-0.5 rounded bg-wax/10 border border-wax/35 font-body text-xs font-bold text-wax">
                                                ✦ {{ $syn }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="font-body italic text-ink-muted block">Aucune synergie active</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @else
                {{-- 2. ÉTAT COMBAT EN COURS / RÉSOLU : CHRONIQUE & BILAN --}}
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-display text-sm font-bold uppercase tracking-wider text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-brass-dark" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 4 H16 L19 7 V20 H5 Z M9 10 H15 M9 14 H15 M9 18 H12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Journal des Échanges</span>
                        </h3>
                        <button
                            type="button"
                            x-show="!done"
                            x-on:click="finish()"
                            class="btn-cartouche btn-cartouche--sm"
                        >
                            Passer le combat ⏭
                        </button>
                    </div>

                    <div x-ref="log" class="duel-log-scroll h-72 overflow-y-auto p-3.5 space-y-1.5">
                        <template x-for="(entry, i) in entries" :key="i">
                            <p :class="style(entry)" x-text="entry.message"></p>
                        </template>
                    </div>

                    {{-- Bilan Final --}}
                    <div x-show="done" x-cloak x-transition class="p-4 sm:p-5 rounded-lg bg-parchment-bright border-2 border-brass text-center space-y-3">
                        @if ($result['draw'])
                            <div class="font-display text-xl sm:text-2xl font-bold text-ink-sepia">
                                ÉGALITÉ SANGLANTE !
                            </div>
                            <p class="font-body text-sm italic text-ink-muted">Double K.O. simultané. Aucun changement d'Élo.</p>
                        @elseif ($result['won'])
                            <div class="font-display text-xl sm:text-2xl font-bold text-forest">
                                VICTOIRE ÉCLATANTE !
                            </div>
                        @else
                            <div class="font-display text-xl sm:text-2xl font-bold text-wax">
                                DÉFAITE
                            </div>
                        @endif

                        <div class="font-body text-xs font-semibold text-ink-muted">
                            {{ $result['turns'] }} tour(s) {{ $result['reason'] === 'turn_limit' ? '• décision aux points' : '' }}
                        </div>

                        <div class="flex flex-wrap justify-center gap-2.5 pt-1">
                            <span class="px-3 py-1 rounded-md bg-parchment-light border border-parchment-border font-display text-xs font-bold {{ $result['elo_delta'] >= 0 ? 'text-forest' : 'text-wax' }}">
                                {{ $result['elo_delta'] >= 0 ? '+' : '' }}{{ $result['elo_delta'] }} Élo
                            </span>
                            <span class="px-3 py-1 rounded-md bg-parchment-light border border-parchment-border font-display text-xs font-bold text-brass-dark">
                                +{{ $result['coins'] }} 🪙
                            </span>
                            <span class="px-3 py-1 rounded-md bg-parchment-light border border-parchment-border font-display text-xs font-bold text-ink">
                                +{{ $result['xp'] }} XP
                            </span>
                        </div>

                        @if ($result['league_change'])
                            @if ($result['league_change']['type'] === 'promotion')
                                <div class="mx-auto max-w-sm rounded-lg border-2 border-brass bg-parchment-light px-4 py-3 space-y-1 shadow-md">
                                    <div class="text-xl">{{ $result['league_change']['icon'] }}</div>
                                    <div class="font-display text-sm font-bold uppercase tracking-wider text-brass-dark">Promotion !</div>
                                    <div class="font-body text-sm text-ink">
                                        Vous entrez en ligue <strong class="font-display text-ink">{{ $result['league_change']['label'] }}</strong>.
                                    </div>
                                </div>
                            @else
                                <div class="mx-auto max-w-sm rounded-lg border-2 border-wax/60 bg-wax/5 px-4 py-3 space-y-1">
                                    <div class="text-xl">{{ $result['league_change']['icon'] }}</div>
                                    <div class="font-display text-sm font-bold uppercase tracking-wider text-wax">Relégation</div>
                                    <div class="font-body text-sm text-ink-sepia">
                                        Vous redescendez en ligue <strong class="font-display text-ink">{{ $result['league_change']['label'] }}</strong>. Quelques victoires et vous remontez !
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- MODALE SÉLECTION DU COMBATTANT (VRAIES CARTES UNIVERSELLES)               --}}
        {{-- ========================================================================= --}}
        @if ($showFighterModal)
            <div
                class="codex-modal-backdrop"
                x-data
                x-on:keydown.escape.window="$wire.closeFighterModal()"
            >
                <div
                    class="codex-modal-panel max-w-6xl max-h-[90vh] flex flex-col"
                    @click.outside="$wire.closeFighterModal()"
                >
                    <div class="p-4 sm:p-5 border-b border-parchment-border bg-parchment-shade/45 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-display text-lg font-bold text-ink">Sélectionner ton Combattant</h3>
                            <p class="font-body text-sm italic text-ink-muted">Cliquez une carte pour consulter son verso, puis choisissez votre champion.</p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeFighterModal"
                            class="w-9 h-9 rounded-full bg-parchment-bright border border-parchment-border text-ink hover:bg-wax hover:text-parchment-bright hover:border-wax transition flex items-center justify-center font-bold cursor-pointer"
                            title="Fermer la sélection"
                            aria-label="Fermer la sélection"
                        >
                            ✕
                        </button>
                    </div>

                    {{-- Filtres Modale --}}
                    <div class="p-4 border-b border-parchment-border bg-parchment-light/70 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-8 relative">
                                <input
                                    type="text"
                                    wire:model.live.debounce.250ms="search"
                                    placeholder="Rechercher par nom, race, arme ou pouvoir..."
                                    class="w-full pl-3.5 pr-9 py-2 bg-parchment-bright border border-parchment-border rounded-md font-body text-sm text-ink placeholder-ink-muted focus:outline-none focus:border-brass-dark focus:ring-1 focus:ring-brass-dark"
                                >
                                @if (! empty($search))
                                    <button
                                        type="button"
                                        wire:click="$set('search', '')"
                                        class="absolute inset-y-0 right-3 flex items-center text-ink-muted hover:text-ink text-xs font-bold"
                                        aria-label="Effacer la recherche"
                                    >
                                        ✕
                                    </button>
                                @endif
                            </div>

                            <div class="sm:col-span-4">
                                <select
                                    wire:model.live="sortBy"
                                    class="w-full py-2 px-3 bg-parchment-bright border border-parchment-border rounded-md font-display text-xs font-bold text-ink focus:outline-none focus:border-brass-dark cursor-pointer"
                                >
                                    <option value="power_desc">Puissance (Max → Min)</option>
                                    <option value="power_asc">Puissance (Min → Max)</option>
                                    <option value="wins_desc">Victoires (Max)</option>
                                    <option value="date_desc">Plus récents</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-display text-xs font-bold text-ink-muted mr-1">Rareté :</span>
                            <button
                                type="button"
                                wire:click="$set('rarityFilter', '')"
                                class="filter-chip {{ empty($rarityFilter) ? 'is-active' : '' }}"
                            >
                                Tous
                            </button>
                            @foreach (['common' => ['Commune', '#6B4A2A'], 'rare' => ['Rare', '#2A3A7A'], 'epic' => ['Épique', '#54274A'], 'legendary' => ['Légendaire', '#74520A']] as $rKey => [$rLabel, $rHex])
                                <button
                                    type="button"
                                    wire:click="$set('rarityFilter', '{{ $rKey }}')"
                                    class="filter-chip {{ $rarityFilter === $rKey ? 'is-active' : '' }}"
                                    style="--chip-hex: {{ $rHex }};"
                                >
                                    {{ $rLabel }}
                                </button>
                            @endforeach

                            @if (! empty($search) || ! empty($rarityFilter))
                                <button
                                    type="button"
                                    wire:click="resetModalFilters"
                                    class="ml-auto font-display text-xs font-bold text-wax hover:underline cursor-pointer"
                                >
                                    Réinitialiser
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Grille des cartes universelles --}}
                    <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                        @if ($this->modalFighters->isEmpty())
                            <div class="text-center py-12 space-y-3">
                                <p class="font-display text-base font-bold text-ink">Aucun combattant ne correspond à cette recherche.</p>
                                <button
                                    type="button"
                                    wire:click="resetModalFilters"
                                    class="btn-cartouche btn-cartouche--sm"
                                >
                                    Effacer les filtres
                                </button>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 justify-items-center">
                                @foreach ($this->modalFighters as $char)
                                    @php $isSelected = $char->id === $fighterId; @endphp
                                    <div
                                        wire:key="modal-pick-{{ $char->id }}"
                                        class="picker-slot w-full max-w-[344px] {{ $isSelected ? 'is-selected' : '' }}"
                                    >
                                        <x-folio-card
                                            :card="$char"
                                            :showOwnerActions="false"
                                            :folioKey="'picker-'.$char->id"
                                        />

                                        <button
                                            type="button"
                                            wire:click="selectFighter('{{ $char->id }}')"
                                            class="{{ $isSelected ? 'btn-wax btn-wax--sm' : 'btn-brass btn-brass--sm' }} w-full max-w-[316px]"
                                        >
                                            @if ($isSelected)
                                                <span>✓ En combat</span>
                                            @else
                                                <span>Choisir {{ $char->name }}</span>
                                            @endif
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALE D'INSPECTION RIVAL (JOUEUR OU ENTITÉ ASTRALE)                      --}}
    {{-- ========================================================================= --}}
    @if ($showOpponentModal && $inspectedRival)
        <div
            class="codex-modal-backdrop"
            x-data
            x-on:keydown.escape.window="$wire.closeOpponentModal()"
        >
            <div
                class="codex-modal-panel {{ ($inspectedRival['kind'] === 'player' && ! empty($inspectedRival['favorites'])) ? 'max-w-6xl' : 'max-w-lg' }} max-h-[90vh] flex flex-col"
                @click.outside="$wire.closeOpponentModal()"
            >
                <div class="p-4 sm:p-5 border-b border-parchment-border bg-parchment-shade/45 flex items-center justify-between gap-3">
                    <span class="font-display text-xs font-bold uppercase tracking-widest text-wax">
                        {{ $inspectedRival['kind'] === 'player' ? "Rival d'Arène" : 'Entité Astrale' }}
                    </span>
                    <button
                        type="button"
                        wire:click="closeOpponentModal"
                        class="w-8 h-8 rounded-full bg-parchment-bright border border-parchment-border text-ink hover:bg-wax hover:text-parchment-bright hover:border-wax transition flex items-center justify-center font-bold text-sm cursor-pointer"
                        aria-label="Fermer l'inspection"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                    @if ($inspectedRival['kind'] === 'player')
                        <div class="flex flex-wrap items-center gap-4">
                            <div
                                class="w-16 h-16 rounded-xl flex flex-col items-center justify-center border-2 bg-parchment-bright shrink-0"
                                style="border-color: {{ $inspectedRival['league']['ink_color'] ?? '#6E4F0E' }};"
                            >
                                <span class="text-2xl leading-none">{{ $inspectedRival['league']['icon'] ?? '🛡️' }}</span>
                                <span class="font-display text-[9px] uppercase font-bold mt-1" style="color: {{ $inspectedRival['league']['ink_color'] ?? '#6E4F0E' }};">
                                    {{ $inspectedRival['league']['name'] ?? 'Ligue' }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-display text-xl font-bold text-ink truncate">
                                    {{ $inspectedRival['name'] }}
                                </h3>
                                @if (isset($inspectedRival['level']))
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-brass/15 border border-brass-dark/40 font-display text-xs font-bold text-ink-sepia mt-1">
                                        <span>Niveau {{ $inspectedRival['level'] }}</span>
                                        <span>·</span>
                                        <span>« {{ $inspectedRival['title'] }} »</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5 text-center p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                            <div>
                                <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Élo</span>
                                <span class="font-display text-base font-bold text-brass-dark">{{ $inspectedRival['elo'] }}</span>
                            </div>
                            <div>
                                <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Duels</span>
                                <span class="font-display text-base font-bold text-ink">{{ $inspectedRival['battles'] ?? 0 }}</span>
                            </div>
                            <div>
                                <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Winrate</span>
                                <span class="font-display text-base font-bold text-forest">{{ $inspectedRival['winrate'] ?? 0 }}%</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">
                                Champions de prédilection
                            </h4>
                            @if (! empty($inspectedRival['favorites']))
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 justify-items-center">
                                    @foreach ($inspectedRival['favorites'] as $fav)
                                        <x-folio-card
                                            :card="$fav"
                                            :showOwnerActions="false"
                                            :folioKey="'rival-fav-'.($fav['id'] ?? $loop->index)"
                                        />
                                    @endforeach
                                </div>
                            @else
                                <p class="font-body text-sm italic text-ink-muted p-4 rounded-lg bg-parchment-bright/70 border border-dashed border-parchment-border text-center">
                                    Aucun favori exposé actuellement.
                                </p>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-parchment-border flex flex-wrap items-center justify-between gap-3">
                            <button
                                type="button"
                                wire:click="closeOpponentModal"
                                class="btn-cartouche btn-cartouche--sm"
                            >
                                Fermer
                            </button>
                            @if (! empty($inspectedRival['profile_url']))
                                <a href="{{ $inspectedRival['profile_url'] }}" class="btn-brass btn-brass--sm">
                                    <span>Voir le profil complet</span>
                                    <span>→</span>
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="space-y-4">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink">{{ $inspectedRival['name'] }}</h3>
                                <p class="font-body text-sm italic text-ink-muted">Fantôme invoqué par l'Arène</p>
                            </div>

                            <div class="p-3.5 rounded-lg bg-parchment-bright border border-parchment-border space-y-2 text-sm">
                                <p class="font-body text-ink-sepia leading-relaxed">
                                    Ce combattant est une projection astrale générée par la Roue pour éprouver votre valeur tactique. Ses attributs sont équilibrés selon votre division.
                                </p>
                                <div class="flex justify-between pt-2 border-t border-parchment-border/70 font-display text-xs font-bold text-ink">
                                    <span>Élo estimé : <strong class="text-brass-dark">{{ $inspectedRival['elo'] }}</strong></span>
                                    <span>Puissance : <strong class="text-wax">✦ {{ $inspectedRival['power_score'] }}</strong></span>
                                </div>
                            </div>

                            @if (! empty($inspectedRival['card']))
                                <div class="flex justify-center">
                                    <x-folio-card
                                        :card="$inspectedRival['card']"
                                        :showOwnerActions="false"
                                        folioKey="rival-ghost-preview"
                                    />
                                </div>
                            @endif

                            <div class="pt-3 border-t border-parchment-border flex justify-end">
                                <button
                                    type="button"
                                    wire:click="closeOpponentModal"
                                    class="btn-brass btn-brass--sm"
                                >
                                    Compris
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
