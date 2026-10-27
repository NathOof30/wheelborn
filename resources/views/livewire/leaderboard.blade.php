<div class="space-y-6 pb-24">
    {{-- Onglets de ligue --}}
    <div class="flex flex-wrap justify-center gap-2">
        @foreach ($this->leagueTabs as $tab)
            <button
                wire:key="tab-{{ $tab['value'] }}"
                wire:click="setLeague('{{ $tab['value'] }}')"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition {{ $league === $tab['value'] ? 'bg-slate-800 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200' }}"
                @if ($league === $tab['value']) style="color: {{ $tab['color'] }}; border-color: {{ $tab['color'] }}" @endif
            >
                {{ $tab['icon'] }} {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    @if (empty($this->players))
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-10 text-center">
            <p class="text-4xl">🏟️</p>
            <p class="text-slate-400 text-sm mt-2">Aucun joueur classé dans cette ligue pour l'instant.</p>
        </div>
    @else
        {{-- Podium du Top 3 --}}
        <div class="flex items-end justify-center gap-3 sm:gap-6 pt-6">
            @foreach ($this->podium as $player)
                <div wire:key="podium-{{ $player['id'] }}" class="flex flex-col items-center w-28 sm:w-36">
                    <div class="text-4xl mb-1">{{ $player['medal'] }}</div>
                    <a
                        href="{{ route('player.profile', $player['name']) }}"
                        class="font-extrabold text-white hover:text-amber-300 transition text-sm truncate max-w-full flex items-center gap-1 group"
                        title="Voir le profil de {{ $player['name'] }}"
                    >
                        <span>{{ $player['name'] }}</span>
                        <span class="text-[10px] opacity-0 group-hover:opacity-100 transition">🔍</span>
                    </a>
                    <div class="text-xs mb-2" style="color: {{ $player['league']['color'] }}">
                        {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                    </div>
                    <button
                        type="button"
                        wire:click="inspectPlayer({{ $player['id'] }})"
                        class="w-full rounded-t-2xl border-2 bg-slate-950/80 flex flex-col items-center justify-start pt-3 hover:scale-105 transition-transform duration-300 cursor-pointer"
                        style="height: {{ $player['height'] }}px; border-color: {{ $player['color'] }}; box-shadow: 0 0 25px {{ $player['color'] }}66, inset 0 0 20px {{ $player['color'] }}22"
                        title="Inspecter le profil de {{ $player['name'] }}"
                    >
                        <span class="text-3xl font-black" style="color: {{ $player['color'] }}; text-shadow: 0 0 12px {{ $player['color'] }}">
                            #{{ $player['rank'] }}
                        </span>
                        <span class="font-mono font-bold text-white text-sm">{{ $player['elo'] }} Élo</span>
                        <span class="text-[9px] text-slate-400 mt-1 uppercase font-semibold">Inspecter 👁️</span>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Suite du classement --}}
        @if (!empty($this->playersAfterPodium))
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden">
                @foreach ($this->playersAfterPodium as $player)
                    <div
                        wire:key="row-{{ $player['id'] }}"
                        class="flex items-center gap-4 px-5 py-3 border-b border-slate-800/70 last:border-0 hover:bg-slate-800/40 transition {{ $player['id'] === auth()->id() ? 'bg-indigo-500/10' : '' }}"
                    >
                        <span class="w-10 font-mono font-black text-slate-400">#{{ $player['rank'] }}</span>
                        <div class="flex-1 flex items-center gap-2 min-w-0">
                            <a
                                href="{{ route('player.profile', $player['name']) }}"
                                class="font-bold text-white hover:text-amber-300 transition truncate"
                                title="Voir la fiche de {{ $player['name'] }}"
                            >
                                {{ $player['name'] }}
                            </a>
                            <button
                                type="button"
                                wire:click="inspectPlayer({{ $player['id'] }})"
                                class="text-slate-500 hover:text-amber-300 text-xs px-1.5 py-0.5 rounded bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 transition cursor-pointer"
                                title="Aperçu rapide"
                            >
                                🔍
                            </button>
                        </div>
                        <span class="hidden sm:inline text-xs text-slate-500">{{ $player['battles'] }} combat(s)</span>
                        <span class="text-xs font-bold w-24 text-right" style="color: {{ $player['league']['color'] }}">
                            {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                        </span>
                        <span class="w-20 text-right font-mono font-bold text-amber-300">{{ $player['elo'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <p class="text-center text-[11px] text-slate-600">Classement mis à jour toutes les minutes.</p>

    {{-- Ligne épinglée du joueur connecté --}}
    @if ($this->currentPlayer)
        <div class="fixed bottom-4 inset-x-0 px-4 z-40">
            <div
                class="max-w-3xl mx-auto flex items-center gap-4 px-5 py-3 rounded-2xl border-2 bg-slate-950/95 backdrop-blur-md shadow-2xl"
                style="border-color: {{ $this->currentPlayer['league']['color'] }}"
            >
                <span class="text-xs uppercase tracking-widest text-slate-500">Toi</span>
                <span class="font-mono font-black text-white">#{{ $this->currentPlayer['rank'] }}</span>
                <span class="flex-1 font-bold text-white truncate">{{ $this->currentPlayer['name'] }}</span>
                <span class="text-xs font-bold" style="color: {{ $this->currentPlayer['league']['color'] }}">
                    {{ $this->currentPlayer['league']['icon'] }} {{ $this->currentPlayer['league']['label'] }}
                </span>
                <span class="font-mono font-bold text-amber-300">{{ $this->currentPlayer['elo'] }} Élo</span>
            </div>
        </div>
    @endif

    {{-- Modale d'Inspection Rival depuis le Classement --}}
    @if ($showInspectorModal && $inspectedPlayer)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 backdrop-blur-md p-4 animate-fade-in">
            <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5">
                <button
                    type="button"
                    wire:click="closeInspector"
                    class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center font-bold text-sm transition cursor-pointer"
                >
                    ✕
                </button>

                <div class="flex items-center gap-4">
                    <div 
                        class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center border-2 bg-slate-950 shadow-lg shrink-0"
                        style="border-color: {{ $inspectedPlayer['league']['color'] ?? '#6366f1' }}"
                    >
                        <span class="text-2xl">{{ $inspectedPlayer['league']['icon'] ?? '🛡️' }}</span>
                        <span class="text-[8px] uppercase font-bold text-amber-300">{{ $inspectedPlayer['league']['name'] ?? 'Ligue' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-extrabold tracking-wider text-amber-400">Joueur Classé</span>
                        <h3 class="text-xl font-black text-white font-display">
                            {{ $inspectedPlayer['name'] }}
                        </h3>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-950 border border-amber-400/30 text-amber-300 text-[10px] font-semibold mt-1">
                            <span>⚡ Niveau {{ $inspectedPlayer['level'] }}</span>
                            <span>·</span>
                            <span>« {{ $inspectedPlayer['title'] }} »</span>
                        </div>
                    </div>
                </div>

                {{-- Stats Palmarès Rapides --}}
                <div class="grid grid-cols-3 gap-2 text-center text-xs bg-slate-950/70 p-3 rounded-2xl border border-slate-800">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Élo</span>
                        <span class="font-mono font-bold text-amber-300">{{ $inspectedPlayer['elo'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Duels</span>
                        <span class="font-mono font-bold text-white">{{ $inspectedPlayer['battles'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Winrate</span>
                        <span class="font-mono font-bold text-emerald-400">{{ $inspectedPlayer['winrate'] }}%</span>
                    </div>
                </div>

                {{-- Héros favoris en vitrine --}}
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <span>⭐</span>
                        <span>Champions de prédilection</span>
                    </span>
                    @if (!empty($inspectedPlayer['favorites']))
                        <div class="space-y-1.5">
                            @foreach ($inspectedPlayer['favorites'] as $fav)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/50 border border-slate-800/80 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-bold font-mono">⚡ {{ $fav['power_score'] }}</span>
                                        <span class="font-bold text-white">{{ $fav['name'] }}</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400">
                                        {{ $fav['race'] }} · {{ $fav['weapon'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 italic p-3 rounded-xl bg-slate-950/40 border border-slate-800/50 text-center">
                            Aucun favori exposé actuellement.
                        </p>
                    @endif
                </div>

                {{-- Bouton vers Profil Public Complet --}}
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-3">
                    <button
                        type="button"
                        wire:click="closeInspector"
                        class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition cursor-pointer"
                    >
                        Fermer
                    </button>
                    <a
                        href="{{ $inspectedPlayer['profile_url'] }}"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 text-xs font-black transition shadow-lg shadow-amber-500/20"
                    >
                        <span>Voir la fiche de destinée</span>
                        <span>➔</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
