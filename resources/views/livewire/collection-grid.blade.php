<div class="space-y-6">
    {{-- Notification Toast / Banner --}}
    @if ($notification)
        <div class="p-4 rounded-xl flex items-center justify-between border shadow-lg transition-all duration-300 {{ $notificationType === 'success' ? 'bg-emerald-950/80 border-emerald-500/50 text-emerald-200' : 'bg-rose-950/80 border-rose-500/50 text-rose-200' }}">
            <div class="flex items-center gap-3">
                <span class="text-xl">{{ $notificationType === 'success' ? '✨' : '⚠️' }}</span>
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
            {{-- Compteur Global & Meilleur Power --}}
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2.5 bg-slate-950/80 px-4 py-2 rounded-xl border border-slate-800">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Effectif</span>
                        <span class="text-base font-extrabold text-white font-mono">
                            {{ $totalCount }} {{ Str::plural('Héros', $totalCount) }}
                        </span>
                    </div>
                </div>

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

            {{-- Répartition par Rareté (cliquables pour filtrer directement !) --}}
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
            </div>
        </div>
    </div>

    {{-- BARRE DE FILTRES ET DE TRI DYNAMIQUE (T1.2) --}}
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
            <div class="flex items-center gap-2">
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

                {{-- Filtre Rareté "Toutes" si filtré --}}
                @if (!empty($rarity))
                    <button
                        wire:click="$set('rarity', '')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 flex items-center gap-1"
                        title="Annuler le filtre de rareté"
                    >
                        <span>Filtre rareté actif : <strong>{{ ucfirst($rarity) }}</strong></span>
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
        {{-- Grille Responsive des Cartes de Héros --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($characters as $character)
                @php
                    $rarityKey = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : $character->rarity;
                    $borderStyles = match($rarityKey) {
                        'legendary' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(251,191,36,0.2)]',
                        'epic'      => 'border-purple-500/70 bg-gradient-to-b from-purple-950/30 via-slate-900 to-slate-950 shadow-[0_0_15px_rgba(192,132,252,0.15)]',
                        'rare'      => 'border-cyan-400/60 bg-gradient-to-b from-cyan-950/30 via-slate-900 to-slate-950 shadow-[0_0_15px_rgba(56,189,248,0.12)]',
                        default     => 'border-slate-800 bg-slate-900/90',
                    };
                    $badgeStyle = match($rarityKey) {
                        'legendary' => 'bg-amber-400 text-slate-950 font-black shadow-[0_0_10px_rgba(251,191,36,0.5)]',
                        'epic'      => 'bg-purple-500 text-white font-bold',
                        'rare'      => 'bg-cyan-500 text-slate-950 font-bold',
                        default     => 'bg-slate-700 text-slate-200 font-medium',
                    };
                @endphp

                <div class="rounded-2xl border {{ $borderStyles }} p-4 flex flex-col justify-between transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl relative group">
                    <div>
                        {{-- En-tête : Rareté & Power Score --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider {{ $badgeStyle }}">
                                {{ $character->rarity->label() }}
                            </span>
                            <div class="flex items-center gap-1 bg-slate-950/80 px-2 py-0.5 rounded-md border border-slate-800">
                                <span class="text-xs text-amber-400">⚡</span>
                                <span class="font-extrabold text-sm text-white font-mono">{{ $character->power_score }}</span>
                            </div>
                        </div>

                        {{-- Nom du Personnage --}}
                        <h3 class="text-base font-extrabold text-white truncate tracking-tight mb-2 group-hover:text-amber-300 transition-colors" title="{{ $character->name }}">
                            {{ $character->name }}
                        </h3>

                        {{-- Composition Tri-Slot (Race, Arme, Pouvoir) --}}
                        <div class="grid grid-cols-3 gap-1.5 py-2 px-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 mb-3 text-center text-xs">
                            <div title="Race">
                                <span class="text-base block">{{ $character->race_data['icon'] ?? '👤' }}</span>
                                <span class="text-[10px] text-slate-400 truncate block mt-0.5">{{ $character->race_data['name'] ?? ucfirst($character->race_id) }}</span>
                            </div>
                            <div title="Arme">
                                <span class="text-base block">{{ $character->weapon_data['icon'] ?? '⚔️' }}</span>
                                <span class="text-[10px] text-slate-400 truncate block mt-0.5">{{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}</span>
                            </div>
                            <div title="Pouvoir">
                                <span class="text-base block">{{ $character->power_data['icon'] ?? '🔮' }}</span>
                                <span class="text-[10px] text-indigo-300 truncate block mt-0.5">{{ $character->power_data['name'] ?? ucfirst($character->power_id) }}</span>
                            </div>
                        </div>

                        {{-- Mini Barres de Stats --}}
                        <div class="space-y-1.5 mb-3 bg-slate-950/40 p-2.5 rounded-xl border border-slate-800/50">
                            {{-- PV --}}
                            <div>
                                <div class="flex justify-between text-[11px] mb-0.5">
                                    <span class="text-rose-400 font-semibold">❤️ PV</span>
                                    <span class="font-mono text-white">{{ $character->hp }}</span>
                                </div>
                                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($character->hp / 140) * 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Force --}}
                            <div>
                                <div class="flex justify-between text-[11px] mb-0.5">
                                    <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                    <span class="font-mono text-white">{{ $character->strength }}</span>
                                </div>
                                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($character->strength / 100) * 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Vitesse --}}
                            <div>
                                <div class="flex justify-between text-[11px] mb-0.5">
                                    <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                                    <span class="font-mono text-white">{{ $character->speed }}</span>
                                </div>
                                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($character->speed / 100) * 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Défense --}}
                            <div>
                                <div class="flex justify-between text-[11px] mb-0.5">
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
                            <div class="mb-4 space-y-1">
                                @foreach ($character->synergies_data as $syn)
                                    <div class="px-2.5 py-1 rounded-md text-[11px] font-bold border flex items-center justify-between {{ !empty($syn['is_unstable']) ? 'bg-amber-950/50 border-amber-600/60 text-amber-200' : 'bg-indigo-950/40 border-indigo-500/40 text-indigo-200' }}">
                                        <span>{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                        @if (!empty($syn['is_unstable']))
                                            <span class="text-[10px] text-amber-400">⚠️ Instable</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Pied de carte : Ratio de combat & Bouton Recycler --}}
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-400 font-mono" title="Palmarès arène">
                            🏆 {{ $character->wins }}V - {{ $character->losses }}D
                        </span>

                        <button
                            wire:click="recycleCharacter('{{ $character->id }}')"
                            wire:confirm="Voulez-vous vraiment recycler {{ $character->name }} pour +{{ $character->recycle_value }} 🪙 ?"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500/10 border border-amber-500/30 text-amber-300 hover:bg-amber-500/20 hover:border-amber-500/60 transition duration-150 flex items-center gap-1 disabled:opacity-50"
                            title="Recycler ce héros"
                        >
                            <span>🪙</span>
                            <span>+{{ $character->recycle_value }}</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-6">
            {{ $characters->links() }}
        </div>
    @endif
</div>
