<div
    x-data="battleAnimation"
    x-on:battle-started.window="play($event.detail)"
    x-on:battle-reset.window="reset()"
    class="space-y-4 max-w-7xl mx-auto"
    @if (!$isAdmin && $arenaTickets < $maxTickets) wire:poll.15s="refreshTickets" @endif
>
    @if ($this->fighters->isEmpty())
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-12 text-center max-w-xl mx-auto shadow-2xl space-y-4 my-10">
            <p class="text-6xl animate-bounce">⚔️</p>
            <h2 class="text-2xl font-black text-white">Aucun combattant disponible</h2>
            <p class="text-slate-400 text-sm leading-relaxed">Invoque d'abord un héros sur la Roue du Destin pour entrer dans l'arène de duel.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl transition shadow-lg hover:scale-105 active:scale-95">
                <span>🎡</span>
                <span>Aller au tirage</span>
            </a>
        </div>
    @else
        {{-- LAYOUT COCKPIT ZERO-SCROLL : ARÈNE À GAUCHE (65%), POSTE DE COMMANDE & LOGS À DROITE (35%) --}}
        <div class="grid grid-cols-1 lg:grid-cols-[65%_1fr] gap-6 items-stretch">

            {{-- ========================================================================= --}}
            {{-- COLONNE GAUCHE : L'ARÈNE FACE-À-FACE (JOUEUR VS BOT)                      --}}
            {{-- ========================================================================= --}}
            <div class="bg-slate-900/70 border border-slate-800/90 rounded-3xl p-5 shadow-2xl backdrop-blur-md relative flex flex-col justify-between h-full w-full">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-5 sm:gap-6">
                    @foreach ($this->cards as $side => $card)
                        @if ($side === 'opponent')
                            {{-- Écusson VS Central --}}
                            <div class="flex flex-col items-center justify-center py-2 sm:py-0 shrink-0 select-none">
                                <div class="w-12 h-12 rounded-2xl bg-rose-600/20 border-2 border-rose-500/60 shadow-[0_0_20px_rgba(244,63,94,0.4)] flex items-center justify-center text-xl font-black text-rose-400 animate-pulse">
                                    VS
                                </div>
                            </div>
                        @endif

                        @php
                            $rarityKey = $card['rarity_key'];
                            $borderStyles = match($rarityKey) {
                                'legendary' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950 shadow-[0_0_25px_rgba(251,191,36,0.25)] hover:shadow-[0_0_35px_rgba(251,191,36,0.4)]',
                                'epic'      => 'border-purple-500/70 bg-gradient-to-b from-purple-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(192,132,252,0.2)] hover:shadow-[0_0_30px_rgba(192,132,252,0.35)]',
                                'rare'      => 'border-cyan-400/60 bg-gradient-to-b from-cyan-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(56,189,248,0.18)] hover:shadow-[0_0_30px_rgba(56,189,248,0.3)]',
                                default     => 'border-slate-800 bg-slate-900/90 hover:border-slate-700',
                            };
                            $badgeStyle = match($rarityKey) {
                                'legendary' => 'bg-amber-400 text-slate-950 font-black shadow-[0_0_10px_rgba(251,191,36,0.5)]',
                                'epic'      => 'bg-purple-500 text-white font-bold',
                                'rare'      => 'bg-cyan-500 text-slate-950 font-bold',
                                default     => 'bg-slate-700 text-slate-200 font-medium',
                            };
                            $stars = match($rarityKey) {
                                'legendary' => 5,
                                'epic'      => 4,
                                'rare'      => 3,
                                default     => 2,
                            };
                        @endphp

                        <div class="space-y-2.5 w-[270px] max-w-[270px] shrink-0">
                            {{-- En-tête de carte : Bouton d'action rapide & Camp --}}
                            <div class="flex items-center justify-between gap-1 text-xs">
                                @if ($side === 'challenger')
                                    <button
                                        type="button"
                                        wire:click="openFighterModal"
                                        @disabled($result)
                                        class="px-2.5 py-1 rounded-lg font-bold bg-slate-800 hover:bg-slate-700 text-rose-300 border border-slate-700 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                                        title="Ouvrir la sélection de combattant"
                                    >
                                        <span>🔄</span>
                                        <span>Changer</span>
                                    </button>
                                    <span class="font-extrabold text-emerald-400 flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        {{ $card['label'] }}
                                    </span>
                                @else
                                    <span class="font-extrabold text-rose-400 flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                                        <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                                        {{ $card['label'] }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="newOpponent"
                                        wire:loading.attr="disabled"
                                        @disabled($result)
                                        class="px-2.5 py-1 rounded-lg font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                                        title="Chercher un nouvel adversaire"
                                    >
                                        <span>🎲</span>
                                        <span>Autre</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Combat HUD : Barre de PV animée pendant le duel --}}
                            <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-2.5 shadow space-y-1">
                                <div class="flex justify-between items-center text-xs text-slate-300 font-mono">
                                    <span class="font-bold flex items-center gap-1 text-[11px]">
                                        <span>❤️ PV</span>
                                        @if ($card['elo'])
                                            <span class="text-amber-400 font-bold ml-1">({{ $card['elo'] }} Élo)</span>
                                        @endif
                                    </span>
                                    <span><strong x-text="hp('{{ $side }}', {{ $card['hp'] }})" class="text-white">{{ $card['hp'] }}</strong> / {{ $card['hp'] }}</span>
                                </div>
                                <div class="h-2.5 rounded-full bg-slate-900 border border-slate-800 overflow-hidden shadow-inner">
                                    <div
                                        class="h-full rounded-full transition-all duration-300 shadow-sm {{ $side === 'challenger' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-rose-500 to-amber-500' }}"
                                        x-bind:style="{ width: pct('{{ $side }}', {{ $card['hp'] }}) + '%' }"
                                    ></div>
                                </div>
                            </div>

                            {{-- CARTE OFFICIELLE WHEELBORN AVEC 3D FLIP AU CLIC --}}
                            <div
                                x-data="{ flipped: false }"
                                class="perspective-1000 w-[270px] select-none"
                                style="width: 270px; height: 465px; min-height: 465px;"
                            >
                                <div
                                    class="relative w-full h-full preserve-3d card-flip-transition"
                                    :class="{ 'rotate-y-180': flipped }"
                                    style="width: 270px; height: 465px; min-height: 465px;"
                                >
                                    {{-- ========================================================================= --}}
                                    {{-- FACE RECTO (CLIC POUR FLIP VERS LE VERSO)                                  --}}
                                    {{-- ========================================================================= --}}
                                    <div
                                        @click="flipped = true"
                                        class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} overflow-hidden p-3.5 flex flex-col justify-between backface-hidden shadow-xl cursor-pointer hover:scale-[1.01] transition-transform duration-200"
                                        :class="{ 'pointer-events-none': flipped }"
                                        style="width: 270px; height: 465px; min-height: 465px;"
                                        title="Cliquer pour afficher les statistiques et passifs"
                                    >
                                        {{-- En-tête Recto --}}
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] uppercase tracking-wider {{ $badgeStyle }}">
                                                {{ $card['rarity_label'] }}
                                            </span>

                                            <div class="flex items-center gap-1.5">
                                                @if ($card['is_locked'])
                                                    <span class="text-amber-400 text-[10px] bg-slate-950/80 px-1.5 py-0.5 rounded-full border border-amber-500/40 shadow-sm" title="Protégé contre le recyclage">
                                                        🔒
                                                    </span>
                                                @endif
                                                <div class="flex items-center gap-1 bg-slate-950/80 px-2 py-0.5 rounded-lg border border-slate-800">
                                                    <span class="text-[11px] text-amber-400">⚡</span>
                                                    <span class="font-extrabold text-xs text-white font-mono">{{ $card['power_score'] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Scène Visuelle : Avatar Composite Paper Doll --}}
                                        <div class="flex-1 my-1 flex items-center justify-center overflow-hidden">
                                            <x-character-avatar :character="$card['character']" />
                                        </div>

                                        {{-- Pied de carte Recto : Titre & Composants --}}
                                        <div class="text-center pt-2 border-t border-slate-800/80">
                                            {{-- Étoiles de Rareté --}}
                                            <div class="flex items-center justify-center gap-0.5 mb-0.5">
                                                @for ($s = 1; $s <= 5; $s++)
                                                    <span class="text-[11px] {{ $s <= $stars ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]' : 'text-slate-800' }}">
                                                        ★
                                                    </span>
                                                @endfor
                                            </div>
                                            <h3 class="text-sm font-black text-white truncate tracking-tight">
                                                {{ $card['name'] }}
                                            </h3>
                                            <p class="text-[11px] text-slate-400 truncate mt-0.5 font-medium">
                                                {{ $card['race']['name'] ?? '' }} • {{ $card['weapon']['name'] ?? '' }}
                                                <span class="text-indigo-400 font-semibold">({{ $card['power']['name'] ?? '' }})</span>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- ========================================================================= --}}
                                    {{-- FACE VERSO (RÉVÉLÉE AU CLIC - STATISTIQUES & PASSIFS)                      --}}
                                    {{-- ========================================================================= --}}
                                    <div
                                        @click="if (!$event.target.closest('button, a')) flipped = false"
                                        class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} bg-slate-950/95 backdrop-blur-md overflow-hidden p-3.5 flex flex-col justify-between backface-hidden rotate-y-180 shadow-2xl cursor-pointer"
                                        :class="{ 'pointer-events-auto': flipped, 'pointer-events-none': !flipped }"
                                        style="width: 270px; height: 465px; min-height: 465px;"
                                    >
                                        <div>
                                            {{-- En-tête Verso : Nom, Rareté, Bouton Retourner --}}
                                            <div class="flex items-center justify-between gap-1.5 mb-2 pb-1.5 border-b border-slate-800">
                                                <div class="flex items-center gap-1 min-w-0">
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider {{ $badgeStyle }} shrink-0">
                                                        {{ $card['rarity_label'] }}
                                                    </span>
                                                    <h4 class="text-xs font-black text-white truncate" title="{{ $card['name'] }}">
                                                        {{ $card['name'] }}
                                                    </h4>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click.stop="flipped = false"
                                                    class="p-1 rounded-lg text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition shrink-0"
                                                    title="Retourner la carte (voir le recto)"
                                                >
                                                    🔄
                                                </button>
                                            </div>

                                            {{-- Tri-Slot Compact --}}
                                            <div class="grid grid-cols-3 gap-1 py-1 px-1.5 rounded-xl bg-slate-900 border border-slate-800/80 mb-2 text-center text-[10px]">
                                                <div>
                                                    <span class="text-xs block">{{ $card['race']['icon'] ?? '👤' }}</span>
                                                    <span class="text-[8px] text-slate-400 truncate block">{{ $card['race']['name'] ?? '' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-xs block">{{ $card['weapon']['icon'] ?? '⚔️' }}</span>
                                                    <span class="text-[8px] text-slate-400 truncate block">{{ $card['weapon']['name'] ?? '' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-xs block">{{ $card['power']['icon'] ?? '🔮' }}</span>
                                                    <span class="text-[8px] text-indigo-300 truncate block">{{ $card['power']['name'] ?? '' }}</span>
                                                </div>
                                            </div>

                                            {{-- Barres de stats techniques --}}
                                            <div class="space-y-1 mb-2 bg-slate-900/60 p-2 rounded-xl border border-slate-800/50 text-[10px]">
                                                <div>
                                                    <div class="flex justify-between mb-0.5">
                                                        <span class="text-rose-400 font-semibold">❤️ PV</span>
                                                        <span class="font-mono text-white">{{ $card['hp'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($card['hp'] / 140) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between mb-0.5">
                                                        <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                                        <span class="font-mono text-white">{{ $card['strength'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($card['strength'] / 100) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between mb-0.5">
                                                        <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                                                        <span class="font-mono text-white">{{ $card['speed'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($card['speed'] / 100) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex justify-between mb-0.5">
                                                        <span class="text-emerald-400 font-semibold">🛡️ Défense</span>
                                                        <span class="font-mono text-white">{{ $card['defense'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, ($card['defense'] / 100) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Synergies actives --}}
                                            @if (!empty($card['synergies_data']))
                                                <div class="mb-1 space-y-1">
                                                    @foreach ($card['synergies_data'] as $syn)
                                                        <div class="px-2 py-0.5 rounded text-[9px] font-bold border flex items-center justify-between {{ !empty($syn['is_unstable']) ? 'bg-amber-950/60 border-amber-600/70 text-amber-200' : 'bg-indigo-950/50 border-indigo-500/50 text-indigo-200' }}">
                                                            <span class="truncate">{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                                            @if (!empty($syn['is_unstable']))
                                                                <span class="text-[8px] text-amber-400 font-mono ml-1">Instable</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="mb-1 px-2 py-1 rounded bg-slate-900/50 border border-slate-800/40 text-center">
                                                    <span class="text-[9px] text-slate-500 italic">Aucune synergie active</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Pied de carte Verso : Palmarès ou Statut Bot --}}
                                        <div class="pt-1.5 border-t border-slate-800 text-center">
                                            @if ($side === 'challenger')
                                                <span class="text-[10px] text-slate-400 font-mono">
                                                    🏆 Palmarès : {{ $card['wins'] }}V - {{ $card['losses'] }}D
                                                </span>
                                            @else
                                                <span class="text-[10px] text-rose-400 font-mono">
                                                    🤖 Combattant d'entraînement
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- COLONNE DROITE : POSTE DE COMMANDE, ACTIONS, JOURNAL & BILAN (ZERO-SCROLL) --}}
            {{-- ========================================================================= --}}
            <div class="bg-slate-900/80 border border-slate-800/90 rounded-3xl p-5 shadow-2xl backdrop-blur-md flex flex-col justify-between space-y-4 h-full w-full">
                
                {{-- En-tête : Réserve de Tickets & Timer en Direct --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚔️</span>
                        <div>
                            <h2 class="font-extrabold text-sm text-white uppercase tracking-wider">Arène de Duel 1v1</h2>
                            <p class="text-[11px] text-slate-400">Matchmaking d'entraînement d'arène</p>
                        </div>
                    </div>

                    {{-- Tickets & Timer Alpine --}}
                    <div>
                        @if ($isAdmin)
                            <span class="px-3 py-1 rounded-full bg-purple-500/15 text-purple-300 border border-purple-500/30 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                                <span>👑</span>
                                <span>Tickets illimités</span>
                            </span>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full bg-slate-950 border text-xs font-bold font-mono flex items-center gap-1.5 {{ $arenaTickets > 0 ? 'text-amber-300 border-amber-500/40' : 'text-rose-400 border-rose-500/40' }}">
                                    <span>🎟️</span>
                                    <span>{{ $arenaTickets }}/{{ $maxTickets }}</span>
                                </span>

                                @if ($arenaTickets < $maxTickets)
                                    <div
                                        wire:key="arena-timer-{{ $arenaTickets }}-{{ $nextTicketSeconds }}"
                                        x-data="{
                                            remaining: {{ (int) $nextTicketSeconds }},
                                            formatTime() {
                                                let s = Math.max(0, parseInt(this.remaining, 10) || 0);
                                                let m = Math.floor(s / 60);
                                                let sec = s % 60;
                                                return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
                                            },
                                            init() {
                                                let interval = setInterval(() => {
                                                    if (this.remaining > 0) {
                                                        this.remaining--;
                                                    } else {
                                                        clearInterval(interval);
                                                        $wire.call('refreshTickets');
                                                    }
                                                }, 1000);
                                            }
                                        }"
                                        class="text-[11px] font-mono text-cyan-400 bg-cyan-950/60 px-2.5 py-1 rounded-full border border-cyan-800/40 flex items-center gap-1 shadow-sm"
                                    >
                                        <span>⏳</span>
                                        <span>+1 dans <strong x-text="formatTime()" class="text-cyan-200 font-bold">{{ sprintf('%02d:%02d', floor($nextTicketSeconds / 60), $nextTicketSeconds % 60) }}</strong></span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Message d'erreur éventuel --}}
                @if ($error)
                    <div class="p-3 bg-rose-950/80 border border-rose-700/60 rounded-2xl text-xs font-bold text-rose-200 flex items-center gap-2 animate-pulse">
                        <span>⚠️</span>
                        <span>{{ $error }}</span>
                    </div>
                @endif

                {{-- ZONE CENTRALE DYNAMIQUE : BRIEFING TACTIQUE OU JOURNAL EN DIRECT OU BILAN --}}
                <div class="flex-1 flex flex-col justify-center">
                    @if (!$result)
                        {{-- 1. État Pré-combat : Comparatif Dynamique des Forces (Jauges Bicolores Vert/Rouge) --}}
                        @if ($comparison)
                            <div class="bg-slate-950/70 border border-slate-800/80 rounded-2xl p-5 space-y-4 my-auto shadow-inner">
                                {{-- En-tête du comparatif : Titre & Badge d'Équilibre Global --}}
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-800/80">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-sm">🎯</span>
                                        <span class="text-xs font-black uppercase tracking-wider text-slate-200 truncate">
                                            Comparatif des Forces
                                        </span>
                                    </div>

                                    {{-- Indicateur d'avantage de Puissance --}}
                                    @if ($comparison['power_diff'] > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950/80 border border-emerald-800/60 text-emerald-300 flex items-center gap-1 shadow-sm shrink-0">
                                            <span>▲</span>
                                            <span>Avantage Héros (+{{ $comparison['power_diff'] }})</span>
                                        </span>
                                    @elseif ($comparison['power_diff'] < 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-950/80 border border-rose-800/60 text-rose-300 flex items-center gap-1 shadow-sm shrink-0">
                                            <span>▼</span>
                                            <span>Avantage Bot (+{{ abs($comparison['power_diff']) }})</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950/80 border border-amber-800/60 text-amber-300 shrink-0">
                                            Équilibre Parfait
                                        </span>
                                    @endif
                                </div>

                                {{-- Bandeau des Duellistes & Initiative --}}
                                <div class="flex items-center justify-between text-[11px] bg-slate-900/60 px-3.5 py-2 rounded-xl border border-slate-800/50">
                                    {{-- Combattant Joueur --}}
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                                        <span class="font-extrabold text-white truncate max-w-[160px]">{{ $comparison['challenger']['name'] }}</span>
                                        <span class="text-[10px] font-mono font-bold text-amber-300 bg-slate-950/80 px-1.5 py-0.5 rounded border border-slate-800 shrink-0">⚡{{ $comparison['challenger']['power_score'] }}</span>
                                    </div>

                                    {{-- Badge Initiative central --}}
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold border shrink-0 {{ $comparison['initiative']['badge'] }}">
                                        {{ $comparison['initiative']['icon'] }} {{ $comparison['initiative']['label'] }}
                                    </span>

                                    {{-- Adversaire Bot --}}
                                    <div class="flex items-center gap-2 min-w-0 justify-end">
                                        <span class="text-[10px] font-mono font-bold text-amber-300 bg-slate-950/80 px-1.5 py-0.5 rounded border border-slate-800 shrink-0">⚡{{ $comparison['opponent']['power_score'] }}</span>
                                        <span class="font-extrabold text-slate-300 truncate max-w-[160px]">{{ $comparison['opponent']['name'] }}</span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400 shrink-0"></span>
                                    </div>
                                </div>

                                {{-- LES 4 BARRES HORIZONTALES DE CONFRONTATION (VERT GAUCHE -> DROITE / ROUGE DROITE -> GAUCHE) --}}
                                <div class="space-y-3">
                                    @foreach ($comparison['stats'] as $sKey => $s)
                                        <div class="space-y-1">
                                            {{-- Ligne d'en-tête de la statistique : Joueur (gauche), Nom & Icône (centre), Bot (droite) --}}
                                            <div class="flex items-center justify-between text-xs font-mono">
                                                {{-- Valeur Joueur --}}
                                                <div class="flex items-center gap-1.5 min-w-[70px]">
                                                    <span class="font-extrabold text-sm {{ $s['diff'] > 0 ? 'text-emerald-400' : ($s['diff'] < 0 ? 'text-slate-400' : 'text-slate-200') }}">
                                                        {{ $s['c_val'] }}
                                                    </span>
                                                    @if ($s['diff'] > 0)
                                                        <span class="text-[9px] px-1 py-0.2 rounded bg-emerald-950/90 border border-emerald-800/80 text-emerald-300 font-bold">
                                                            +{{ $s['diff'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Libellé au centre avec icône --}}
                                                <div class="text-[11px] font-sans font-bold text-slate-300 flex items-center gap-1 select-none">
                                                    <span>{{ $s['icon'] }}</span>
                                                    <span>{{ $s['label'] }}</span>
                                                </div>

                                                {{-- Valeur Bot --}}
                                                <div class="flex items-center gap-1.5 min-w-[70px] justify-end">
                                                    @if ($s['diff'] < 0)
                                                        <span class="text-[9px] px-1 py-0.2 rounded bg-rose-950/90 border border-rose-800/80 text-rose-300 font-bold">
                                                            +{{ abs($s['diff']) }}
                                                        </span>
                                                    @endif
                                                    <span class="font-extrabold text-sm {{ $s['diff'] < 0 ? 'text-rose-400' : ($s['diff'] > 0 ? 'text-slate-400' : 'text-slate-200') }}">
                                                        {{ $s['o_val'] }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Jauge bicolore horizontale de ratio --}}
                                            <div class="relative h-3 rounded-full bg-slate-950 border border-slate-800/90 overflow-hidden flex shadow-inner">
                                                {{-- Segment Vert (Joueur, de gauche à droite) --}}
                                                <div
                                                    class="h-full bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-400 transition-all duration-300"
                                                    style="width: {{ $s['ratio_c'] }}%;"
                                                    title="Joueur : {{ $s['c_val'] }} ({{ $s['ratio_c'] }}%)"
                                                ></div>

                                                {{-- Ligne de démarcation blanche lumineuse à l'intersection --}}
                                                <div class="w-0.5 h-full bg-white shadow-[0_0_6px_white] z-10 shrink-0"></div>

                                                {{-- Segment Rouge (Bot, de droite à gauche) --}}
                                                <div
                                                    class="h-full bg-gradient-to-r from-rose-500 via-rose-600 to-rose-700 transition-all duration-300 flex-1"
                                                    title="Bot : {{ $s['o_val'] }} ({{ $s['ratio_o'] }}%)"
                                                ></div>

                                                {{-- Marqueur subtil d'équilibre 50% au centre --}}
                                                <div class="absolute inset-y-0 left-1/2 w-px bg-white/20 pointer-events-none z-0"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Comparatif des Synergies Actives en bas --}}
                                <div class="pt-2 border-t border-slate-800/70 grid grid-cols-2 gap-2 text-[10px]">
                                    {{-- Synergies Joueur --}}
                                    <div class="bg-slate-900/50 p-2 rounded-xl border border-slate-800/60 space-y-1">
                                        <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center gap-1">
                                            <span class="text-emerald-400">●</span>
                                            <span>Joueur ({{ count($comparison['challenger']['synergies'] ?? []) }})</span>
                                        </div>
                                        @if (!empty($comparison['challenger']['synergies']))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($comparison['challenger']['synergies'] as $syn)
                                                    <span class="px-1.5 py-0.5 rounded bg-purple-950/80 border border-purple-800/60 text-purple-200 font-semibold truncate text-[9px]">
                                                        🔮 {{ $syn }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-500 italic block text-[9px]">Aucune synergie active</span>
                                        @endif
                                    </div>

                                    {{-- Synergies Bot --}}
                                    <div class="bg-slate-900/50 p-2 rounded-xl border border-slate-800/60 space-y-1 text-right">
                                        <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center justify-end gap-1">
                                            <span>Bot ({{ count($comparison['opponent']['synergies'] ?? []) }})</span>
                                            <span class="text-rose-400">●</span>
                                        </div>
                                        @if (!empty($comparison['opponent']['synergies']))
                                            <div class="flex flex-wrap gap-1 justify-end">
                                                @foreach ($comparison['opponent']['synergies'] as $syn)
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-950/80 border border-rose-800/60 text-rose-200 font-semibold truncate text-[9px]">
                                                        🔮 {{ $syn }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-500 italic block text-[9px]">Aucune synergie active</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        {{-- 2. État Combat en cours / Résolu : Journal de combat & Bilan --}}
                        <div class="flex flex-col h-full space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                                    <span>📜</span>
                                    <span>Journal des Échanges</span>
                                </h3>
                                <button
                                    x-show="!done"
                                    x-on:click="finish()"
                                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-lg border border-slate-700 shadow-sm transition hover:scale-105 active:scale-95 flex items-center gap-1"
                                >
                                    Passer le combat ⏭
                                </button>
                            </div>

                            {{-- Boîte de défilement des logs --}}
                            <div x-ref="log" class="h-72 overflow-y-auto space-y-1 font-mono text-xs bg-slate-950/90 rounded-2xl p-4 border border-slate-800/90 shadow-inner">
                                <template x-for="(entry, i) in entries" :key="i">
                                    <p :class="style(entry)" x-text="entry.message" class="leading-relaxed"></p>
                                </template>
                            </div>

                            {{-- Bilan Final (s'affiche instantanément dès que l'animation est finie) --}}
                            <div x-show="done" x-cloak x-transition class="bg-slate-950/95 border border-slate-800 rounded-2xl p-3.5 text-center space-y-2">
                                @if ($result['draw'])
                                    <div class="text-xl font-black text-amber-400 flex items-center justify-center gap-1.5">
                                        <span>🩸</span>
                                        <span>ÉGALITÉ SANGLANTE !</span>
                                    </div>
                                    <div class="text-xs text-slate-400">Double K.O. simultané. Aucun changement d'Élo.</div>
                                @elseif ($result['won'])
                                    <div class="text-2xl font-black text-emerald-400 flex items-center justify-center gap-2">
                                        <span>🏆</span>
                                        <span>VICTOIRE ÉCLATANTE !</span>
                                    </div>
                                @else
                                    <div class="text-2xl font-black text-rose-400 flex items-center justify-center gap-2">
                                        <span>💀</span>
                                        <span>DÉFAITE</span>
                                    </div>
                                @endif

                                <div class="text-[11px] text-slate-500 font-mono">
                                    {{ $result['turns'] }} tour(s) {{ $result['reason'] === 'turn_limit' ? '• décision aux points' : '' }}
                                </div>

                                {{-- Récompenses récoltées --}}
                                <div class="flex justify-center gap-2 text-xs font-bold font-mono pt-1">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 {{ $result['elo_delta'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $result['elo_delta'] >= 0 ? '+' : '' }}{{ $result['elo_delta'] }} Élo
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-amber-400">
                                        +{{ $result['coins'] }} 🪙
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-sky-400">
                                        +{{ $result['xp'] }} XP
                                    </span>
                                </div>

                                {{-- Changement de ligue --}}
                                @if ($result['league_change'])
                                    @if ($result['league_change']['type'] === 'promotion')
                                        <div
                                            class="mx-auto max-w-xs rounded-xl border-2 bg-slate-900/80 px-3 py-2 animate-bounce"
                                            style="border-color: {{ $result['league_change']['color'] }}; box-shadow: 0 0 24px {{ $result['league_change']['color'] }}55"
                                        >
                                            <div class="text-2xl">🎉 {{ $result['league_change']['icon'] }} 🎉</div>
                                            <div class="text-sm font-black uppercase tracking-wider" style="color: {{ $result['league_change']['color'] }}">Promotion !</div>
                                            <div class="text-xs text-slate-300">
                                                Tu entres en ligue <strong style="color: {{ $result['league_change']['color'] }}">{{ $result['league_change']['label'] }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mx-auto max-w-xs rounded-xl border-2 border-rose-700/60 bg-rose-950/40 px-3 py-2">
                                            <div class="text-2xl">📉 {{ $result['league_change']['icon'] }}</div>
                                            <div class="text-sm font-black uppercase tracking-wider text-rose-400">Relégation</div>
                                            <div class="text-xs text-slate-300">
                                                Tu redescends en ligue <strong style="color: {{ $result['league_change']['color'] }}">{{ $result['league_change']['label'] }}</strong>. Quelques victoires et tu remontes !
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- BOUTONS D'ACTION DU POSTE DE COMMANDE --}}
                <div class="pt-2 border-t border-slate-800/80">
                    @if (!$result)
                        <button
                            type="button"
                            wire:click="fight"
                            wire:loading.attr="disabled"
                            @disabled(!$isAdmin && $arenaTickets === 0)
                            class="w-full py-4 text-white font-black text-base rounded-2xl shadow-xl transition-all duration-200 flex items-center justify-center gap-2 {{ !$isAdmin && $arenaTickets === 0 ? 'opacity-40 cursor-not-allowed bg-slate-800 text-slate-500 shadow-none' : 'bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 shadow-rose-900/40 hover:scale-[1.02] active:scale-[0.98] cursor-pointer' }}"
                        >
                            <span wire:loading.remove wire:target="fight" class="flex items-center gap-2">
                                <span>⚔️</span>
                                <span>Combattre !</span>
                                <span class="text-xs font-bold opacity-80">(-1 🎟️)</span>
                            </span>
                            <span wire:loading wire:target="fight" class="flex items-center gap-2">
                                <span class="animate-spin">⏳</span>
                                <span>Combat en cours...</span>
                            </span>
                        </button>
                    @else
                        <div class="grid grid-cols-2 gap-2.5">
                            <button
                                type="button"
                                wire:click="rematch"
                                class="py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-extrabold text-xs rounded-2xl border border-slate-700 shadow-md transition hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Rejouer contre le même adversaire"
                            >
                                <span>🔄</span>
                                <span>Revanche</span>
                            </button>
                            <button
                                type="button"
                                wire:click="newOpponent"
                                class="py-3.5 bg-gradient-to-r from-indigo-600 to-rose-600 hover:from-indigo-500 hover:to-rose-500 text-white font-extrabold text-xs rounded-2xl shadow-lg transition hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Chercher un nouvel adversaire"
                            >
                                <span>⚔️</span>
                                <span>Autre adversaire</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODALE FLOTTANTE DE SÉLECTION DU COMBATTANT (AVEC RECHERCHE & FILTRES)     --}}
        {{-- ========================================================================= --}}
        @if ($showFighterModal)
            <div
                class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 transition-opacity duration-300"
                x-data
                x-on:keydown.escape.window="$wire.closeFighterModal()"
            >
                <div
                    class="bg-slate-900 border border-slate-700/80 rounded-3xl max-w-4xl w-full max-h-[88vh] flex flex-col shadow-2xl overflow-hidden"
                    @click.outside="$wire.closeFighterModal()"
                >
                    {{-- En-tête de la modale --}}
                    <div class="p-5 border-b border-slate-800 flex items-center justify-between gap-3 bg-slate-950/60">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚔️</span>
                            <div>
                                <h3 class="text-base font-extrabold text-white">Sélectionner ton Combattant</h3>
                                <p class="text-xs text-slate-400">Choisis le héros qui entrera dans l'arène de duel</p>
                            </div>
                        </div>
                        <button
                            wire:click="closeFighterModal"
                            class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-sm font-bold transition hover:bg-slate-700"
                            title="Fermer la sélection"
                        >
                            ✕
                        </button>
                    </div>

                    {{-- Barre d'outils : Recherche & Filtres --}}
                    <div class="p-4 border-b border-slate-800/80 bg-slate-950/40 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            {{-- Champ Recherche --}}
                            <div class="sm:col-span-8 relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 text-sm pointer-events-none">🔍</span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.250ms="search"
                                    placeholder="Rechercher par nom, race, arme ou pouvoir..."
                                    class="w-full pl-9 pr-8 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition"
                                >
                                @if (!empty($search))
                                    <button
                                        wire:click="$set('search', '')"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-500 hover:text-slate-300 text-xs"
                                    >
                                        ✕
                                    </button>
                                @endif
                            </div>

                            {{-- Tri --}}
                            <div class="sm:col-span-4">
                                <select
                                    wire:model.live="sortBy"
                                    class="w-full py-2 px-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-amber-300 font-semibold focus:outline-none focus:border-amber-400 cursor-pointer"
                                >
                                    <option value="power_desc">⚡ Puissance (Max → Min)</option>
                                    <option value="power_asc">⚡ Puissance (Min → Max)</option>
                                    <option value="wins_desc">🏆 Victoires (Max)</option>
                                    <option value="date_desc">🕒 Plus récents</option>
                                </select>
                            </div>
                        </div>

                        {{-- Filtres de Rareté rapides --}}
                        <div class="flex items-center gap-1.5 flex-wrap text-xs">
                            <span class="text-slate-400 text-[11px] font-semibold mr-1">Rareté :</span>
                            <button
                                wire:click="$set('rarityFilter', '')"
                                class="px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase transition {{ empty($rarityFilter) ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                            >
                                Tous
                            </button>
                            @foreach (['common' => 'Commune', 'rare' => 'Rare', 'epic' => 'Épique', 'legendary' => 'Légendaire'] as $rKey => $rLabel)
                                <button
                                    wire:click="$set('rarityFilter', '{{ $rKey }}')"
                                    class="px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase transition {{ $rarityFilter === $rKey ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                                >
                                    {{ $rLabel }}
                                </button>
                            @endforeach

                            @if (!empty($search) || !empty($rarityFilter))
                                <button
                                    wire:click="resetModalFilters"
                                    class="ml-auto text-[11px] text-rose-400 hover:text-rose-300 font-semibold underline"
                                >
                                    Réinitialiser
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Corps : Grille compacte des Héros éligibles --}}
                    <div class="p-5 overflow-y-auto flex-1 max-h-[58vh]">
                        @if ($this->modalFighters->isEmpty())
                            <div class="text-center py-12 space-y-3">
                                <span class="text-4xl">🔍</span>
                                <p class="text-sm font-bold text-slate-300">Aucun combattant ne correspond à cette recherche.</p>
                                <button
                                    wire:click="resetModalFilters"
                                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-amber-300 rounded-xl transition"
                                >
                                    Effacer les filtres
                                </button>
                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                                @foreach ($this->modalFighters as $char)
                                    @php
                                        $cRarityKey = $char->rarity instanceof \App\Enums\Rarity ? $char->rarity->value : (string) $char->rarity;
                                        $cBorder = match($cRarityKey) {
                                            'legendary' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950',
                                            'epic'      => 'border-purple-500/70 bg-gradient-to-b from-purple-950/30 via-slate-900 to-slate-950',
                                            'rare'      => 'border-cyan-400/60 bg-gradient-to-b from-cyan-950/30 via-slate-900 to-slate-950',
                                            default     => 'border-slate-800 bg-slate-900',
                                        };
                                        $cBadge = match($cRarityKey) {
                                            'legendary' => 'bg-amber-400 text-slate-950 font-black',
                                            'epic'      => 'bg-purple-500 text-white font-bold',
                                            'rare'      => 'bg-cyan-500 text-slate-950 font-bold',
                                            default     => 'bg-slate-700 text-slate-200 font-medium',
                                        };
                                        $isSelected = $char->id === $fighterId;
                                    @endphp

                                    <button
                                        type="button"
                                        wire:key="modal-pick-{{ $char->id }}"
                                        wire:click="selectFighter('{{ $char->id }}')"
                                        class="group rounded-2xl border p-3 flex flex-col justify-between text-left transition-all duration-200 relative overflow-hidden cursor-pointer {{ $cBorder }} {{ $isSelected ? 'ring-2 ring-rose-500 shadow-[0_0_15px_rgba(244,63,94,0.35)] scale-[1.02]' : 'hover:scale-[1.02] hover:border-slate-600' }}"
                                        style="height: 250px;"
                                    >
                                        {{-- En-tête de carte compacte --}}
                                        <div class="flex items-center justify-between gap-1 w-full">
                                            <span class="px-2 py-0.5 rounded text-[8px] uppercase tracking-wider {{ $cBadge }}">
                                                {{ $char->rarity->label() }}
                                            </span>
                                            <span class="text-xs font-mono font-bold text-amber-300">
                                                ⚡ {{ $char->power_score }}
                                            </span>
                                        </div>

                                        {{-- Avatar Paper Doll --}}
                                        <div class="my-auto flex items-center justify-center overflow-hidden py-1">
                                            <x-character-avatar :character="$char" class="max-w-[110px]" />
                                        </div>

                                        {{-- Infos Héros --}}
                                        <div class="w-full pt-1.5 border-t border-slate-800/80 space-y-0.5 text-center">
                                            <div class="font-extrabold text-xs text-white truncate group-hover:text-amber-300 transition-colors">
                                                {{ $char->name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate">
                                                {{ $char->race_data['icon'] ?? '' }} {{ $char->weapon_data['icon'] ?? '' }} {{ $char->power_data['icon'] ?? '' }}
                                            </div>
                                            <div class="text-[9px] font-mono text-slate-500 flex items-center justify-center gap-1">
                                                @if ($isSelected)
                                                    <span class="text-rose-400 font-bold">✓ En combat</span>
                                                @else
                                                    <span>🏆 {{ $char->wins }}V - {{ $char->losses }}D</span>
                                                @endif
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
