@php
    use App\View\Presenters\FolioCardPresenter;
@endphp

<div class="space-y-6 pb-20">
    {{-- Onglets de ligue --}}
    <div class="flex flex-wrap justify-center gap-2">
        @foreach ($this->leagueTabs as $tab)
            @php $tabHex = $tab['ink_color'] ?? '#6E4F0E'; @endphp
            <button
                type="button"
                wire:key="tab-{{ $tab['value'] }}"
                wire:click="setLeague('{{ $tab['value'] }}')"
                class="filter-chip {{ $league === $tab['value'] ? 'is-active' : '' }}"
                style="--chip-hex: {{ $tabHex }};"
            >
                <span>{{ $tab['icon'] }}</span>
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    @if (empty($this->players))
        <div class="codex-panel codex-panel--framed max-w-lg mx-auto p-8 text-center space-y-2">
            <p class="font-display text-lg font-bold text-ink">Registre vierge</p>
            <p class="font-body text-sm italic text-ink-muted">Aucun joueur classé dans cette ligue pour l'instant.</p>
        </div>
    @else
        {{-- Podium du Top 3 (ordre visuel 2e · 1er · 3e) --}}
        <div class="codex-panel codex-panel--framed p-5 sm:p-6">
            <div class="flex items-end justify-center gap-3 sm:gap-6 pt-3">
                @foreach ($this->podium as $player)
                    @php $pLeagueInk = FolioCardPresenter::leagueInkColor((string) ($player['league']['value'] ?? 'silver')); @endphp
                    <div wire:key="podium-{{ $player['id'] }}" class="flex flex-col items-center w-28 sm:w-40 min-w-0">
                        <div class="text-3xl sm:text-4xl mb-1 leading-none">{{ $player['medal'] }}</div>
                        <a
                            href="{{ route('player.profile', $player['name']) }}"
                            class="font-display font-bold text-ink hover:text-wax transition text-xs sm:text-sm truncate max-w-full"
                            title="Voir le profil de {{ $player['name'] }}"
                        >
                            {{ $player['name'] }}
                        </a>
                        <div class="font-display text-[11px] font-bold mb-2 truncate max-w-full" style="color: {{ $pLeagueInk }};">
                            {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                        </div>
                        <button
                            type="button"
                            wire:click="inspectPlayer({{ $player['id'] }})"
                            class="w-full rounded-t-lg border-2 bg-parchment-bright hover:bg-parchment-light flex flex-col items-center justify-start pt-3 px-1.5 transition-transform duration-200 hover:-translate-y-1 cursor-pointer shadow-md"
                            style="height: {{ $player['height'] }}px; border-color: {{ $player['color'] }}; box-shadow: inset 0 0 0 3px var(--vellum-bright), inset 0 0 0 4px {{ $player['color'] }}55;"
                            title="Inspecter le profil de {{ $player['name'] }}"
                        >
                            <span class="font-display text-2xl sm:text-3xl font-bold leading-none" style="color: {{ $player['color'] }};">
                                #{{ $player['rank'] }}
                            </span>
                            <span class="font-display font-bold text-ink text-xs sm:text-sm mt-1.5">{{ $player['elo'] }} Élo</span>
                            <span class="font-display text-[10px] text-ink-muted mt-1 uppercase tracking-wider">Inspecter</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Suite du classement (à partir du 4e) --}}
        @if (! empty($this->playersAfterPodium))
            <div class="codex-panel overflow-hidden">
                <div class="px-5 py-2.5 bg-parchment-shade/55 border-b border-parchment-border flex items-center gap-4 font-display text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                    <span class="w-10">Rang</span>
                    <span class="flex-1">Combattant</span>
                    <span class="hidden sm:inline w-24 text-right">Duels</span>
                    <span class="w-24 text-right">Ligue</span>
                    <span class="w-16 text-right">Élo</span>
                </div>

                @foreach ($this->playersAfterPodium as $player)
                    @php
                        $rowLeagueInk = FolioCardPresenter::leagueInkColor((string) ($player['league']['value'] ?? 'silver'));
                        $isMe = $player['id'] === auth()->id();
                    @endphp
                    <div
                        wire:key="row-{{ $player['id'] }}"
                        class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 border-b border-parchment-border/60 last:border-0 hover:bg-parchment-bright/80 transition {{ $isMe ? 'bg-brass/15' : '' }}"
                    >
                        <span class="w-10 font-display font-bold text-sm text-ink-sepia shrink-0">#{{ $player['rank'] }}</span>

                        <div class="flex-1 flex items-center gap-2 min-w-0">
                            <a
                                href="{{ route('player.profile', $player['name']) }}"
                                class="font-display text-sm font-bold text-ink hover:text-wax transition truncate"
                                title="Voir la fiche de {{ $player['name'] }}"
                            >
                                {{ $player['name'] }}
                            </a>
                            <button
                                type="button"
                                wire:click="inspectPlayer({{ $player['id'] }})"
                                class="w-7 h-7 rounded-md bg-parchment-bright border border-parchment-border text-ink-sepia hover:border-brass-dark hover:text-ink transition inline-flex items-center justify-center shrink-0 cursor-pointer"
                                title="Aperçu rapide"
                                aria-label="Aperçu rapide de {{ $player['name'] }}"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="11" cy="11" r="6" fill="none" stroke="currentColor" stroke-width="2"/>
                                    <path d="M16 16 L20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <span class="hidden sm:inline w-24 text-right font-body text-xs italic text-ink-muted shrink-0">
                            {{ $player['battles'] }} combat(s)
                        </span>

                        <span class="w-24 text-right font-display text-xs font-bold shrink-0" style="color: {{ $rowLeagueInk }};">
                            {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                        </span>

                        <span class="w-16 text-right font-display text-sm font-bold text-brass-dark shrink-0">
                            {{ $player['elo'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <p class="text-center font-body text-xs italic text-ink-muted">Classement mis à jour toutes les minutes.</p>

    {{-- Ligne épinglée du joueur connecté --}}
    @if ($this->currentPlayer)
        <div class="fixed bottom-3 inset-x-0 px-3 sm:px-4 z-40 pointer-events-none">
            <div class="pointer-events-auto max-w-3xl mx-auto flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 rounded-lg border-2 border-brass bg-leather text-parchment-light shadow-2xl">
                <span class="font-display text-[10px] uppercase tracking-widest text-brass-light shrink-0">Toi</span>
                <span class="font-display text-base font-bold text-parchment-bright shrink-0">#{{ $this->currentPlayer['rank'] }}</span>
                <span class="flex-1 font-display text-sm font-bold text-parchment-bright truncate">{{ $this->currentPlayer['name'] }}</span>
                <span class="font-display text-xs font-bold text-brass-light shrink-0">
                    {{ $this->currentPlayer['league']['icon'] }} {{ $this->currentPlayer['league']['label'] }}
                </span>
                <span class="font-display text-sm font-bold text-brass-light shrink-0">{{ $this->currentPlayer['elo'] }} Élo</span>
            </div>
        </div>
    @endif

    {{-- Modale d'Inspection Rival depuis le Classement --}}
    @if ($showInspectorModal && $inspectedPlayer)
        <div
            class="codex-modal-backdrop"
            x-data
            x-on:keydown.escape.window="$wire.closeInspector()"
        >
            <div
                class="codex-modal-panel {{ ! empty($inspectedPlayer['favorites']) ? 'max-w-6xl' : 'max-w-lg' }} max-h-[90vh] flex flex-col"
                @click.outside="$wire.closeInspector()"
            >
                <div class="p-4 sm:p-5 border-b border-parchment-border bg-parchment-shade/45 flex items-center justify-between gap-3">
                    <span class="font-display text-xs font-bold uppercase tracking-widest text-brass-dark">Joueur Classé</span>
                    <button
                        type="button"
                        wire:click="closeInspector"
                        class="w-8 h-8 rounded-full bg-parchment-bright border border-parchment-border text-ink hover:bg-wax hover:text-parchment-bright hover:border-wax transition flex items-center justify-center font-bold text-sm cursor-pointer"
                        aria-label="Fermer l'inspection"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                    <div class="flex flex-wrap items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-xl flex flex-col items-center justify-center border-2 bg-parchment-bright shrink-0"
                            style="border-color: {{ $inspectedPlayer['league']['ink_color'] ?? '#6E4F0E' }};"
                        >
                            <span class="text-2xl leading-none">{{ $inspectedPlayer['league']['icon'] ?? '🛡️' }}</span>
                            <span class="font-display text-[9px] uppercase font-bold mt-1" style="color: {{ $inspectedPlayer['league']['ink_color'] ?? '#6E4F0E' }};">
                                {{ $inspectedPlayer['league']['name'] ?? 'Ligue' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-display text-xl font-bold text-ink truncate">
                                {{ $inspectedPlayer['name'] }}
                            </h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-brass/15 border border-brass-dark/40 font-display text-xs font-bold text-ink-sepia mt-1">
                                <span>Niveau {{ $inspectedPlayer['level'] }}</span>
                                <span>·</span>
                                <span>« {{ $inspectedPlayer['title'] }} »</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 text-center p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                        <div>
                            <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Élo</span>
                            <span class="font-display text-base font-bold text-brass-dark">{{ $inspectedPlayer['elo'] }}</span>
                        </div>
                        <div>
                            <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Duels</span>
                            <span class="font-display text-base font-bold text-ink">{{ $inspectedPlayer['battles'] }}</span>
                        </div>
                        <div>
                            <span class="font-display text-[10px] uppercase font-bold text-ink-muted block">Winrate</span>
                            <span class="font-display text-base font-bold text-forest">{{ $inspectedPlayer['winrate'] }}%</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">
                            Champions de prédilection
                        </h4>
                        @if (! empty($inspectedPlayer['favorites']))
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 justify-items-center">
                                @foreach ($inspectedPlayer['favorites'] as $fav)
                                    <x-folio-card
                                        :card="$fav"
                                        :showOwnerActions="false"
                                        :folioKey="'lb-fav-'.($fav['id'] ?? $loop->index)"
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
                            wire:click="closeInspector"
                            class="btn-cartouche btn-cartouche--sm"
                        >
                            Fermer
                        </button>
                        <a href="{{ $inspectedPlayer['profile_url'] }}" class="btn-brass btn-brass--sm">
                            <span>Voir la fiche de destinée</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
