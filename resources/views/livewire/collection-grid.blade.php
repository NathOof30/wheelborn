<div class="space-y-6">
    {{-- Notification Toast / Banner --}}
    @if ($notification)
        <div class="p-4 rounded-xl flex items-center justify-between border shadow-lg transition-all duration-300 {{ $notificationType === 'success' ? 'bg-emerald-950/80 border-emerald-500/50 text-emerald-200' : ($notificationType === 'error' ? 'bg-rose-950/80 border-rose-500/50 text-rose-200' : 'bg-slate-900 border-indigo-500/40 text-indigo-200') }}">
            <div class="flex items-center gap-3">
                <span class="text-xl">
                    @if ($notificationType === 'success') ✨
                    @elseif ($notificationType === 'error') ⚠️
                    @else ℹ️
                    @endif
                </span>
                <span class="text-sm font-medium">{{ $notification }}</span>
            </div>
            <button wire:click="dismissNotification" class="text-slate-400 hover:text-white text-lg px-2">
                ✕
            </button>
        </div>
    @endif

    {{-- Collector HUD Summary Bar --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl backdrop-blur-md">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            {{-- Compteur Global, Lock & Meilleur Power --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2.5 bg-slate-950/80 px-4 py-2 rounded-xl border border-slate-800">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Effectif</span>
                        <span class="text-base font-extrabold text-white font-mono">
                            {{ $totalCount }} {{ Str::plural('Héros', $totalCount) }}
                        </span>
                    </div>
                </div>

                @if ($lockedCount > 0)
                    <div class="flex items-center gap-2 bg-slate-950/80 px-3.5 py-2 rounded-xl border border-amber-500/30 text-amber-300" title="Héros protégés contre le recyclage">
                        <span class="text-lg">🔒</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Verrouillés</span>
                            <span class="text-base font-extrabold font-mono text-amber-400">
                                {{ $lockedCount }}
                            </span>
                        </div>
                    </div>
                @endif

                @if ($maxPowerScore > 0)
                    <div class="flex items-center gap-2.5 bg-slate-950/80 px-4 py-2 rounded-xl border border-slate-800">
                        <span class="text-xl">⚡</span>
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Record Puissance</span>
                            <span class="text-base font-extrabold text-amber-400 font-mono">
                                {{ $maxPowerScore }} pts
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Répartition par Rareté & Bouton Recyclage en Masse --}}
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <button
                    wire:click="setRarity('legendary')"
                    class="px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-150 {{ $rarity === 'legendary' ? 'bg-amber-400/25 border-amber-400 text-amber-300 ring-2 ring-amber-400/30 shadow-[0_0_12px_rgba(251,191,36,0.3)]' : 'bg-amber-400/10 text-amber-300 border-amber-400/30 hover:bg-amber-400/20' }}"
                    title="Filtrer les Légendaires"
                >
                    <span>👑</span>
                    <span>{{ $rarityCounts['legendary'] ?? 0 }} Légendaires</span>
                </button>
                <button
                    wire:click="setRarity('epic')"
                    class="px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-150 {{ $rarity === 'epic' ? 'bg-purple-500/25 border-purple-400 text-purple-300 ring-2 ring-purple-400/30 shadow-[0_0_12px_rgba(192,132,252,0.3)]' : 'bg-purple-500/10 text-purple-300 border-purple-500/30 hover:bg-purple-500/20' }}"
                    title="Filtrer les Épiques"
                >
                    <span>🔮</span>
                    <span>{{ $rarityCounts['epic'] ?? 0 }} Épiques</span>
                </button>
                <button
                    wire:click="setRarity('rare')"
                    class="px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-150 {{ $rarity === 'rare' ? 'bg-cyan-500/25 border-cyan-400 text-cyan-300 ring-2 ring-cyan-400/30 shadow-[0_0_12px_rgba(56,189,248,0.3)]' : 'bg-cyan-500/10 text-cyan-300 border-cyan-500/30 hover:bg-cyan-500/20' }}"
                    title="Filtrer les Rares"
                >
                    <span>💎</span>
                    <span>{{ $rarityCounts['rare'] ?? 0 }} Rares</span>
                </button>
                <button
                    wire:click="setRarity('common')"
                    class="px-3 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-150 {{ $rarity === 'common' ? 'bg-slate-700 border-slate-500 text-white ring-2 ring-slate-400/30' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}"
                    title="Filtrer les Communs"
                >
                    <span>⚔️</span>
                    <span>{{ $rarityCounts['common'] ?? 0 }} Communs</span>
                </button>

                {{-- BOUTON DÉDIÉ RECYCLAGE EN MASSE AVANCÉ (T1.4) --}}
                @if ($totalCount > 0)
                    <button
                        wire:click="openBatchModal"
                        class="ml-auto lg:ml-2 px-3.5 py-1.5 rounded-lg bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-extrabold border border-amber-400 shadow-md shadow-amber-500/20 flex items-center gap-1.5 transition-all duration-150 transform hover:scale-105 cursor-pointer"
                        title="Ouvrir le menu de recyclage en masse avancé"
                    >
                        <span>♻️</span>
                        <span>Recyclage en masse</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- BARRE DE FILTRES ET DE TRI DYNAMIQUE (T1.2 & T1.3) --}}
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg backdrop-blur-md space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            {{-- Recherche textuelle en direct par nom --}}
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Rechercher par nom de héros..."
                    class="w-full pl-10 pr-9 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400/80 focus:ring-1 focus:ring-amber-400/50 transition duration-150"
                />
                @if (!empty($search))
                    <button
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
                        title="Effacer la recherche"
                    >
                        ✕
                    </button>
                @endif
            </div>

            {{-- Filtre Race --}}
            <div class="md:col-span-2">
                <select
                    wire:model.live="race"
                    class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-400/80 focus:ring-1 focus:ring-indigo-400/50 transition duration-150 cursor-pointer"
                >
                    <option value="">👤 Toutes les races</option>
                    @foreach ($races as $r)
                        <option value="{{ $r['id'] }}">{{ $r['icon'] }} {{ $r['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtre Arme --}}
            <div class="md:col-span-2">
                <select
                    wire:model.live="weapon"
                    class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-cyan-400/80 focus:ring-1 focus:ring-cyan-400/50 transition duration-150 cursor-pointer"
                >
                    <option value="">⚔️ Toutes les armes</option>
                    @foreach ($weapons as $w)
                        <option value="{{ $w['id'] }}">{{ $w['icon'] }} {{ $w['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tri Dynamique --}}
            <div class="md:col-span-4">
                <select
                    wire:model.live="sortBy"
                    class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-amber-300 font-semibold focus:outline-none focus:border-amber-400/80 focus:ring-1 focus:ring-amber-400/50 transition duration-150 cursor-pointer"
                >
                    <option value="power_desc">⚡ Puissance (Max → Min)</option>
                    <option value="power_asc">⚡ Puissance (Min → Max)</option>
                    <option value="rarity">👑 Rareté (Légendaire → Commun)</option>
                    <option value="date_desc">🕒 Plus récents d'abord</option>
                    <option value="date_asc">⏳ Plus anciens d'abord</option>
                    <option value="wins_desc">🏆 Victoires en Arène</option>
                </select>
            </div>
        </div>

        {{-- Ligne 2 : Filtres rapides complémentaires & Résumé --}}
        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-800/60 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Toggle Synergies actives --}}
                <button
                    wire:click="toggleOnlySynergies"
                    class="px-3 py-1.5 rounded-lg border font-semibold flex items-center gap-1.5 transition-all duration-150 {{ $onlySynergies ? 'bg-indigo-600/30 border-indigo-500 text-indigo-300 ring-1 ring-indigo-400/40 shadow-[0_0_10px_rgba(99,102,241,0.3)]' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}"
                >
                    <span>✨</span>
                    <span>Avec synergies actives</span>
                    @if ($onlySynergies)
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    @endif
                </button>

                {{-- Toggle Protégés / Verrouillés (T1.3) --}}
                <button
                    wire:click="toggleOnlyLocked"
                    class="px-3 py-1.5 rounded-lg border font-semibold flex items-center gap-1.5 transition-all duration-150 {{ $onlyLocked ? 'bg-amber-500/25 border-amber-500 text-amber-300 ring-1 ring-amber-400/40 shadow-[0_0_10px_rgba(251,191,36,0.3)]' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700' }}"
                >
                    <span>🔒</span>
                    <span>Verrouillés uniquement</span>
                    @if ($onlyLocked)
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    @endif
                </button>

                {{-- Filtre Rareté actif --}}
                @if (!empty($rarity))
                    <button
                        wire:click="$set('rarity', '')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 flex items-center gap-1"
                        title="Annuler le filtre de rareté"
                    >
                        <span>Filtre rareté : <strong>{{ ucfirst($rarity) }}</strong></span>
                        <span class="text-rose-400 font-bold ml-1">✕</span>
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="text-slate-400 font-mono">
                    @if ($this->hasActiveFilters())
                        <strong class="text-amber-400 font-bold">{{ $filteredCount }}</strong> / {{ $totalCount }} {{ Str::plural('héros', $totalCount) }}
                    @else
                        <strong class="text-slate-300 font-bold">{{ $totalCount }}</strong> {{ Str::plural('héros', $totalCount) }}
                    @endif
                </span>

                @if ($this->hasActiveFilters())
                    <button
                        wire:click="resetFilters"
                        class="px-2.5 py-1 rounded-md text-rose-300 bg-rose-950/50 hover:bg-rose-900/60 border border-rose-800/60 transition duration-150 flex items-center gap-1 font-semibold"
                        title="Réinitialiser tous les filtres"
                    >
                        <span>↺</span>
                        <span>Effacer les filtres</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Grille des Héros ou États Vides --}}
    @if ($totalCount === 0)
        {{-- État vide 1 : Caserne vide (aucun héros tiré) --}}
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-12 text-center max-w-xl mx-auto shadow-2xl">
            <div class="text-6xl mb-4 animate-bounce">⚔️</div>
            <h2 class="text-2xl font-black text-white">Votre caserne est déserte !</h2>
            <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                Vous ne possédez encore aucun combattant. Rendez-vous sur la Roue du Destin pour invoquer votre tout premier héros hybride !
            </p>
            <div class="mt-6">
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-amber-500/25 transition-all duration-200 transform hover:-translate-y-0.5"
                >
                    <span>🎡</span>
                    <span>Faire tourner la Roue du Destin</span>
                </a>
            </div>
        </div>
    @elseif ($filteredCount === 0)
        {{-- État vide 2 : Filtres sans aucun résultat --}}
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-10 text-center max-w-lg mx-auto shadow-xl">
            <div class="text-5xl mb-3 text-slate-600">🔍</div>
            <h3 class="text-xl font-bold text-white">Aucun héros trouvé</h3>
            <p class="text-slate-400 text-sm mt-1.5">
                Aucun de vos combattants ne correspond aux critères de recherche sélectionnés.
            </p>
            <div class="mt-5">
                <button
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/40 text-xs font-bold transition duration-150"
                >
                    <span>↺</span>
                    <span>Réinitialiser les filtres</span>
                </button>
            </div>
        </div>
    @else
        {{-- GRILLE COLLECTOR : CARTES RECTO / VERSO (T1.5) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($characters as $character)
                @php
                    $rarityKey = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : $character->rarity;
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

                {{-- Composant Carte avec Flip / Double-face --}}
                <div
                    x-data="{ flipped: false }"
                    @mouseenter="flipped = true"
                    @mouseleave="flipped = false"
                    class="rounded-3xl border {{ $borderStyles }} relative transition-all duration-300 overflow-hidden group select-none shadow-xl hover:-translate-y-1 w-full"
                    style="height: 490px; min-height: 490px;"
                >
                    {{-- ========================================================================= --}}
                    {{-- FACE RECTO (FACE ARTISTIQUE COLLECTOR PAR DÉFAUT - PLACEHOLDER ARTWORK)   --}}
                    {{-- ========================================================================= --}}
                    <div
                        class="absolute inset-0 p-4 flex flex-col justify-between transition-all duration-300 z-10 cursor-pointer"
                        :class="{ 'opacity-0 scale-95 pointer-events-none': flipped, 'opacity-100 scale-100': !flipped }"
                        style="height: 490px; min-height: 490px;"
                        wire:click="inspectCharacter('{{ $character->id }}')"
                    >
                        {{-- En-tête Recto --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider {{ $badgeStyle }}">
                                {{ $character->rarity->label() }}
                            </span>

                            <div class="flex items-center gap-2">
                                @if ($character->is_locked)
                                    <span class="text-amber-400 text-xs bg-slate-950/80 px-2 py-0.5 rounded-full border border-amber-500/40 shadow-sm" title="Protégé contre le recyclage">
                                        🔒 Verrouillé
                                    </span>
                                @endif
                                <div class="flex items-center gap-1 bg-slate-950/80 px-2.5 py-0.5 rounded-lg border border-slate-800">
                                    <span class="text-xs text-amber-400">⚡</span>
                                    <span class="font-extrabold text-sm text-white font-mono">{{ $character->power_score }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Scène Visuelle : Placeholder Illustration Officielle (Recto) --}}
                        <div class="flex-1 flex flex-col items-center justify-between relative my-2.5 rounded-2xl bg-gradient-to-b from-slate-900/90 via-slate-950 to-slate-950 border-2 border-dashed border-slate-700/80 p-3 overflow-hidden group-hover:border-amber-400/60 transition-all shadow-inner">
                            {{-- Halo lumineux d'ambiance selon rareté --}}
                            <div class="absolute w-36 h-36 rounded-full blur-3xl opacity-20 pointer-events-none {{ $rarityKey === 'legendary' ? 'bg-amber-500' : ($rarityKey === 'epic' ? 'bg-purple-500' : ($rarityKey === 'rare' ? 'bg-cyan-500' : 'bg-slate-500')) }}"></div>

                            {{-- Bandeau supérieur du Placeholder --}}
                            <div class="w-full flex items-center justify-between text-[10px] uppercase font-mono tracking-wider text-slate-400 z-10">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-800/90 border border-slate-700 text-slate-300">
                                    <span>🖼️</span>
                                    <span>Placeholder Artwork</span>
                                </span>
                                <span class="text-slate-500 font-semibold text-[10px]">
                                    {{ $character->race_data['name'] ?? ucfirst($character->race_id) }}
                                </span>
                            </div>

                            {{-- Cadre d'avatar / Piédestal --}}
                            <div class="relative w-28 h-28 rounded-2xl bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/80 shadow-2xl flex items-center justify-center p-3 group-hover:scale-105 transition-transform duration-300 my-auto">
                                {{-- Avatar Race Géant --}}
                                <span class="text-6xl filter drop-shadow-[0_8px_16px_rgba(0,0,0,0.8)]">
                                    {{ $character->race_data['icon'] ?? '👤' }}
                                </span>

                                {{-- Superposition Arme (en bas à droite) --}}
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-sm shadow-lg" title="{{ $character->weapon_data['name'] ?? 'Arme' }}">
                                    {{ $character->weapon_data['icon'] ?? '⚔️' }}
                                </div>

                                {{-- Superposition Pouvoir Élémentaire (en haut à gauche) --}}
                                <div class="absolute -top-2 -left-2 w-8 h-8 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-sm shadow-lg animate-pulse" title="{{ $character->power_data['name'] ?? 'Pouvoir' }}">
                                    {{ $character->power_data['icon'] ?? '🔮' }}
                                </div>
                            </div>

                            {{-- Étoiles de Rareté & Mention --}}
                            <div class="w-full flex flex-col items-center gap-1 z-10">
                                <div class="flex items-center gap-1">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="text-xs {{ $s <= $stars ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]' : 'text-slate-800' }}">
                                            ★
                                        </span>
                                    @endfor
                                </div>
                                <span class="text-[9px] text-slate-500 font-medium tracking-tight">
                                    Emplacement visuel • {{ $character->rarity->label() }}
                                </span>
                            </div>
                        </div>

                        {{-- Pied de carte Recto : Titre & Décoration --}}
                        <div class="text-center pt-2 border-t border-slate-800/80">
                            <h3 class="text-sm font-black text-white truncate tracking-tight group-hover:text-amber-300 transition-colors">
                                {{ $character->name }}
                            </h3>
                            <p class="text-[11px] text-slate-400 truncate mt-0.5 font-medium">
                                {{ $character->race_data['name'] ?? ucfirst($character->race_id) }} • {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}
                                <span class="text-indigo-400 font-semibold">({{ $character->power_data['name'] ?? ucfirst($character->power_id) }})</span>
                            </p>
                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- FACE VERSO (RÉVÉLÉE AU SURVOL / HOVER - STATISTIQUES & ACTIONS)            --}}
                    {{-- ========================================================================= --}}
                    <div
                        class="absolute inset-0 p-4 flex flex-col justify-between transition-all duration-300 bg-slate-950/95 backdrop-blur-md z-20 rounded-3xl"
                        :class="{ 'opacity-100 scale-100 pointer-events-auto': flipped, 'opacity-0 scale-95 pointer-events-none': !flipped }"
                        style="height: 490px; min-height: 490px;"
                    >
                        <div>
                            {{-- En-tête Verso : Nom, Rareté & Cadenas --}}
                            <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-800">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="px-2 py-0.5 rounded text-[9px] uppercase tracking-wider {{ $badgeStyle }} shrink-0">
                                        {{ $character->rarity->label() }}
                                    </span>
                                    <h4 class="text-xs font-black text-white truncate" title="{{ $character->name }}">
                                        {{ $character->name }}
                                    </h4>
                                </div>
                                {{-- Bouton Cadenas (T1.3) --}}
                                <button
                                    type="button"
                                    wire:click.stop="toggleLock('{{ $character->id }}')"
                                    class="p-1 rounded-lg text-xs transition-all duration-150 shrink-0 {{ $character->is_locked ? 'bg-amber-500/20 text-amber-400 border border-amber-500/50 shadow-[0_0_8px_rgba(251,191,36,0.4)]' : 'bg-slate-900 text-slate-500 border border-slate-800 hover:text-slate-300' }}"
                                    title="{{ $character->is_locked ? 'Verrouillé (cliquer pour déverrouiller)' : 'Déverrouillé (cliquer pour verrouiller)' }}"
                                >
                                    {{ $character->is_locked ? '🔒' : '🔓' }}
                                </button>
                            </div>

                            {{-- Tri-Slot Compact --}}
                            <div class="grid grid-cols-3 gap-1 py-1 px-2 rounded-xl bg-slate-900 border border-slate-800/80 mb-2.5 text-center text-[11px]">
                                <div>
                                    <span class="text-sm block">{{ $character->race_data['icon'] ?? '👤' }}</span>
                                    <span class="text-[9px] text-slate-400 truncate block">{{ $character->race_data['name'] ?? ucfirst($character->race_id) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm block">{{ $character->weapon_data['icon'] ?? '⚔️' }}</span>
                                    <span class="text-[9px] text-slate-400 truncate block">{{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm block">{{ $character->power_data['icon'] ?? '🔮' }}</span>
                                    <span class="text-[9px] text-indigo-300 truncate block">{{ $character->power_data['name'] ?? ucfirst($character->power_id) }}</span>
                                </div>
                            </div>

                            {{-- Barres de stats techniques --}}
                            <div class="space-y-1 mb-2.5 bg-slate-900/60 p-2 rounded-xl border border-slate-800/50 text-[10px]">
                                <div>
                                    <div class="flex justify-between mb-0.5">
                                        <span class="text-rose-400 font-semibold">❤️ PV</span>
                                        <span class="font-mono text-white">{{ $character->hp }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($character->hp / 140) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-0.5">
                                        <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                        <span class="font-mono text-white">{{ $character->strength }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($character->strength / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-0.5">
                                        <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                                        <span class="font-mono text-white">{{ $character->speed }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($character->speed / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-0.5">
                                        <span class="text-emerald-400 font-semibold">🛡️ Défense</span>
                                        <span class="font-mono text-white">{{ $character->defense }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, ($character->defense / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Synergies actives --}}
                            @if (!empty($character->synergies_data))
                                <div class="mb-2 space-y-1">
                                    @foreach ($character->synergies_data as $syn)
                                        <div class="px-2 py-0.5 rounded text-[10px] font-bold border flex items-center justify-between {{ !empty($syn['is_unstable']) ? 'bg-amber-950/60 border-amber-600/70 text-amber-200' : 'bg-indigo-950/50 border-indigo-500/50 text-indigo-200' }}">
                                            <span>{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                            @if (!empty($syn['is_unstable']))
                                                <span class="text-[9px] text-amber-400 font-mono">Instable</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mb-2 px-2 py-1 rounded bg-slate-900/50 border border-slate-800/40 text-center">
                                    <span class="text-[10px] text-slate-500 italic">Aucune synergie active</span>
                                </div>
                            @endif
                        </div>

                        {{-- Pied de carte Verso : Bouton Inspecter + Palmarès + Recycler --}}
                        <div class="pt-2 border-t border-slate-800 space-y-2">
                            {{-- BOUTON INSPECTER CLÉ (T1.5) --}}
                            <button
                                type="button"
                                wire:click="inspectCharacter('{{ $character->id }}')"
                                class="w-full py-1.5 px-3 rounded-xl bg-indigo-600/30 hover:bg-indigo-600/50 border border-indigo-500/50 text-indigo-200 hover:text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
                            >
                                <span>🔍</span>
                                <span>Fiche détaillée & Passifs</span>
                            </button>

                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-slate-400 font-mono" title="Palmarès arène">
                                    🏆 {{ $character->wins }}V - {{ $character->losses }}D
                                </span>

                                @if ($character->is_locked)
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-800 border border-slate-700 text-slate-500 cursor-not-allowed select-none">
                                        🔒 Protégé
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click.stop="recycleCharacter('{{ $character->id }}')"
                                        wire:confirm="Voulez-vous vraiment recycler {{ $character->name }} pour +{{ $character->recycle_value }} 🪙 ?"
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-500/10 border border-amber-500/30 text-amber-300 hover:bg-amber-500/20 transition flex items-center gap-1 cursor-pointer"
                                        title="Recycler"
                                    >
                                        <span>🪙</span>
                                        <span>+{{ $character->recycle_value }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-6">
            {{ $characters->links() }}
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODALE GRAND FORMAT D'INSPECTION DÉTAILLÉE DE COMBATTANT (T1.5)           --}}
    {{-- ========================================================================= --}}
    @if ($inspectedCharacter)
        @php
            $iRarityKey = $inspectedCharacter->rarity instanceof \App\Enums\Rarity ? $inspectedCharacter->rarity->value : $inspectedCharacter->rarity;
            $iBorderStyles = match($iRarityKey) {
                'legendary' => 'border-amber-400/80 shadow-[0_0_40px_rgba(251,191,36,0.3)]',
                'epic'      => 'border-purple-500/80 shadow-[0_0_35px_rgba(192,132,252,0.25)]',
                'rare'      => 'border-cyan-400/80 shadow-[0_0_30px_rgba(56,189,248,0.25)]',
                default     => 'border-slate-700',
            };
            $iBadgeStyle = match($iRarityKey) {
                'legendary' => 'bg-amber-400 text-slate-950 font-black shadow-[0_0_12px_rgba(251,191,36,0.6)]',
                'epic'      => 'bg-purple-500 text-white font-bold',
                'rare'      => 'bg-cyan-500 text-slate-950 font-bold',
                default     => 'bg-slate-700 text-slate-200 font-medium',
            };
            $iStars = match($iRarityKey) {
                'legendary' => 5,
                'epic'      => 4,
                'rare'      => 3,
                default     => 2,
            };
        @endphp

        <div class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
            <div
                class="bg-slate-900 border {{ $iBorderStyles }} rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl relative my-6 overflow-hidden animate-in fade-in zoom-in-95 duration-200"
                @click.away="$wire.closeInspector()"
            >
                {{-- Bouton Fermer Absolu --}}
                <button
                    type="button"
                    wire:click="closeInspector"
                    class="absolute top-5 right-5 z-20 text-slate-400 hover:text-white p-2 text-xl rounded-full bg-slate-800/80 hover:bg-slate-700 transition"
                    title="Fermer la fiche"
                >
                    ✕
                </button>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                    {{-- Colonne Gauche : Vitrine Monumentale du Héros --}}
                    <div class="md:col-span-5 flex flex-col items-center text-center p-5 rounded-2xl bg-slate-950/80 border border-slate-800/80 relative overflow-hidden">
                        {{-- Halo lumineux d'arrière-plan --}}
                        <div class="absolute w-44 h-44 rounded-full blur-3xl opacity-25 {{ $iRarityKey === 'legendary' ? 'bg-amber-400' : ($iRarityKey === 'epic' ? 'bg-purple-500' : ($iRarityKey === 'rare' ? 'bg-cyan-400' : 'bg-slate-500')) }}"></div>

                        {{-- Rareté & Étoiles --}}
                        <span class="px-3.5 py-1 rounded-full text-xs uppercase tracking-wider {{ $iBadgeStyle }} mb-2">
                            {{ $inspectedCharacter->rarity->label() }}
                        </span>

                        <div class="flex items-center gap-1 mb-4">
                            @for ($s = 1; $s <= 5; $s++)
                                <span class="text-sm {{ $s <= $iStars ? 'text-amber-400 drop-shadow-[0_0_8px_rgba(251,191,36,0.7)]' : 'text-slate-700' }}">
                                    ★
                                </span>
                            @endfor
                        </div>

                        {{-- Grand Piédestal d'Avatar --}}
                        <div class="relative w-36 h-36 rounded-3xl bg-gradient-to-b from-slate-800/60 to-slate-950 border border-slate-700/80 shadow-2xl flex items-center justify-center p-4 my-2">
                            <span class="text-7xl filter drop-shadow-[0_10px_20px_rgba(0,0,0,0.9)]">
                                {{ $inspectedCharacter->race_data['icon'] ?? '👤' }}
                            </span>

                            <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center text-lg shadow-xl" title="Arme équipée">
                                {{ $inspectedCharacter->weapon_data['icon'] ?? '⚔️' }}
                            </div>

                            <div class="absolute -top-2 -left-2 w-10 h-10 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center text-lg shadow-xl animate-pulse" title="Pouvoir élémentaire">
                                {{ $inspectedCharacter->power_data['icon'] ?? '🔮' }}
                            </div>
                        </div>

                        {{-- Nom & Titre --}}
                        <h2 class="text-xl font-black text-white tracking-tight mt-3">
                            {{ $inspectedCharacter->name }}
                        </h2>
                        <span class="text-xs text-slate-400 mt-1">
                            {{ $inspectedCharacter->race_data['name'] ?? ucfirst($inspectedCharacter->race_id) }} • {{ $inspectedCharacter->weapon_data['name'] ?? ucfirst($inspectedCharacter->weapon_id) }} ({{ $inspectedCharacter->power_data['name'] ?? ucfirst($inspectedCharacter->power_id) }})
                        </span>

                        {{-- Score de Puissance Monumental --}}
                        <div class="mt-4 flex items-center gap-2 bg-slate-900/90 px-4 py-2 rounded-xl border border-slate-800">
                            <span class="text-lg text-amber-400">⚡</span>
                            <span class="text-xs uppercase font-bold text-slate-400">Power Score :</span>
                            <span class="text-xl font-extrabold text-amber-300 font-mono">{{ $inspectedCharacter->power_score }} pts</span>
                        </div>

                        {{-- Palmarès de Combat en Arène --}}
                        <div class="w-full mt-4 p-3 rounded-xl bg-slate-900/70 border border-slate-800/80 text-xs text-left space-y-1.5">
                            <span class="uppercase font-bold text-slate-400 tracking-wider block text-[10px]">Historique d'Arène</span>
                            <div class="flex justify-between font-mono">
                                <span class="text-slate-300">Duels disputés :</span>
                                <span class="font-bold text-white">{{ $inspectedCharacter->wins + $inspectedCharacter->losses }}</span>
                            </div>
                            <div class="flex justify-between font-mono">
                                <span class="text-emerald-400">Victoires :</span>
                                <span class="font-bold text-emerald-300">{{ $inspectedCharacter->wins }}</span>
                            </div>
                            <div class="flex justify-between font-mono">
                                <span class="text-rose-400">Défaites :</span>
                                <span class="font-bold text-rose-300">{{ $inspectedCharacter->losses }}</span>
                            </div>
                            <div class="flex justify-between font-mono pt-1 border-t border-slate-800">
                                <span class="text-amber-400">Taux de victoire :</span>
                                <span class="font-bold text-amber-300">{{ $inspectedCharacter->winrate }}%</span>
                            </div>
                        </div>

                        {{-- Actions Rapides Colonne Gauche --}}
                        <div class="w-full flex items-center gap-2 mt-4">
                            <button
                                type="button"
                                wire:click="toggleLock('{{ $inspectedCharacter->id }}')"
                                class="flex-1 py-2 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $inspectedCharacter->is_locked ? 'bg-amber-500/20 text-amber-300 border-amber-500/50 shadow-md' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white' }}"
                            >
                                <span>{{ $inspectedCharacter->is_locked ? '🔒' : '🔓' }}</span>
                                <span>{{ $inspectedCharacter->is_locked ? 'Verrouillé' : 'Verrouiller' }}</span>
                            </button>

                            @if (!$inspectedCharacter->is_locked)
                                <button
                                    type="button"
                                    wire:click="recycleCharacter('{{ $inspectedCharacter->id }}')"
                                    wire:confirm="Recycler définitivement {{ $inspectedCharacter->name }} pour +{{ $inspectedCharacter->recycle_value }} 🪙 ?"
                                    class="flex-1 py-2 px-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold transition flex items-center justify-center gap-1"
                                >
                                    <span>🪙</span>
                                    <span>+{{ $inspectedCharacter->recycle_value }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Colonne Droite : Fiche Technique Complète & Passifs Détaillés --}}
                    <div class="md:col-span-7 space-y-4">
                        {{-- 1. Statistiques Détaillées avec barres pleines --}}
                        <div class="bg-slate-950/70 p-4 rounded-2xl border border-slate-800">
                            <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider mb-3 flex items-center gap-1.5">
                                <span>⚔️</span>
                                <span>Statistiques de Combat</span>
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-rose-400 font-bold">❤️ Points de Vie (PV)</span>
                                        <span class="font-mono text-white font-extrabold">{{ $inspectedCharacter->hp }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($inspectedCharacter->hp / 140) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-amber-400 font-bold">⚔️ Force d'Attaque</span>
                                        <span class="font-mono text-white font-extrabold">{{ $inspectedCharacter->strength }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($inspectedCharacter->strength / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-cyan-400 font-bold">⚡ Vitesse d'Initiative</span>
                                        <span class="font-mono text-white font-extrabold">{{ $inspectedCharacter->speed }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($inspectedCharacter->speed / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-emerald-400 font-bold">🛡️ Défense Physique</span>
                                        <span class="font-mono text-white font-extrabold">{{ $inspectedCharacter->defense }}</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, ($inspectedCharacter->defense / 100) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Détail des 3 Composants (Race, Arme, Pouvoir) & Leurs Passifs --}}
                        <div class="space-y-2.5">
                            <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider flex items-center gap-1.5">
                                <span>📜</span>
                                <span>Composants & Passifs Innés</span>
                            </h4>

                            {{-- Passif de Race --}}
                            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">{{ $inspectedCharacter->race_data['icon'] ?? '👤' }}</span>
                                    <span class="font-extrabold text-white">Race : {{ $inspectedCharacter->race_data['name'] ?? ucfirst($inspectedCharacter->race_id) }}</span>
                                    <span class="text-[10px] text-amber-400 bg-amber-950/40 px-2 py-0.5 rounded-md font-mono border border-amber-600/30 ml-auto">
                                        Passif : {{ $inspectedCharacter->race_data['passive'] ?? 'Adaptabilité' }}
                                    </span>
                                </div>
                                <p class="text-slate-400 leading-relaxed text-[11px] pl-7">
                                    {{ $inspectedCharacter->race_data['passive_desc'] ?? 'Confère un bonus universel équilibré sur toutes les statistiques.' }}
                                </p>
                            </div>

                            {{-- Passif d'Arme --}}
                            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">{{ $inspectedCharacter->weapon_data['icon'] ?? '⚔️' }}</span>
                                    <span class="font-extrabold text-white">Arme : {{ $inspectedCharacter->weapon_data['name'] ?? ucfirst($inspectedCharacter->weapon_id) }}</span>
                                    <span class="text-[10px] text-cyan-400 bg-cyan-950/40 px-2 py-0.5 rounded-md font-mono border border-cyan-600/30 ml-auto">
                                        {{ $inspectedCharacter->weapon_data['passive'] ?? 'Tranchant' }}
                                    </span>
                                </div>
                                <p class="text-slate-400 leading-relaxed text-[11px] pl-7">
                                    {{ $inspectedCharacter->weapon_data['passive_desc'] ?? $inspectedCharacter->weapon_data['description'] ?? 'Modifie la cadence et la force des attaques.' }}
                                </p>
                            </div>

                            {{-- Pouvoir Élémentaire --}}
                            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">{{ $inspectedCharacter->power_data['icon'] ?? '🔮' }}</span>
                                    <span class="font-extrabold text-white">Pouvoir : {{ $inspectedCharacter->power_data['name'] ?? ucfirst($inspectedCharacter->power_id) }}</span>
                                    <span class="text-[10px] text-indigo-400 bg-indigo-950/40 px-2 py-0.5 rounded-md font-mono border border-indigo-600/30 ml-auto">
                                        Tier {{ ucfirst($inspectedCharacter->power_data['tier'] ?? 'Commun') }}
                                    </span>
                                </div>
                                <p class="text-slate-400 leading-relaxed text-[11px] pl-7">
                                    {{ $inspectedCharacter->power_data['description'] ?? 'Inflige des altérations d\'état élémentaires en combat.' }}
                                </p>
                            </div>
                        </div>

                        {{-- 3. Synergies Actives en Détail --}}
                        <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs">
                            <h4 class="uppercase font-extrabold text-slate-400 tracking-wider mb-2 flex items-center justify-between">
                                <span>✨ Synergies Actives</span>
                                <span class="font-mono text-amber-400">{{ !empty($inspectedCharacter->synergies_data) ? count($inspectedCharacter->synergies_data) : 0 }} active(s)</span>
                            </h4>

                            @if (!empty($inspectedCharacter->synergies_data))
                                <div class="space-y-2 mt-2">
                                    @foreach ($inspectedCharacter->synergies_data as $syn)
                                        <div class="p-2.5 rounded-lg border {{ !empty($syn['is_unstable']) ? 'bg-amber-950/50 border-amber-500/60 text-amber-200' : 'bg-indigo-950/40 border-indigo-500/50 text-indigo-200' }}">
                                            <div class="flex items-center justify-between font-bold text-xs mb-1">
                                                <span>{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                                @if (!empty($syn['is_unstable']))
                                                    <span class="text-[10px] text-amber-400 font-mono bg-amber-900/60 px-2 py-0.5 rounded border border-amber-600/50">⚠️ Synergie Instable</span>
                                                @endif
                                            </div>
                                            <p class="text-slate-300 text-[11px] leading-relaxed">
                                                {{ $syn['description'] ?? 'Confère des bonus majeurs de combat.' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-slate-500 text-[11px] italic">
                                    Aucune synergie active sur cette combinaison. Associez des éléments complémentaires (ex: Démon + Feu, Elfe + Arc, Golem + Marteau) pour débloquer des effets dévastateurs !
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Pied de Modale --}}
                <div class="flex justify-end pt-5 mt-4 border-t border-slate-800">
                    <button
                        type="button"
                        wire:click="closeInspector"
                        class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer"
                    >
                        Fermer la fiche
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODALE COMPLÈTE & AVANCÉE DE RECYCLAGE EN MASSE (T1.4) --}}
    @if ($showBatchModal)
        <div class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
            <div
                class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl relative my-8 animate-in fade-in zoom-in-95 duration-200"
                @click.away="$wire.closeBatchModal()"
            >
                {{-- En-tête de la Modale --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-2xl shadow-inner">
                            ♻️
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tight">Recyclage en Masse Avancé</h3>
                            <p class="text-xs text-slate-400">Filtrez précisément les héros à recycler en pièces d'or</p>
                        </div>
                    </div>
                    <button
                        wire:click="closeBatchModal"
                        class="text-slate-400 hover:text-white p-2 text-lg rounded-xl hover:bg-slate-800 transition"
                    >
                        ✕
                    </button>
                </div>

                {{-- Corps du Formulaire de Filtrage Avancé --}}
                <div class="py-5 space-y-5 text-xs">
                    {{-- 1. Raretés ciblées --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="uppercase font-bold text-slate-400 tracking-wider">
                                1. Raretés à recycler :
                            </label>
                            <span class="text-slate-500">Cliquer pour cocher/décocher</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button
                                type="button"
                                wire:click="toggleBatchRarity('common')"
                                class="p-2.5 rounded-xl border text-center transition-all duration-150 {{ in_array('common', $batchRarities, true) ? 'bg-slate-800 border-slate-400 text-white ring-2 ring-slate-400/40 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}"
                            >
                                <span class="block font-bold text-sm">⚔️ Commun</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ in_array('common', $batchRarities, true) ? '✓ Sélectionné' : 'Ignorer' }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="toggleBatchRarity('rare')"
                                class="p-2.5 rounded-xl border text-center transition-all duration-150 {{ in_array('rare', $batchRarities, true) ? 'bg-cyan-950/60 border-cyan-400 text-cyan-200 ring-2 ring-cyan-400/40 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}"
                            >
                                <span class="block font-bold text-sm">💎 Rare</span>
                                <span class="text-[10px] text-cyan-300 font-mono">{{ in_array('rare', $batchRarities, true) ? '✓ Sélectionné' : 'Ignorer' }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="toggleBatchRarity('epic')"
                                class="p-2.5 rounded-xl border text-center transition-all duration-150 {{ in_array('epic', $batchRarities, true) ? 'bg-purple-950/60 border-purple-400 text-purple-200 ring-2 ring-purple-400/40 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}"
                            >
                                <span class="block font-bold text-sm">🔮 Épique</span>
                                <span class="text-[10px] text-purple-300 font-mono">{{ in_array('epic', $batchRarities, true) ? '✓ Sélectionné' : 'Ignorer' }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="toggleBatchRarity('legendary')"
                                class="p-2.5 rounded-xl border text-center transition-all duration-150 {{ in_array('legendary', $batchRarities, true) ? 'bg-amber-950/60 border-amber-400 text-amber-200 ring-2 ring-amber-400/40 shadow-sm' : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700' }}"
                            >
                                <span class="block font-bold text-sm">👑 Légendaire</span>
                                <span class="text-[10px] text-amber-300 font-mono">{{ in_array('legendary', $batchRarities, true) ? '✓ Sélectionné' : 'Ignorer' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- 2. Filtres avancés par Score, Race et Arme --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-950/60 p-3.5 rounded-2xl border border-slate-800">
                        {{-- Plafond de Score de Puissance --}}
                        <div>
                            <label class="block text-slate-300 font-bold mb-1.5 flex items-center justify-between">
                                <span>⚡ Power Score Max :</span>
                                @if (!empty($batchMaxPower))
                                    <span class="text-amber-400 font-mono">≤ {{ $batchMaxPower }}</span>
                                @else
                                    <span class="text-slate-500">Tous</span>
                                @endif
                            </label>
                            <input
                                type="number"
                                wire:model.live.debounce.150ms="batchMaxPower"
                                placeholder="ex: 60 (optionnel)"
                                min="0"
                                max="200"
                                class="w-full py-1.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono placeholder-slate-600 focus:outline-none focus:border-amber-400 text-xs"
                            />
                            <div class="flex gap-1 mt-1.5">
                                <button type="button" wire:click="$set('batchMaxPower', '50')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-300">≤ 50</button>
                                <button type="button" wire:click="$set('batchMaxPower', '65')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-300">≤ 65</button>
                                <button type="button" wire:click="$set('batchMaxPower', '80')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-300">≤ 80</button>
                                <button type="button" wire:click="$set('batchMaxPower', '')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-rose-300">Tous</button>
                            </div>
                        </div>

                        {{-- Filtre Race spécifique --}}
                        <div>
                            <label class="block text-slate-300 font-bold mb-1.5">
                                👤 Race ciblée :
                            </label>
                            <select
                                wire:model.live="batchRace"
                                class="w-full py-1.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-indigo-400 text-xs cursor-pointer"
                            >
                                <option value="">Toutes les races</option>
                                @foreach ($races as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['icon'] }} {{ $r['name'] }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-500 block mt-1">Cible une race spécifique</span>
                        </div>

                        {{-- Filtre Arme spécifique --}}
                        <div>
                            <label class="block text-slate-300 font-bold mb-1.5">
                                ⚔️ Arme ciblée :
                            </label>
                            <select
                                wire:model.live="batchWeapon"
                                class="w-full py-1.5 px-3 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:border-cyan-400 text-xs cursor-pointer"
                            >
                                <option value="">Toutes les armes</option>
                                @foreach ($weapons as $w)
                                    <option value="{{ $w['id'] }}">{{ $w['icon'] }} {{ $w['name'] }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-500 block mt-1">Cible une arme spécifique</span>
                        </div>
                    </div>

                    {{-- 3. Sécurité : Préservation des synergies --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">✨</span>
                            <div>
                                <span class="font-bold text-slate-200 block">Exclure les combattants avec synergie</span>
                                <span class="text-[11px] text-slate-500">Empêche de détruire accidentellement vos cartes à synergies actives</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model.live="batchExcludeSynergies"
                                class="sr-only peer"
                            />
                            <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    {{-- 4. Aperçu visuel en direct des cartes ciblées --}}
                    <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-800/80">
                            <span class="text-slate-400 font-medium">Héros ciblés : <strong class="text-white font-mono text-sm">{{ $batchPreview['count'] }}</strong></span>
                            <span class="text-slate-400 font-medium">Gain d'or total : <strong class="text-amber-400 font-black font-mono text-base">+{{ number_format($batchPreview['coins'], 0, ',', ' ') }} 🪙</strong></span>
                        </div>

                        {{-- Liste scrollable des héros ciblés pour vérification visuelle --}}
                        @if ($batchPreview['count'] > 0)
                            <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1 text-[11px]">
                                @foreach ($batchPreview['characters'] as $c)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/80 border border-slate-800/60 hover:border-slate-700">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="shrink-0">{{ $c->race_data['icon'] ?? '👤' }}</span>
                                            <span class="font-bold text-white truncate max-w-[140px] sm:max-w-[200px]">{{ $c->name }}</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 uppercase font-mono">{{ $c->rarity->label() }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-slate-400 font-mono">⚡ {{ $c->power_score }}</span>
                                            <span class="text-amber-400 font-bold font-mono">+{{ $c->recycle_value }} 🪙</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-4 text-center text-slate-500">
                                <span>Aucun combattant ne correspond aux critères sélectionnés.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Alerte Sécurité Verrouillage --}}
                    <div class="bg-amber-950/20 border border-amber-600/30 rounded-xl p-3 flex items-start gap-2.5 text-[11px] text-amber-200">
                        <span class="text-base shrink-0">🔒</span>
                        <p class="leading-relaxed">
                            <strong>Protection automatique garantie :</strong> Vos <strong>{{ $lockedCount }} héros verrouillés</strong> ainsi que vos héros déployés en arène sont strictement exclus du recyclage.
                        </p>
                    </div>
                </div>

                {{-- Actions de la Modale --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-800">
                    <button
                        type="button"
                        wire:click="resetBatchFilters"
                        class="text-xs text-slate-400 hover:text-slate-200 underline"
                    >
                        ↺ Réinitialiser les critères
                    </button>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <button
                            type="button"
                            wire:click="closeBatchModal"
                            class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            wire:click="confirmBatchRecycle"
                            wire:loading.attr="disabled"
                            @if ($batchPreview['count'] === 0) disabled @endif
                            class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/25 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="confirmBatchRecycle">
                                Recycler {{ $batchPreview['count'] }} {{ Str::plural('héros', $batchPreview['count']) }} (+{{ number_format($batchPreview['coins'], 0, ',', ' ') }} 🪙)
                            </span>
                            <span wire:loading wire:target="confirmBatchRecycle">
                                Recyclage en cours...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
