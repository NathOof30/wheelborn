<div class="space-y-6">
    {{-- Notification Codex --}}
    @if ($notification)
        <div
            role="status"
            class="codex-alert {{ $notificationType === 'success' ? 'codex-alert--success' : ($notificationType === 'error' ? 'codex-alert--error' : ($notificationType === 'warning' ? 'codex-alert--warning' : 'codex-alert--info')) }}"
        >
            <span>{{ $notification }}</span>
            <button type="button" wire:click="dismissNotification" class="font-display font-bold px-2 py-0.5 rounded hover:bg-black/5 cursor-pointer" aria-label="Fermer">&times;</button>
        </div>
    @endif

    {{-- Barre KPI & Raretés Codex --}}
    <div class="codex-panel p-4 sm:p-5">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="codex-well px-3.5 py-2 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.5 L19.5 5.5 V11.5 C19.5 16.5 16.2 19.8 12 21.5 C7.8 19.8 4.5 16.5 4.5 11.5 V5.5 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <span class="block font-display text-[11px] uppercase font-bold tracking-wider text-ink-muted">Effectif</span>
                        <span class="font-display text-sm font-bold text-ink tabular-nums">
                            {{ $totalCount }} {{ Str::plural('Héros', $totalCount) }}
                        </span>
                    </div>
                </div>

                @if ($lockedCount > 0)
                    <div class="codex-well px-3.5 py-2 flex items-center gap-2.5" title="Héros protégés contre le recyclage">
                        <svg class="w-4 h-4 text-ink-sepia shrink-0" viewBox="0 0 20 20" aria-hidden="true">
                            <rect x="4.5" y="9" width="11" height="8.5" rx="1.8" fill="none" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M7 9 V6.4 C7 4.4 8.3 3 10 3 C11.7 3 13 4.4 13 6.4 V9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <div>
                            <span class="block font-display text-[11px] uppercase font-bold tracking-wider text-ink-muted">Verrouillés</span>
                            <span class="font-display text-sm font-bold text-ink tabular-nums">{{ $lockedCount }}</span>
                        </div>
                    </div>
                @endif

                @if ($maxPowerScore > 0)
                    <div class="codex-well px-3.5 py-2 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-brass-dark shrink-0" viewBox="0 0 20 20" aria-hidden="true">
                            <polygon points="10,1.5 12.6,7.4 18.5,10 12.6,12.6 10,18.5 7.4,12.6 1.5,10 7.4,7.4" fill="currentColor"/>
                        </svg>
                        <div>
                            <span class="block font-display text-[11px] uppercase font-bold tracking-wider text-ink-muted">Record Puissance</span>
                            <span class="font-display text-sm font-bold text-brass-dark tabular-nums">{{ $maxPowerScore }} pts</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pilules Rareté + Bouton Recyclage en masse --}}
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="setRarity('legendary')"
                    class="filter-chip tabular-nums {{ $rarity === 'legendary' ? 'is-active' : '' }}"
                    style="--chip-hex: #74520A;"
                    title="Filtrer les Légendaires"
                >
                    <span class="w-2.5 h-2.5 rounded-full bg-[#74520A] {{ $rarity === 'legendary' ? 'bg-[#FBF1DC]' : '' }}"></span>
                    <span>{{ $rarityCounts['legendary'] ?? 0 }} Légendaires</span>
                </button>

                <button
                    type="button"
                    wire:click="setRarity('epic')"
                    class="filter-chip tabular-nums {{ $rarity === 'epic' ? 'is-active' : '' }}"
                    style="--chip-hex: #54274A;"
                    title="Filtrer les Épiques"
                >
                    <span class="w-2.5 h-2.5 rounded-full bg-[#54274A] {{ $rarity === 'epic' ? 'bg-[#FBF1DC]' : '' }}"></span>
                    <span>{{ $rarityCounts['epic'] ?? 0 }} Épiques</span>
                </button>

                <button
                    type="button"
                    wire:click="setRarity('rare')"
                    class="filter-chip tabular-nums {{ $rarity === 'rare' ? 'is-active' : '' }}"
                    style="--chip-hex: #2A3A7A;"
                    title="Filtrer les Rares"
                >
                    <span class="w-2.5 h-2.5 rounded-full bg-[#2A3A7A] {{ $rarity === 'rare' ? 'bg-[#FBF1DC]' : '' }}"></span>
                    <span>{{ $rarityCounts['rare'] ?? 0 }} Rares</span>
                </button>

                <button
                    type="button"
                    wire:click="setRarity('common')"
                    class="filter-chip tabular-nums {{ $rarity === 'common' ? 'is-active' : '' }}"
                    style="--chip-hex: #6B4A2A;"
                    title="Filtrer les Communs"
                >
                    <span class="w-2.5 h-2.5 rounded-full bg-[#6B4A2A] {{ $rarity === 'common' ? 'bg-[#FBF1DC]' : '' }}"></span>
                    <span>{{ $rarityCounts['common'] ?? 0 }} Communs</span>
                </button>

                @if ($totalCount > 0)
                    <button
                        type="button"
                        wire:click="openBatchModal"
                        class="btn-brass btn-brass--sm ml-auto lg:ml-2"
                        title="Ouvrir le menu de recyclage en masse avancé"
                    >
                        <span>Recyclage en masse</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Barre de Filtres et de Tri complète --}}
    <div class="codex-panel p-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-ink-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Rechercher par nom de héros..."
                    class="codex-input w-full pl-9 pr-8"
                />
                @if (! empty($search))
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-ink-muted hover:text-ink cursor-pointer"
                        title="Effacer la recherche"
                        aria-label="Effacer la recherche"
                    >
                        &times;
                    </button>
                @endif
            </div>

            <div class="md:col-span-2">
                <select wire:model.live="race" class="codex-select w-full" aria-label="Filtrer par race">
                    <option value="">Toutes les races</option>
                    @foreach ($races as $r)
                        <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <select wire:model.live="weapon" class="codex-select w-full" aria-label="Filtrer par arme">
                    <option value="">Toutes les armes</option>
                    @foreach ($weapons as $w)
                        <option value="{{ $w['id'] }}">{{ $w['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <select wire:model.live="sortBy" class="codex-select w-full" aria-label="Trier la collection">
                    <option value="power_desc">Puissance (Max → Min)</option>
                    <option value="power_asc">Puissance (Min → Max)</option>
                    <option value="rarity">Rareté (Légendaire → Commun)</option>
                    <option value="date_desc">Plus récents d'abord</option>
                    <option value="date_asc">Plus anciens d'abord</option>
                    <option value="wins_desc">Victoires en Arène</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-parchment-border/70">
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    type="button"
                    wire:click="toggleOnlySynergies"
                    class="filter-chip {{ $onlySynergies ? 'is-active' : '' }}"
                    style="--chip-hex: #74520A;"
                >
                    <span>✦ Avec synergies actives</span>
                </button>

                <button
                    type="button"
                    wire:click="toggleOnlyLocked"
                    class="filter-chip {{ $onlyLocked ? 'is-active' : '' }}"
                    style="--chip-hex: #52381F;"
                >
                    <span>Verrouillés</span>
                </button>

                <button
                    type="button"
                    wire:click="toggleOnlyFavorites"
                    class="filter-chip tabular-nums {{ $onlyFavorites ? 'is-active' : '' }}"
                    style="--chip-hex: #74520A;"
                    title="Afficher les combattants de votre vitrine de profil"
                >
                    <span>★ Favoris ({{ $favoriteCount }}/3)</span>
                </button>

                @if (! empty($rarity))
                    <button
                        type="button"
                        wire:click="$set('rarity', '')"
                        class="filter-chip is-active"
                        style="--chip-hex: #8A1A14;"
                        title="Annuler le filtre de rareté"
                    >
                        <span>Rareté : {{ \App\Enums\Rarity::tryFrom($rarity)?->label() ?? ucfirst($rarity) }} &times;</span>
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="font-display text-xs font-bold text-ink-sepia tabular-nums">
                    @if ($this->hasActiveFilters())
                        <strong class="text-wax">{{ $filteredCount }}</strong> / {{ $totalCount }} {{ Str::plural('héros', $totalCount) }}
                    @else
                        <strong class="text-ink">{{ $totalCount }}</strong> {{ Str::plural('héros', $totalCount) }}
                    @endif
                </span>

                @if ($this->hasActiveFilters())
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="btn-cartouche btn-cartouche--sm"
                        title="Réinitialiser tous les filtres"
                    >
                        ↺ Effacer les filtres
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Grille Universelle ou États Vides --}}
    @if ($totalCount === 0)
        <div class="codex-panel p-10 text-center max-w-xl mx-auto flex flex-col items-center gap-4">
            <svg class="w-24 h-28 text-brass-dark opacity-70" viewBox="0 0 120 150" aria-hidden="true">
                <rect x="14" y="10" width="92" height="130" rx="6" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="5 4"/>
                <circle cx="60" cy="68" r="24" fill="none" stroke="currentColor" stroke-width="1.6"/>
                <polygon points="60,48 65,64 81,68 65,72 60,88 55,72 39,68 55,64" fill="none" stroke="currentColor" stroke-width="1.4"/>
            </svg>
            <h2 class="font-display font-bold text-xl text-ink">Votre caserne est déserte !</h2>
            <p class="text-base text-ink-sepia max-w-md">
                Aucun combattant n'a encore été scellé dans votre grimoire. Tournez la Roue du Destin pour invoquer votre premier héros.
            </p>
            <a href="{{ route('dashboard') }}" class="btn-brass mt-1">
                Faire tourner la Roue du Destin
            </a>
        </div>
    @elseif ($filteredCount === 0)
        <div class="codex-panel p-10 text-center max-w-lg mx-auto flex flex-col items-center gap-3">
            <h3 class="font-display font-bold text-lg text-ink">Aucun héros trouvé</h3>
            <p class="text-sm text-ink-sepia">
                Aucun de vos combattants ne correspond aux critères sélectionnés.
            </p>
            <button type="button" wire:click="resetFilters" class="btn-cartouche btn-cartouche--sm mt-2">
                ↺ Réinitialiser les filtres
            </button>
        </div>
    @else
        <div class="folios-grid">
            @foreach ($characters as $character)
                <x-folio-card
                    :card="$character"
                    :showOwnerActions="true"
                    wire:key="collection-card-{{ $character->id }}"
                    folioKey="collection-{{ $character->id }}"
                />
            @endforeach
        </div>

        <div class="pt-4">
            {{ $characters->links('vendor.pagination.codex') }}
        </div>
    @endif

    {{-- Modale Recyclage en Masse Avancé (DA Codex) --}}
    @if ($showBatchModal)
        <div
            class="codex-modal-backdrop overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="batch-modal-title"
            x-data
            x-on:keydown.escape.window="$wire.closeBatchModal()"
        >
            <div
                class="codex-modal-panel max-w-2xl my-6"
                @click.outside="$wire.closeBatchModal()"
            >
                <div class="flex items-center justify-between p-5 border-b border-parchment-border bg-parchment-shade/40">
                    <div>
                        <h3 id="batch-modal-title" class="font-display font-bold text-lg text-ink">
                            Recyclage en Masse Avancé
                        </h3>
                        <p class="text-sm text-ink-sepia">
                            Filtrez précisément les combattants non protégés à convertir en pièces d'or.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeBatchModal"
                        class="btn-cartouche btn-cartouche--sm"
                        aria-label="Fermer"
                    >
                        &times;
                    </button>
                </div>

                <div class="p-5 space-y-5">
                    {{-- 1. Raretés ciblées --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">
                                1. Raretés à recycler :
                            </span>
                            <span class="text-xs text-ink-muted">Cliquer pour cocher / décocher</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach ([
                                ['key' => 'common', 'label' => 'Commun', 'hex' => '#6B4A2A'],
                                ['key' => 'rare', 'label' => 'Rare', 'hex' => '#2A3A7A'],
                                ['key' => 'epic', 'label' => 'Épique', 'hex' => '#54274A'],
                                ['key' => 'legendary', 'label' => 'Légendaire', 'hex' => '#74520A'],
                            ] as $rOpt)
                                @php $checked = in_array($rOpt['key'], $batchRarities, true); @endphp
                                <button
                                    type="button"
                                    wire:click="toggleBatchRarity('{{ $rOpt['key'] }}')"
                                    class="p-2.5 rounded-md border text-center transition cursor-pointer {{ $checked ? 'text-[#FBF1DC] shadow-sm' : 'bg-parchment-bright text-ink border-parchment-border hover:border-brass-dark' }}"
                                    @if ($checked) style="background-color: {{ $rOpt['hex'] }}; border-color: {{ $rOpt['hex'] }};" @endif
                                >
                                    <span class="block font-display font-bold text-sm">{{ $rOpt['label'] }}</span>
                                    <span class="text-xs opacity-90">{{ $checked ? '✓ Sélectionné' : 'Ignorer' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. Score Max, Race, Arme --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 codex-well p-3.5">
                        <div>
                            <label for="batch-max-power" class="flex items-center justify-between font-display text-xs font-bold text-ink-sepia mb-1.5">
                                <span>Power Score Max</span>
                                <span class="text-brass-dark tabular-nums">{{ ! empty($batchMaxPower) ? '≤ '.$batchMaxPower : 'Tous' }}</span>
                            </label>
                            <input
                                id="batch-max-power"
                                type="number"
                                wire:model.live.debounce.150ms="batchMaxPower"
                                placeholder="ex: 60"
                                min="0"
                                max="200"
                                class="codex-input w-full text-sm tabular-nums"
                            />
                            <div class="flex gap-1 mt-1.5 flex-wrap">
                                <button type="button" wire:click="$set('batchMaxPower', '50')" class="px-1.5 py-0.5 rounded border border-parchment-border bg-parchment-bright hover:border-brass-dark text-xs font-display font-bold text-ink cursor-pointer">≤ 50</button>
                                <button type="button" wire:click="$set('batchMaxPower', '65')" class="px-1.5 py-0.5 rounded border border-parchment-border bg-parchment-bright hover:border-brass-dark text-xs font-display font-bold text-ink cursor-pointer">≤ 65</button>
                                <button type="button" wire:click="$set('batchMaxPower', '80')" class="px-1.5 py-0.5 rounded border border-parchment-border bg-parchment-bright hover:border-brass-dark text-xs font-display font-bold text-ink cursor-pointer">≤ 80</button>
                                <button type="button" wire:click="$set('batchMaxPower', '')" class="px-1.5 py-0.5 rounded border border-wax/40 bg-wax/10 hover:bg-wax hover:text-[#FBF1DC] text-xs font-display font-bold text-wax cursor-pointer">Tous</button>
                            </div>
                        </div>

                        <div>
                            <label for="batch-race" class="block font-display text-xs font-bold text-ink-sepia mb-1.5">
                                Race ciblée
                            </label>
                            <select id="batch-race" wire:model.live="batchRace" class="codex-select w-full text-sm">
                                <option value="">Toutes les races</option>
                                @foreach ($races as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="batch-weapon" class="block font-display text-xs font-bold text-ink-sepia mb-1.5">
                                Arme ciblée
                            </label>
                            <select id="batch-weapon" wire:model.live="batchWeapon" class="codex-select w-full text-sm">
                                <option value="">Toutes les armes</option>
                                @foreach ($weapons as $w)
                                    <option value="{{ $w['id'] }}">{{ $w['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 3. Exclusion synergies --}}
                    <label class="flex items-center justify-between gap-3 codex-well p-3 cursor-pointer">
                        <div>
                            <span class="font-display text-xs font-bold text-ink block">Exclure les combattants avec synergie</span>
                            <span class="text-sm text-ink-sepia">Préserve les cartes possédant des synergies actives.</span>
                        </div>
                        <input
                            type="checkbox"
                            wire:model.live="batchExcludeSynergies"
                            class="w-5 h-5 rounded border-brass-dark text-brass-dark focus:ring-brass"
                        />
                    </label>

                    {{-- 4. Aperçu temps réel --}}
                    <div class="codex-well p-4 space-y-3">
                        <div class="flex flex-wrap justify-between items-center gap-2 pb-2 border-b border-parchment-border">
                            <span class="text-sm text-ink-sepia">
                                Héros ciblés : <strong class="font-display font-bold text-ink tabular-nums">{{ $batchPreview['count'] }}</strong>
                            </span>
                            <span class="text-sm text-ink-sepia">
                                Gain d'or total : <strong class="font-display font-bold text-brass-dark tabular-nums">+{{ number_format($batchPreview['coins'], 0, ',', ' ') }} 🪙</strong>
                            </span>
                        </div>

                        @if ($batchPreview['count'] > 0)
                            <div class="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                                @foreach ($batchPreview['characters'] as $c)
                                    <div class="flex items-center justify-between px-3 py-1.5 rounded bg-parchment-bright border border-parchment-border/80 text-sm">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-display font-bold text-ink truncate max-w-[180px]">{{ $c->name }}</span>
                                            <span class="text-xs font-display font-bold px-1.5 py-0.5 rounded bg-parchment-shade text-ink-sepia">{{ $c->rarity->label() }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0 font-display text-xs font-bold tabular-nums">
                                            <span class="text-ink-sepia">{{ $c->power_score }} pts</span>
                                            <span class="text-brass-dark">+{{ $c->recycle_value }} 🪙</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="py-3 text-center text-sm italic text-ink-muted">
                                Aucun combattant ne correspond aux critères sélectionnés.
                            </p>
                        @endif
                    </div>

                    <div class="codex-alert codex-alert--warning text-sm">
                        <span>
                            <strong>Protection automatique :</strong> vos <strong>{{ $lockedCount }} héros verrouillés</strong>, vos favoris et vos combattants actifs en arène sont exclus.
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-5 border-t border-parchment-border bg-parchment-shade/30">
                    <button type="button" wire:click="resetBatchFilters" class="text-sm font-display font-bold text-ink-sepia hover:text-wax underline cursor-pointer">
                        ↺ Réinitialiser les critères
                    </button>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <button type="button" wire:click="closeBatchModal" class="btn-cartouche btn-cartouche--sm flex-1 sm:flex-initial">
                            Annuler
                        </button>
                        <button
                            type="button"
                            wire:click="confirmBatchRecycle"
                            wire:loading.attr="disabled"
                            @disabled($batchPreview['count'] === 0)
                            class="btn-brass btn-brass--sm flex-1 sm:flex-initial tabular-nums"
                        >
                            <span wire:loading.remove wire:target="confirmBatchRecycle">
                                Recycler {{ $batchPreview['count'] }} {{ Str::plural('héros', $batchPreview['count']) }} (+{{ number_format($batchPreview['coins'], 0, ',', ' ') }} 🪙)
                            </span>
                            <span wire:loading wire:target="confirmBatchRecycle">Recyclage...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
