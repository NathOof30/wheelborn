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

            {{-- Répartition par Rareté --}}
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="px-3 py-1.5 rounded-lg bg-amber-400/10 text-amber-300 border border-amber-400/30 flex items-center gap-1.5" title="Légendaires">
                    <span>👑</span>
                    <span>{{ $rarityCounts['legendary'] ?? 0 }} Légendaires</span>
                </span>
                <span class="px-3 py-1.5 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/30 flex items-center gap-1.5" title="Épiques">
                    <span>🔮</span>
                    <span>{{ $rarityCounts['epic'] ?? 0 }} Épiques</span>
                </span>
                <span class="px-3 py-1.5 rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/30 flex items-center gap-1.5" title="Rares">
                    <span>💎</span>
                    <span>{{ $rarityCounts['rare'] ?? 0 }} Rares</span>
                </span>
                <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700 flex items-center gap-1.5" title="Communs">
                    <span>⚔️</span>
                    <span>{{ $rarityCounts['common'] ?? 0 }} Communs</span>
                </span>
            </div>
        </div>
    </div>

    {{-- État vide si aucune carte n'est possédée --}}
    @if ($characters->isEmpty())
        <div class="bg-slate-900/60 border-2 border-dashed border-slate-800 rounded-3xl p-12 text-center my-8 max-w-2xl mx-auto">
            <div class="w-20 h-20 mx-auto rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center text-4xl mb-4 animate-bounce">
                🎡
            </div>
            <h3 class="text-xl font-extrabold text-white mb-2">Votre caserne est déserte !</h3>
            <p class="text-sm text-slate-400 mb-6 max-w-md mx-auto">
                Vous n'avez pas encore de combattant en réserve. Lancez la Roue du Destin et choisissez <strong class="text-emerald-400">"Garder"</strong> pour recruter vos premiers héros.
            </p>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 font-extrabold rounded-xl shadow-lg hover:scale-105 transition-all duration-200"
            >
                <span>🎡</span>
                <span>Faire tourner la Roue du Destin</span>
            </a>
        </div>
    @else
        {{-- Grille des Cartes de Combattants --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($characters as $character)
                @php
                    $rarityClass = match($character->rarity?->value) {
                        'legendary' => 'border-amber-400/80 shadow-amber-500/10 hover:shadow-amber-500/25 hover:border-amber-400 bg-gradient-to-b from-slate-900 to-amber-950/20',
                        'epic'      => 'border-purple-400/80 shadow-purple-500/10 hover:shadow-purple-500/25 hover:border-purple-400 bg-gradient-to-b from-slate-900 to-purple-950/20',
                        'rare'      => 'border-cyan-400/80 shadow-cyan-500/10 hover:shadow-cyan-500/25 hover:border-cyan-400 bg-gradient-to-b from-slate-900 to-cyan-950/20',
                        default     => 'border-slate-800 hover:border-slate-600 bg-slate-900/90',
                    };

                    $badgeClass = match($character->rarity?->value) {
                        'legendary' => 'bg-amber-400/20 text-amber-300 border-amber-400/40',
                        'epic'      => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
                        'rare'      => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40',
                        default     => 'bg-slate-800 text-slate-300 border-slate-700',
                    };
                @endphp

                <div class="border rounded-2xl p-5 shadow-xl transition-all duration-300 hover:-translate-y-1 relative flex flex-col justify-between overflow-hidden {{ $rarityClass }}">
                    {{-- En-tête de la carte : Rareté & Power Score --}}
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                <span>★</span>
                                <span>{{ $character->rarity?->label() ?? 'Commun' }}</span>
                            </span>

                            <div class="flex items-center gap-1 bg-slate-950/90 px-2.5 py-1 rounded-lg border border-slate-800 text-xs font-mono font-black text-amber-400" title="Score de Puissance">
                                <span>⚡</span>
                                <span>{{ $character->power_score }}</span>
                            </div>
                        </div>

                        {{-- Nom du Combattant --}}
                        <h4 class="text-lg font-black text-white tracking-wide truncate mb-3" title="{{ $character->name }}">
                            {{ $character->name }}
                        </h4>

                        {{-- Slots : Race, Arme, Pouvoir --}}
                        <div class="grid grid-cols-3 gap-1.5 mb-4 text-center">
                            {{-- Race --}}
                            <div class="bg-slate-950/70 p-2 rounded-lg border border-slate-800/80" title="Race : {{ $character->race_data['name'] ?? ucfirst($character->race_id) }}">
                                <span class="text-base block mb-0.5">{{ $character->race_data['icon'] ?? '👤' }}</span>
                                <span class="text-[11px] font-bold text-slate-300 block truncate">
                                    {{ $character->race_data['name'] ?? ucfirst($character->race_id) }}
                                </span>
                            </div>

                            {{-- Arme --}}
                            <div class="bg-slate-950/70 p-2 rounded-lg border border-slate-800/80" title="Arme : {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}">
                                <span class="text-base block mb-0.5">{{ $character->weapon_data['icon'] ?? '⚔️' }}</span>
                                <span class="text-[11px] font-bold text-slate-300 block truncate">
                                    {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}
                                </span>
                            </div>

                            {{-- Pouvoir --}}
                            <div class="bg-slate-950/70 p-2 rounded-lg border border-slate-800/80" title="Pouvoir : {{ $character->power_data['name'] ?? ucfirst($character->power_id) }}">
                                <span class="text-base block mb-0.5">{{ $character->power_data['icon'] ?? '✨' }}</span>
                                <span class="text-[11px] font-bold text-indigo-300 block truncate">
                                    {{ $character->power_data['name'] ?? ucfirst($character->power_id) }}
                                </span>
                            </div>
                        </div>

                        {{-- Mini-barres de Statistiques --}}
                        <div class="space-y-1.5 bg-slate-950/50 p-2.5 rounded-xl border border-slate-800/60 mb-4 text-xs">
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
