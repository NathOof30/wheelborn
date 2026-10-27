@props([
    'character' => null,
    'readOnly' => false,
    'closeAction' => 'closeInspector',
])

@if ($character)
    @php
        $rarityKey = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : (string) $character->rarity;
        $borderStyles = match($rarityKey) {
            'legendary' => 'border-amber-400/80 shadow-[0_0_40px_rgba(251,191,36,0.3)]',
            'epic'      => 'border-purple-500/80 shadow-[0_0_35px_rgba(192,132,252,0.25)]',
            'rare'      => 'border-cyan-400/80 shadow-[0_0_30px_rgba(56,189,248,0.25)]',
            default     => 'border-slate-700',
        };
        $cardBorderStyles = match($rarityKey) {
            'legendary' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950 shadow-[0_0_25px_rgba(251,191,36,0.25)] hover:shadow-[0_0_35px_rgba(251,191,36,0.4)]',
            'epic'      => 'border-purple-500/70 bg-gradient-to-b from-purple-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(192,132,252,0.2)] hover:shadow-[0_0_30px_rgba(192,132,252,0.35)]',
            'rare'      => 'border-cyan-400/60 bg-gradient-to-b from-cyan-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(56,189,248,0.18)] hover:shadow-[0_0_30px_rgba(56,189,248,0.3)]',
            default     => 'border-slate-800 bg-slate-900/90 hover:border-slate-700',
        };
        $badgeStyle = match($rarityKey) {
            'legendary' => 'bg-amber-400 text-slate-950 font-black shadow-[0_0_12px_rgba(251,191,36,0.6)]',
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
        $rarityLabel = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->label() : match($rarityKey) {
            'legendary' => 'Légendaire',
            'epic'      => 'Épique',
            'rare'      => 'Rare',
            default     => 'Commune',
        };
    @endphp

    <div class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div
            class="bg-slate-900 border {{ $borderStyles }} rounded-3xl max-w-4xl w-full p-4 sm:p-5 shadow-2xl relative my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-200"
            @click.away="$wire.{{ $closeAction }}()"
        >
            {{-- En-tête Compact de la Fiche Détaillée --}}
            <div class="flex items-center justify-between pb-3 mb-3.5 border-b border-slate-800/80">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider shrink-0 {{ $badgeStyle }}">
                        {{ $rarityLabel }}
                    </span>
                    <h3 class="text-base sm:text-lg font-black text-white tracking-tight truncate">
                        {{ $character->name }}
                    </h3>
                    <span class="text-xs text-slate-400 font-medium hidden sm:inline truncate">
                        — {{ $character->race_data['name'] ?? ucfirst($character->race_id) }} • {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }} <span class="text-indigo-400">({{ $character->power_data['name'] ?? ucfirst($character->power_id) }})</span>
                    </span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        wire:click="{{ $closeAction }}"
                        class="text-slate-400 hover:text-white p-1 rounded-lg bg-slate-800/80 hover:bg-slate-700 transition cursor-pointer"
                        title="Fermer la fiche"
                    >
                        <span class="text-sm px-1.5 font-bold">✕</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">
                {{-- COLONNE GAUCHE : LA CARTE AUTHENTIQUE & FIGÉE (3D FLIP RECTO & VERSO AU CLIC) --}}
                <div class="lg:col-span-5 flex flex-col items-center justify-center">
                    <div
                        x-data="{ flipped: false }"
                        class="perspective-1000 w-full max-w-[290px] group select-none transition-transform duration-300 hover:scale-[1.02]"
                        style="height: 440px; min-height: 440px;"
                    >
                        <div
                            class="relative w-full h-full preserve-3d card-flip-transition"
                            :class="{ 'rotate-y-180': flipped }"
                            style="height: 440px; min-height: 440px;"
                        >
                            {{-- FACE RECTO --}}
                            <div
                                @click="flipped = true"
                                class="absolute inset-0 w-full h-full rounded-3xl border {{ $cardBorderStyles }} overflow-hidden p-3.5 flex flex-col justify-between backface-hidden shadow-2xl cursor-pointer"
                                :class="{ 'pointer-events-none': flipped }"
                                style="height: 440px; min-height: 440px;"
                                title="Cliquer pour retourner la carte"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider {{ $badgeStyle }}">
                                        {{ $rarityLabel }}
                                    </span>

                                    <div class="flex items-center gap-1.5">
                                        @if ($character->is_favorite)
                                            <span class="text-amber-300 text-xs bg-amber-950/80 px-2 py-0.5 rounded-full border border-amber-400/50 shadow font-bold">
                                                ⭐ Vitrine
                                            </span>
                                        @endif
                                        @if ($character->is_locked)
                                            <span class="text-amber-400 text-xs bg-slate-950/80 px-2 py-0.5 rounded-full border border-amber-500/40 shadow-sm" title="Protégé contre le recyclage">
                                                🔒
                                            </span>
                                        @endif
                                        <div class="flex items-center gap-1 bg-slate-950/80 px-2.5 py-0.5 rounded-lg border border-slate-800">
                                            <span class="text-xs text-amber-400">⚡</span>
                                            <span class="font-extrabold text-sm text-white font-mono">{{ $character->power_score }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Scène Visuelle : Avatar Composite Paper Doll --}}
                                <div class="flex-1 my-1 flex items-center justify-center overflow-hidden">
                                    <x-character-avatar :character="$character" class="max-w-[200px]" />
                                </div>

                                {{-- Pied de carte Recto --}}
                                <div class="text-center pt-2 border-t border-slate-800/80">
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span class="text-xs {{ $s <= $stars ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]' : 'text-slate-800' }}">
                                                ★
                                            </span>
                                        @endfor
                                    </div>
                                    <h3 class="text-sm font-black text-white truncate tracking-tight group-hover:text-amber-300 transition-colors">
                                        {{ $character->name }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5 font-medium">
                                        {{ $character->race_data['name'] ?? ucfirst($character->race_id) }} • {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}
                                        <span class="text-indigo-400 font-semibold">({{ $character->power_data['name'] ?? ucfirst($character->power_id) }})</span>
                                    </p>
                                    <div class="mt-2 text-[10px] text-indigo-300/80 font-mono tracking-wide flex items-center justify-center gap-1 opacity-70">
                                        <span>🔄</span>
                                        <span>Cliquer pour retourner</span>
                                    </div>
                                </div>
                            </div>

                            {{-- FACE VERSO --}}
                            <div
                                @click="if (!$event.target.closest('button, a')) flipped = false"
                                class="absolute inset-0 w-full h-full rounded-3xl border {{ $cardBorderStyles }} bg-slate-950/95 backdrop-blur-md overflow-hidden p-3.5 flex flex-col justify-between backface-hidden rotate-y-180 shadow-2xl cursor-pointer"
                                :class="{ 'pointer-events-auto': flipped, 'pointer-events-none': !flipped }"
                                style="height: 440px; min-height: 440px;"
                                title="Cliquer pour revenir au recto"
                            >
                                <div>
                                    {{-- En-tête Verso --}}
                                    <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-800">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="px-2 py-0.5 rounded text-[9px] uppercase tracking-wider {{ $badgeStyle }} shrink-0">
                                                {{ $rarityLabel }}
                                            </span>
                                            <h4 class="text-xs font-black text-white truncate" title="{{ $character->name }}">
                                                {{ $character->name }}
                                            </h4>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <button
                                                type="button"
                                                @click.stop="flipped = false"
                                                class="p-1 rounded-lg text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition cursor-pointer"
                                                title="Retourner la carte"
                                            >
                                                🔄
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Tri-Slot icônes --}}
                                    <div class="grid grid-cols-3 gap-1 mb-2.5 text-center bg-slate-900/80 p-2 rounded-xl border border-slate-800">
                                        <div>
                                            <span class="text-sm block">{{ $character->race_data['icon'] ?? '👤' }}</span>
                                            <span class="text-[9px] text-slate-300 font-bold truncate block">{{ $character->race_data['name'] ?? ucfirst($character->race_id) }}</span>
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

                                    {{-- Barres de stats --}}
                                    <div class="space-y-1 mb-2.5 bg-slate-900/60 p-2 rounded-xl border border-slate-800/50 text-[10px]">
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-rose-400 font-semibold">❤️ PV</span>
                                                <span class="font-mono text-white">{{ $character->hp }} / {{ $character->max_hp }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($character->hp / max(1, $character->max_hp)) * 100) }}%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                                <span class="font-mono text-white">{{ $character->strength }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($character->strength / 120) * 100) }}%"></div>
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
                                    @endif
                                </div>

                                {{-- Pied Verso --}}
                                <div class="pt-2 border-t border-slate-800 text-center">
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        🏆 {{ $character->wins }}V - {{ $character->losses }}D ({{ $character->winrate }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLONNE DROITE : FICHE TECHNIQUE COMPLÈTE & PASSIFS COMPACTS --}}
                <div class="lg:col-span-7 flex flex-col justify-between space-y-3.5">
                    {{-- 1. Détail des 3 Composants (Race, Arme, Pouvoir) & Leurs Passifs --}}
                    <div class="bg-slate-950/70 p-3.5 rounded-2xl border border-slate-800">
                        <span class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider block mb-2.5">
                            📜 Composants & Passifs Innés
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            {{-- Passif de Race --}}
                            <div class="p-3 rounded-xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between space-y-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="text-base">{{ $character->race_data['icon'] ?? '👤' }}</span>
                                        <span class="font-black text-white text-xs truncate">{{ $character->race_data['name'] ?? ucfirst($character->race_id) }}</span>
                                    </div>
                                    <span class="text-[10px] text-amber-400 bg-amber-950/40 px-1.5 py-0.5 rounded font-mono border border-amber-600/30 inline-block mb-1">
                                        Passif : {{ $character->race_data['passive'] ?? 'Inné' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-snug">
                                    {{ $character->race_data['passive_desc'] ?? 'Bonus universel sur les statistiques.' }}
                                </p>
                            </div>

                            {{-- Passif d'Arme --}}
                            <div class="p-3 rounded-xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between space-y-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="text-base">{{ $character->weapon_data['icon'] ?? '⚔️' }}</span>
                                        <span class="font-black text-white text-xs truncate">{{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}</span>
                                    </div>
                                    <span class="text-[10px] text-cyan-400 bg-cyan-950/40 px-1.5 py-0.5 rounded font-mono border border-cyan-600/30 inline-block mb-1">
                                        {{ $character->weapon_data['passive'] ?? 'Attaque' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-snug">
                                    {{ $character->weapon_data['passive_desc'] ?? $character->weapon_data['description'] ?? 'Modifie les caractéristiques de frappe.' }}
                                </p>
                            </div>

                            {{-- Pouvoir Élémentaire --}}
                            <div class="p-3 rounded-xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between space-y-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="text-base">{{ $character->power_data['icon'] ?? '🔮' }}</span>
                                        <span class="font-black text-white text-xs truncate">{{ $character->power_data['name'] ?? ucfirst($character->power_id) }}</span>
                                    </div>
                                    <span class="text-[10px] text-indigo-400 bg-indigo-950/40 px-1.5 py-0.5 rounded font-mono border border-indigo-600/30 inline-block mb-1">
                                        Tier {{ ucfirst($character->power_data['tier'] ?? 'Commun') }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-snug">
                                    {{ $character->power_data['description'] ?? 'Canalise une force élémentaire en duel.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Synergies Actives en Détail --}}
                    <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider">✨ Synergies Actives</span>
                            <span class="text-[10px] font-mono text-amber-400">{{ !empty($character->synergies_data) ? count($character->synergies_data) : 0 }} active(s)</span>
                        </div>

                        @if (!empty($character->synergies_data))
                            <div class="space-y-2">
                                @foreach ($character->synergies_data as $syn)
                                    <div class="p-2.5 rounded-xl border flex items-start justify-between gap-2.5 {{ !empty($syn['is_unstable']) ? 'bg-amber-950/40 border-amber-500/50 text-amber-200' : 'bg-indigo-950/40 border-indigo-500/50 text-indigo-200' }}">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="text-base shrink-0 mt-0.5">{{ $syn['icon'] ?? '✨' }}</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-bold text-xs block leading-tight">{{ $syn['name'] }}</span>
                                                <p class="text-[11px] text-slate-300 leading-relaxed mt-0.5 whitespace-normal break-words">{{ $syn['description'] ?? 'Confère des bonus majeurs de combat.' }}</p>
                                            </div>
                                        </div>
                                        @if (!empty($syn['is_unstable']))
                                            <span class="text-[9px] text-amber-400 font-mono bg-amber-900/60 px-1.5 py-0.5 rounded border border-amber-600/50 shrink-0 mt-0.5">Instable</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-2 px-3 rounded-xl bg-slate-900/40 border border-slate-800/40 text-[11px] text-slate-500 italic">
                                Aucune synergie active sur cette combinaison d'éléments.
                            </div>
                        @endif
                    </div>

                    {{-- 3. Barre Inférieure : Palmarès d'Arène & Actions --}}
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 flex flex-wrap items-center justify-between gap-2.5 text-xs">
                        {{-- Palmarès de Combat --}}
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-medium text-[11px]">🏆 Arène :</span>
                            <span class="font-mono text-white font-bold text-[11px]">{{ $character->wins }}V - {{ $character->losses }}D</span>
                            <span class="text-[10px] text-amber-400 font-mono bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20 font-bold" title="Taux de victoire">
                                {{ $character->winrate }}%
                            </span>
                        </div>

                        {{-- Actions si propriétaire (non read-only) --}}
                        @if (!$readOnly)
                            <div class="flex items-center gap-2 ml-auto">
                                <button
                                    type="button"
                                    wire:click="toggleFavorite('{{ $character->id }}')"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1.5 rounded-xl border text-[11px] font-bold transition flex items-center gap-1 cursor-pointer disabled:opacity-50 {{ $character->is_favorite ? 'bg-amber-400/20 text-amber-300 border-amber-400/50 shadow-[0_0_8px_rgba(251,191,36,0.3)]' : 'bg-slate-900 hover:bg-slate-800 text-slate-300 border-slate-700 hover:text-amber-300' }}"
                                    title="{{ $character->is_favorite ? 'Retirer des favoris de profil' : 'Mettre en favori (vitrine Top 3)' }}"
                                >
                                    <span>{{ $character->is_favorite ? '⭐' : '☆' }}</span>
                                    <span>{{ $character->is_favorite ? 'Favori' : 'En vitrine' }}</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="toggleLock('{{ $character->id }}')"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1.5 rounded-xl border text-[11px] font-bold transition flex items-center gap-1 cursor-pointer disabled:opacity-50 {{ $character->is_locked ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-slate-900 hover:bg-slate-800 text-slate-300 border-slate-700 hover:text-white' }}"
                                    title="{{ $character->is_locked ? 'Déverrouiller la carte' : 'Verrouiller la carte' }}"
                                >
                                    <span>{{ $character->is_locked ? '🔒' : '🔓' }}</span>
                                    <span>{{ $character->is_locked ? 'Verrouillée' : 'Verrouiller' }}</span>
                                </button>

                                @if (!$character->is_locked && !$character->is_favorite)
                                    <button
                                        type="button"
                                        wire:click="recycleCharacter('{{ $character->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:confirm="Recycler définitivement {{ $character->name }} pour +{{ $character->recycle_value }} 🪙 ?"
                                        class="px-2.5 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/40 text-amber-300 text-[11px] font-bold transition flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                        title="Recycler cette carte"
                                    >
                                        <span>🪙</span>
                                        <span>Recycler (+{{ $character->recycle_value }})</span>
                                    </button>
                                @else
                                    <span class="px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-800 border border-slate-700 text-slate-400 select-none cursor-not-allowed">
                                        🔒 Protégé
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="ml-auto">
                                <button
                                    type="button"
                                    wire:click="{{ $closeAction }}"
                                    class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition cursor-pointer"
                                >
                                    Fermer la fiche
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

