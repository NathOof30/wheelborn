<div class="codex-panel codex-panel--framed p-5 sm:p-6 space-y-4">
    <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-parchment-border/70">
        <h3 class="font-display text-sm font-bold uppercase tracking-wider text-ink flex items-center gap-2">
            <svg class="w-4 h-4 text-brass-dark" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 4 H16 L19 7 V20 H5 Z M9 10 H15 M9 14 H15 M9 18 H12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Derniers combats</span>
        </h3>
        <span class="font-body text-xs italic text-ink-muted">10 dernières entrées du registre</span>
    </div>

    @if (empty($this->battles))
        <p class="font-body text-sm italic text-ink-muted text-center py-5">
            Aucun combat pour l'instant. Lancez votre premier duel !
        </p>
    @else
        <div class="divide-y divide-parchment-border/60">
            @foreach ($this->battles as $battle)
                <div wire:key="battle-{{ $battle['id'] }}" class="flex items-center gap-3 py-3">
                    @if ($battle['outcome'] === 'win')
                        <span class="w-8 h-8 rounded-md flex items-center justify-center font-display text-xs font-bold bg-forest/15 text-forest border border-forest/45 shrink-0">V</span>
                    @elseif ($battle['outcome'] === 'loss')
                        <span class="w-8 h-8 rounded-md flex items-center justify-center font-display text-xs font-bold bg-wax/10 text-wax border border-wax/45 shrink-0">D</span>
                    @else
                        <span class="w-8 h-8 rounded-md flex items-center justify-center font-display text-xs font-bold bg-brass/20 text-brass-dark border border-brass-dark/45 shrink-0">N</span>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="font-display text-sm font-bold text-ink truncate">
                            {{ $battle['opponent_kind'] === 'player' ? '⚔️' : '🤖' }} {{ $battle['opponent'] }}
                        </div>
                        <div class="font-body text-xs italic text-ink-muted">{{ $battle['ago'] }}</div>
                    </div>

                    <span class="w-14 text-right font-display text-sm font-bold shrink-0 {{ $battle['elo_delta'] > 0 ? 'text-forest' : ($battle['elo_delta'] < 0 ? 'text-wax' : 'text-ink-muted') }}">
                        {{ $battle['elo_delta'] > 0 ? '+' : '' }}{{ $battle['elo_delta'] }}
                    </span>

                    @if ($battle['can_replay'])
                        <button
                            type="button"
                            wire:click="$dispatch('replay-battle', { battleId: {{ $battle['id'] }} })"
                            class="btn-brass btn-brass--sm shrink-0"
                        >
                            ▶ Revoir
                        </button>
                    @else
                        <span class="px-3 py-1.5 font-body text-xs text-ink-muted" title="Combat enregistré avant l'ajout du replay">—</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
