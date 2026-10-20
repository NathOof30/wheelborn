<div
    x-data="battleAnimation"
    x-on:battle-started.window="play($event.detail)"
    x-on:battle-reset.window="reset()"
    class="space-y-6"
>
    @if ($this->fighters->isEmpty())
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-10 text-center space-y-4">
            <p class="text-5xl">⚔️</p>
            <h2 class="text-xl font-extrabold text-white">Aucun combattant disponible</h2>
            <p class="text-slate-400 text-sm">Invoque d'abord un héros sur la Roue du Destin pour entrer dans l'arène.</p>
            <a href="{{ route('dashboard') }}" class="inline-block px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl transition">
                🎡 Aller au tirage
            </a>
        </div>
    @else
        {{-- Sélection du combattant --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-3">
            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Choisis ton combattant</h3>
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach ($this->fighters as $candidate)
                    <button
                        wire:key="pick-{{ $candidate->id }}"
                        wire:click="selectFighter('{{ $candidate->id }}')"
                        @disabled($result)
                        class="shrink-0 w-40 text-left p-3 rounded-xl border transition disabled:opacity-50 {{ $candidate->id === $fighterId ? 'border-rose-500 bg-rose-500/10' : 'border-slate-700 bg-slate-800/60 hover:border-slate-500' }}"
                    >
                        <div class="font-bold text-sm text-white truncate">{{ $candidate->name }}</div>
                        <div class="text-lg">{{ $candidate->race_data['icon'] ?? '' }} {{ $candidate->weapon_data['icon'] ?? '' }} {{ $candidate->power_data['icon'] ?? '' }}</div>
                        <div class="text-xs text-slate-400">Power {{ $candidate->power_score }} · {{ $candidate->wins }}V / {{ $candidate->losses }}D</div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Face à face --}}
        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 items-stretch">
            @foreach ($this->cards as $side => $card)
                @if ($side === 'opponent')
                    <div class="flex items-center justify-center text-3xl font-black text-rose-500">VS</div>
                @endif

                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4">
                    <div class="flex items-center gap-4">
                        <x-character-avatar :character="$card['character']" />
                        <div class="min-w-0">
                            <div class="text-[10px] uppercase tracking-widest text-slate-500">{{ $card['label'] }}</div>
                            <div class="font-extrabold text-white truncate">{{ $card['name'] }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $card['race']['name'] ?? '' }} · {{ $card['weapon']['name'] ?? '' }} · {{ $card['power']['name'] ?? '' }}
                            </div>
                            <div class="text-xs text-amber-400 font-bold">
                                Power {{ $card['power_score'] }}@if ($card['elo']) · {{ $card['elo'] }} Élo @endif
                            </div>
                        </div>
                    </div>

                    {{-- Barre de PV, animée pendant le combat --}}
                    <div>
                        <div class="flex justify-between text-xs text-slate-400 mb-1">
                            <span>PV</span>
                            <span><span x-text="hp('{{ $side }}', {{ $card['hp'] }})">{{ $card['hp'] }}</span> / {{ $card['hp'] }}</span>
                        </div>
                        <div class="h-3 rounded-full bg-slate-800 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-300 {{ $side === 'challenger' ? 'bg-emerald-500' : 'bg-rose-500' }}"
                                x-bind:style="{ width: pct('{{ $side }}', {{ $card['hp'] }}) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-slate-800/60 rounded-lg py-1.5"><div class="text-slate-500">Force</div><div class="font-bold text-white">{{ $card['strength'] }}</div></div>
                        <div class="bg-slate-800/60 rounded-lg py-1.5"><div class="text-slate-500">Vitesse</div><div class="font-bold text-white">{{ $card['speed'] }}</div></div>
                        <div class="bg-slate-800/60 rounded-lg py-1.5"><div class="text-slate-500">Défense</div><div class="font-bold text-white">{{ $card['defense'] }}</div></div>
                    </div>

                    @if (!empty($card['synergies']))
                        <div class="flex flex-wrap gap-1">
                            @foreach ($card['synergies'] as $synergy)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-500/15 text-purple-300 border border-purple-500/30">✨ {{ $synergy }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Actions --}}
        @if (!$result)
            <div
                class="space-y-3"
                @if (!$isAdmin && $arenaTickets < $maxTickets) wire:poll.10s="refreshTickets" @endif
            >
                {{-- Tickets d'arène --}}
                <div class="flex justify-center items-center gap-2 text-sm">
                    @if ($isAdmin)
                        <span class="px-3 py-1 rounded-full bg-purple-500/15 text-purple-300 border border-purple-500/30 font-bold">🎟️ Tickets illimités</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 font-bold {{ $arenaTickets > 0 ? 'text-amber-300' : 'text-rose-400' }}">
                            🎟️ {{ $arenaTickets }} / {{ $maxTickets }} tickets
                        </span>
                        @if ($arenaTickets < $maxTickets)
                            <span class="text-xs text-slate-500">+1 dans {{ $this->nextTicketIn }}</span>
                        @endif
                    @endif
                </div>

                @if ($error)
                    <p class="text-center text-sm font-semibold text-rose-300 bg-rose-950/60 border border-rose-700/50 rounded-xl px-4 py-2">{{ $error }}</p>
                @endif

                <div class="flex flex-wrap justify-center gap-3">
                    <button wire:click="newOpponent" wire:loading.attr="disabled" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl border border-slate-700 transition">
                        🔄 Autre adversaire
                    </button>
                    <button wire:click="fight" wire:loading.attr="disabled" class="px-8 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-lg shadow-rose-900/40 transition hover:scale-105 active:scale-95 {{ !$isAdmin && $arenaTickets === 0 ? 'opacity-50' : '' }}">
                        ⚔️ Combattre ! <span class="text-xs font-bold opacity-80">(-1 🎟️)</span>
                    </button>
                </div>
            </div>
        @else
            {{-- Animation du combat --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Journal de combat</h3>
                    <button x-show="!done" x-on:click="finish()" class="text-xs text-slate-400 hover:text-white">Passer ⏭</button>
                </div>

                <div x-ref="log" class="h-72 overflow-y-auto space-y-1 font-mono text-sm bg-slate-950/60 rounded-xl p-4">
                    <template x-for="(entry, i) in entries" :key="i">
                        <p :class="style(entry)" x-text="entry.message"></p>
                    </template>
                </div>

                {{-- Bilan --}}
                <div x-show="done" x-cloak x-transition class="text-center space-y-3">
                    @if ($result['draw'])
                        <div class="text-3xl font-black text-amber-400">🩸 ÉGALITÉ SANGLANTE !</div>
                        <div class="text-sm text-slate-400">Les deux combattants sont tombés en même temps. Match nul.</div>
                    @elseif ($result['won'])
                        <div class="text-3xl font-black text-emerald-400">🏆 VICTOIRE</div>
                    @else
                        <div class="text-3xl font-black text-rose-400">💀 DÉFAITE</div>
                    @endif
                    <div class="text-xs text-slate-500">
                        {{ $result['turns'] }} tour(s){{ $result['reason'] === 'turn_limit' ? ' · décision aux points' : '' }}
                    </div>
                    <div class="flex justify-center gap-3 text-sm font-bold">
                        <span class="px-3 py-1 rounded-full bg-slate-800 {{ $result['elo_delta'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $result['elo_delta'] >= 0 ? '+' : '' }}{{ $result['elo_delta'] }} Élo
                        </span>
                        <span class="px-3 py-1 rounded-full bg-slate-800 text-amber-400">+{{ $result['coins'] }} 🪙</span>
                        <span class="px-3 py-1 rounded-full bg-slate-800 text-sky-400">+{{ $result['xp'] }} XP</span>
                    </div>

                    {{-- Changement de ligue --}}
                    @if ($result['league_change'])
                        @if ($result['league_change']['type'] === 'promotion')
                            <div
                                class="mx-auto max-w-sm rounded-2xl border-2 bg-slate-950/80 px-5 py-4 shadow-2xl animate-bounce"
                                style="border-color: {{ $result['league_change']['color'] }}; box-shadow: 0 0 30px {{ $result['league_change']['color'] }}55"
                            >
                                <div class="text-4xl">🎉 {{ $result['league_change']['icon'] }} 🎉</div>
                                <div class="text-lg font-black uppercase tracking-wider" style="color: {{ $result['league_change']['color'] }}">Promotion !</div>
                                <div class="text-sm text-slate-300">
                                    Tu entres en ligue <strong style="color: {{ $result['league_change']['color'] }}">{{ $result['league_change']['label'] }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="mx-auto max-w-sm rounded-2xl border-2 border-rose-700/60 bg-rose-950/40 px-5 py-4">
                                <div class="text-4xl">📉 {{ $result['league_change']['icon'] }}</div>
                                <div class="text-lg font-black uppercase tracking-wider text-rose-400">Relégation</div>
                                <div class="text-sm text-slate-300">
                                    Tu redescends en ligue <strong style="color: {{ $result['league_change']['color'] }}">{{ $result['league_change']['label'] }}</strong>. Quelques victoires et tu remontes !
                                </div>
                            </div>
                        @endif
                    @endif

                    <button wire:click="newOpponent" class="px-8 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl transition hover:scale-105 active:scale-95">
                        ⚔️ Nouveau duel
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
