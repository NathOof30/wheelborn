<div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-3">
    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">📜 Derniers combats</h3>

    @if (empty($this->battles))
        <p class="text-sm text-slate-500 text-center py-4">Aucun combat pour l'instant. Lance ton premier duel !</p>
    @else
        <div class="divide-y divide-slate-800/70">
            @foreach ($this->battles as $battle)
                <div wire:key="battle-{{ $battle['id'] }}" class="flex items-center gap-3 py-2.5">
                    {{-- Victoire / Défaite / Nul --}}
                    @if ($battle['outcome'] === 'win')
                        <span class="w-9 text-center text-xs font-black rounded-lg py-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">V</span>
                    @elseif ($battle['outcome'] === 'loss')
                        <span class="w-9 text-center text-xs font-black rounded-lg py-1 bg-rose-500/15 text-rose-400 border border-rose-500/30">D</span>
                    @else
                        <span class="w-9 text-center text-xs font-black rounded-lg py-1 bg-amber-500/15 text-amber-400 border border-amber-500/30">N</span>
                    @endif

                    {{-- Adversaire --}}
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-white truncate">
                            {{ $battle['opponent_kind'] === 'player' ? '⚔️' : '🤖' }} {{ $battle['opponent'] }}
                        </div>
                        <div class="text-[11px] text-slate-500">{{ $battle['ago'] }}</div>
                    </div>

                    {{-- Variation d'Élo --}}
                    <span class="w-16 text-right font-mono text-sm font-bold {{ $battle['elo_delta'] > 0 ? 'text-emerald-400' : ($battle['elo_delta'] < 0 ? 'text-rose-400' : 'text-slate-400') }}">
                        {{ $battle['elo_delta'] > 0 ? '+' : '' }}{{ $battle['elo_delta'] }}
                    </span>

                    {{-- Revoir le combat dans l'arène --}}
                    @if ($battle['can_replay'])
                        <button
                            wire:click="$dispatch('replay-battle', { battleId: {{ $battle['id'] }} })"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600/30 hover:bg-indigo-600/50 border border-indigo-500/50 text-indigo-200 hover:text-white transition"
                        >
                            ▶ Revoir
                        </button>
                    @else
                        <span class="px-3 py-1.5 text-xs text-slate-600" title="Combat enregistré avant l'ajout du replay">—</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
