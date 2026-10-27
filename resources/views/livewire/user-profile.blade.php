<div class="space-y-8 select-none">
    {{-- Notification Toast Flash --}}
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 5000)"
            class="p-4 rounded-2xl flex items-center justify-between border shadow-lg transition-all duration-300 {{ $notificationType === 'error' ? 'bg-rose-950/80 border-rose-500/50 text-rose-200' : ($notificationType === 'warning' ? 'bg-amber-950/80 border-amber-500/50 text-amber-200' : 'bg-emerald-950/80 border-emerald-500/50 text-emerald-200') }}"
        >
            <div class="flex items-center gap-2 text-sm font-semibold">
                <span>{{ $notificationType === 'error' ? '⚠️' : ($notificationType === 'warning' ? '⭐' : '✅') }}</span>
                <span>{{ $notification }}</span>
            </div>
            <button wire:click="dismissNotification" class="text-xs opacity-70 hover:opacity-100 transition p-1 cursor-pointer">✕</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- BARRE D'ONGLETS SUPÉRIEURE (DESTINÉE & PALMARÈS VS PARAMÈTRES DU COMPTE)   --}}
    {{-- ========================================================================= --}}
    <div class="flex items-center justify-center">
        <div class="inline-flex p-1.5 rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-md shadow-xl gap-2">
            <button
                type="button"
                wire:click="setTab('overview')"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer {{ $activeTab === 'overview' ? 'bg-gradient-to-r from-amber-500/20 via-amber-400/20 to-amber-500/20 border border-amber-400/50 text-amber-300 shadow-[0_0_15px_rgba(251,191,36,0.25)]' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}"
            >
                <span class="text-base">🌟</span>
                <span>Destinée & Palmarès</span>
            </button>

            <button
                type="button"
                wire:click="setTab('settings')"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 cursor-pointer {{ $activeTab === 'settings' ? 'bg-gradient-to-r from-cyan-500/20 via-cyan-400/20 to-cyan-500/20 border border-cyan-400/50 text-cyan-300 shadow-[0_0_15px_rgba(56,189,248,0.25)]' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}"
            >
                <span class="text-base">⚙️</span>
                <span>Paramètres du Compte</span>
            </button>
        </div>
    </div>

    @if ($activeTab === 'overview')
        {{-- ========================================================================= --}}
        {{-- ONGLET 1 : DESTINÉE & PALMARÈS DU JOUEUR                                  --}}
        {{-- ========================================================================= --}}

        {{-- 1. BANNIÈRE HERO DU JOUEUR (Identité, Niveau, XP & Ligue) --}}
        @php
            $leagueBadgeBg = match($league->name) {
                'Maître'  => 'from-purple-950/40 via-purple-900/20 to-slate-950 border-purple-500/40 shadow-purple-500/20',
                'Platine' => 'from-cyan-950/40 via-cyan-900/20 to-slate-950 border-cyan-500/40 shadow-cyan-500/20',
                'Or'      => 'from-amber-950/40 via-amber-900/20 to-slate-950 border-amber-500/40 shadow-amber-500/20',
                'Argent'  => 'from-slate-800/40 via-slate-900/20 to-slate-950 border-slate-600/40 shadow-slate-500/10',
                default   => 'from-amber-950/20 via-slate-900/20 to-slate-950 border-amber-800/40 shadow-amber-900/10',
            };
        @endphp

        <div class="relative rounded-3xl p-6 sm:p-8 border bg-gradient-to-br {{ $leagueBadgeBg }} backdrop-blur-xl shadow-2xl overflow-hidden">
            {{-- Halo Cosmique d'arrière-plan --}}
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full blur-3xl opacity-20 bg-amber-400 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                {{-- Identité & Titre --}}
                <div class="flex items-center gap-5">
                    {{-- Avatar & Blason de Ligue --}}
                    <div class="relative shrink-0">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-900/90 border-2 border-amber-400/50 p-2 flex flex-col items-center justify-center shadow-xl shadow-amber-500/10">
                            <span class="text-3xl sm:text-4xl">{{ $league->icon() }}</span>
                            <span class="text-[9px] uppercase font-black tracking-widest text-amber-300 mt-1">{{ $league->name }}</span>
                        </div>
                        <div class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-full bg-slate-950 border border-slate-700 text-[10px] font-mono text-slate-300 font-bold shadow">
                            {{ $user->elo ?? 1000 }} Élo
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl sm:text-3xl font-black text-white font-display tracking-wide">
                                {{ $user->name }}
                            </h1>
                            @if ($user->isAdmin())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                    Admin
                                </span>
                            @endif
                        </div>

                        {{-- Titre Honorifique débloqué --}}
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900/80 border border-amber-400/30 text-amber-300 text-xs font-semibold shadow-sm">
                            <span>🎖️</span>
                            <span>Titre : « {{ $levelProgress['title'] }} »</span>
                        </div>

                        {{-- Date d'inscription --}}
                        <p class="text-xs text-slate-400 font-medium">
                            Combattant WheelBorn depuis le {{ $user->created_at?->format('d/m/Y') ?? 'l\'Origine' }}
                        </p>
                    </div>
                </div>

                {{-- Jauge d'XP & Niveau Joueur --}}
                <div class="w-full lg:w-80 bg-slate-950/80 p-4 rounded-2xl border border-slate-800/80 space-y-2.5 shrink-0 shadow-lg">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="flex items-center gap-1.5 text-white">
                            <span class="text-base text-amber-400">⚡</span>
                            <span>Niveau {{ $levelProgress['level'] }}</span>
                        </span>
                        <span class="font-mono text-amber-300">{{ $levelProgress['current_level_xp'] }} / {{ $levelProgress['step_xp_required'] }} XP</span>
                    </div>

                    {{-- Barre de progression fluide --}}
                    <div class="w-full bg-slate-900 h-2.5 rounded-full overflow-hidden p-0.5 border border-slate-800">
                        <div 
                            class="bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-300 h-full rounded-full transition-all duration-700 shadow-[0_0_10px_rgba(251,191,36,0.6)]"
                            style="width: {{ $levelProgress['percentage'] }}%"
                        ></div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                        <span>Progression palier</span>
                        <span class="text-amber-400 font-bold">{{ $levelProgress['percentage'] }}%</span>
                    </div>
                </div>
            </div>

            {{-- HUD Stats Récapitulatives Rapides --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-800/60 text-xs">
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                    <span class="text-2xl">🪙</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Solde d'Or</span>
                        <span class="text-sm font-extrabold text-amber-300 font-mono">{{ number_format($user->coins, 0, ',', ' ') }} 🪙</span>
                    </div>
                </div>

                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                    <span class="text-2xl">🎴</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Collection</span>
                        <span class="text-sm font-extrabold text-white font-mono">{{ $heroesCount }} Héros</span>
                    </div>
                </div>

                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Puissance Max</span>
                        <span class="text-sm font-extrabold text-amber-400 font-mono">{{ $maxPowerScore }} pts</span>
                    </div>
                </div>

                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                    <span class="text-2xl">⚔️</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Taux de Victoire</span>
                        <span class="text-sm font-extrabold text-emerald-400 font-mono">{{ $winrate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 2. VITRINE DES FAVORIS (TOP 3 HÉROS PHARE)                               --}}
        {{-- ========================================================================= --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-white font-display flex items-center gap-2">
                        <span class="text-amber-400">⭐</span>
                        <span>Vitrine des Combattants Phares</span>
                        <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-full bg-slate-900 border border-slate-800 text-amber-300">
                            {{ $favoriteCharacters->count() }}/3
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Les 3 héros que vous choisissez d'exhiber en vitrine de votre profil. Cliquez sur une carte pour la retourner.
                    </p>
                </div>

                <a 
                    href="{{ route('collection') }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition shrink-0"
                >
                    <span>🎴</span>
                    <span>Gérer ma collection</span>
                </a>
            </div>

            {{-- Grille des 3 Cartes Favorites --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                @for ($slot = 0; $slot < 3; $slot++)
                    @php
                        $character = $favoriteCharacters[$slot] ?? null;
                    @endphp

                    @if ($character)
                        @php
                            $rarityKey = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : $character->rarity;
                            $borderStyles = match($rarityKey) {
                                'legendary' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-950 shadow-[0_0_25px_rgba(251,191,36,0.25)]',
                                'epic'      => 'border-purple-500/70 bg-gradient-to-b from-purple-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(192,132,252,0.2)]',
                                'rare'      => 'border-cyan-400/60 bg-gradient-to-b from-cyan-950/30 via-slate-900 to-slate-950 shadow-[0_0_20px_rgba(56,189,248,0.18)]',
                                default     => 'border-slate-800 bg-slate-900/90',
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

                        {{-- Carte Favorite avec 3D Flip au Clic --}}
                        <div
                            x-data="{ flipped: false }"
                            class="perspective-1000 w-full group select-none transition-transform duration-300 hover:-translate-y-1 mx-auto"
                            style="height: 440px; min-height: 440px; max-width: 290px;"
                        >
                            <div
                                class="relative w-full h-full preserve-3d card-flip-transition"
                                :class="{ 'rotate-y-180': flipped }"
                                style="height: 440px; min-height: 440px;"
                            >
                                {{-- RECTO --}}
                                <div
                                    @click="flipped = true"
                                    class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} overflow-hidden p-4 flex flex-col justify-between backface-hidden shadow-xl cursor-pointer"
                                    :class="{ 'pointer-events-none': flipped }"
                                    style="height: 440px; min-height: 440px;"
                                    title="Cliquer pour voir les détails"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider {{ $badgeStyle }}">
                                            {{ $character->rarity->label() }}
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-amber-300 text-xs bg-amber-950/80 px-2 py-0.5 rounded-full border border-amber-400/50 shadow font-bold">
                                                ⭐ Vitrine #{{ $slot + 1 }}
                                            </span>
                                            <div class="flex items-center gap-1 bg-slate-950/80 px-2.5 py-0.5 rounded-lg border border-slate-800">
                                                <span class="text-xs text-amber-400">⚡</span>
                                                <span class="font-extrabold text-sm text-white font-mono">{{ $character->power_score }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Portrait Paper Doll --}}
                                    <div class="flex-1 my-1 flex items-center justify-center">
                                        <x-character-avatar :character="$character" class="max-w-[200px]" />
                                    </div>

                                    <div class="text-center pt-2 border-t border-slate-800/80">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <span class="text-xs {{ $s <= $stars ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]' : 'text-slate-800' }}">★</span>
                                            @endfor
                                        </div>
                                        <h3 class="text-sm font-black text-white truncate tracking-tight">{{ $character->name }}</h3>
                                        <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                            {{ $character->race_data['name'] ?? ucfirst($character->race_id) }} • {{ $character->weapon_data['name'] ?? ucfirst($character->weapon_id) }}
                                            <span class="text-indigo-400 font-semibold">({{ $character->power_data['name'] ?? ucfirst($character->power_id) }})</span>
                                        </p>
                                        <div class="mt-2 text-[10px] text-indigo-300/80 font-mono tracking-wide flex items-center justify-center gap-1 opacity-70">
                                            <span>🔄</span>
                                            <span>Cliquer pour retourner</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- VERSO --}}
                                <div
                                    @click="if (!$event.target.closest('button, a')) flipped = false"
                                    class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} bg-slate-950/95 backdrop-blur-md overflow-hidden p-3.5 flex flex-col justify-between backface-hidden rotate-y-180 shadow-2xl cursor-pointer"
                                    :class="{ 'pointer-events-auto': flipped, 'pointer-events-none': !flipped }"
                                    style="height: 440px; min-height: 440px;"
                                    title="Cliquer pour revenir au recto"
                                >
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-800">
                                            <h4 class="text-xs font-black text-white truncate">{{ $character->name }}</h4>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button
                                                    type="button"
                                                    @click.stop="flipped = false"
                                                    class="p-1 rounded-lg text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition cursor-pointer"
                                                    title="Retourner la carte (voir le recto)"
                                                >
                                                    🔄
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click.stop="removeFavorite('{{ $character->id }}')"
                                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 transition cursor-pointer"
                                                    title="Retirer de la vitrine"
                                                >
                                                    ✕ Retirer
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Tri-Slot --}}
                                        <div class="grid grid-cols-3 gap-1 py-1 px-2 rounded-xl bg-slate-900 border border-slate-800/80 mb-2 text-center text-[11px]">
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

                                        {{-- Barres de stats --}}
                                        <div class="space-y-1 mb-2 bg-slate-900/60 p-2 rounded-xl border border-slate-800/50 text-[10px]">
                                            <div>
                                                <div class="flex justify-between mb-0.5">
                                                    <span class="text-rose-400 font-semibold">❤️ PV</span>
                                                    <span class="font-mono text-white">{{ $character->hp }}</span>
                                                </div>
                                                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                                                    <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($character->hp / 140) * 100) }}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-0.5">
                                                    <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                                    <span class="font-mono text-white">{{ $character->strength }}</span>
                                                </div>
                                                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($character->strength / 120) * 100) }}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-0.5">
                                                    <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                                                    <span class="font-mono text-white">{{ $character->speed }}</span>
                                                </div>
                                                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                                                    <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($character->speed / 100) * 100) }}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-0.5">
                                                    <span class="text-emerald-400 font-semibold">🛡️ Défense</span>
                                                    <span class="font-mono text-white">{{ $character->defense }}</span>
                                                </div>
                                                <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                                                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, ($character->defense / 100) * 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Synergies actives --}}
                                        @if (!empty($character->synergies_data))
                                            <div class="space-y-1 mb-2">
                                                @foreach ($character->synergies_data as $syn)
                                                    <div class="px-2 py-0.5 rounded text-[10px] font-bold border flex items-center justify-between {{ $syn['is_unstable'] ?? false ? 'bg-amber-950/60 border-amber-500/60 text-amber-200' : 'bg-indigo-950/60 border-indigo-500/60 text-indigo-200' }}">
                                                        <span>{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-2 border-t border-slate-800 space-y-1.5">
                                        <button
                                            type="button"
                                            wire:click.stop="inspectCharacter('{{ $character->id }}')"
                                            class="w-full py-1.5 px-3 rounded-xl bg-indigo-600/30 hover:bg-indigo-600/50 border border-indigo-500/50 text-indigo-200 hover:text-white text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                                        >
                                            <span>🔍</span>
                                            <span>Fiche Détaillée</span>
                                        </button>
                                        <div class="text-center">
                                            <span class="text-[10px] text-slate-500 font-mono">🏆 {{ $character->wins }}V - {{ $character->losses }}D ({{ $character->winrate }}%)</span>
                                        </div>
                                        <div class="text-center text-[10px] text-slate-500 font-mono flex items-center justify-center gap-1 opacity-70">
                                            <span>🔄</span>
                                            <span>Cliquer pour retourner</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Emplacement Vide Incitatif --}}
                        <div class="w-full rounded-3xl border-2 border-dashed border-slate-800/80 bg-slate-950/40 p-6 flex flex-col items-center justify-center text-center hover:border-amber-500/40 transition group cursor-pointer mx-auto" style="height: 440px; min-height: 440px; max-width: 290px;">
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:border-amber-400/50 transition">
                                <span class="opacity-50 group-hover:opacity-100">⭐</span>
                            </div>
                            <h4 class="text-sm font-black text-white">Emplacement Libre #{{ $slot + 1 }}</h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-[200px]">
                                Étoilez un héros dans votre collection pour l'exposer en vitrine.
                            </p>
                            <a
                                href="{{ route('collection') }}"
                                class="mt-5 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-300 border border-amber-500/30 font-bold text-xs transition"
                            >
                                Choisir un héros ➔
                            </a>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. STATISTIQUES DE CARRIÈRE COMPLÈTES (3 BLOCS ERGONOMIQUES)              --}}
        {{-- ========================================================================= --}}
        <div class="space-y-4">
            <h2 class="text-lg sm:text-xl font-black text-white font-display flex items-center gap-2 border-b border-slate-800/80 pb-3">
                <span class="text-indigo-400">📊</span>
                <span>Statistiques de Carrière</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Bloc 1 : Arène & Combats --}}
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-4 shadow-lg backdrop-blur-md">
                    <div class="flex items-center gap-2.5 text-rose-400 font-bold text-sm">
                        <span class="text-lg">⚔️</span>
                        <span>Performances Arène 1v1</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Duels disputés :</span>
                            <span class="font-bold text-white font-mono">{{ $battlesCount }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Victoires :</span>
                            <span class="font-bold text-emerald-400 font-mono">{{ $winsCount }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Défaites :</span>
                            <span class="font-bold text-rose-400 font-mono">{{ $lossesCount }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Matchs nuls :</span>
                            <span class="font-bold text-amber-300 font-mono">{{ $drawsCount }}</span>
                        </div>
                    </div>

                    {{-- Barre de Winrate --}}
                    <div class="pt-2">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Taux de Victoire :</span>
                            <span class="font-bold text-emerald-400 font-mono">{{ $winrate }}%</span>
                        </div>
                        <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden border border-slate-800">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full" style="width: {{ $winrate }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 2 : Collection & Tirages --}}
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-4 shadow-lg backdrop-blur-md">
                    <div class="flex items-center gap-2.5 text-cyan-400 font-bold text-sm">
                        <span class="text-lg">🎡</span>
                        <span>Collection & Roue</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Héros en réserve :</span>
                            <span class="font-bold text-white font-mono">{{ $heroesCount }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Cartes Légendaires :</span>
                            <span class="font-bold text-amber-400 font-mono">{{ $legendariesCount }} 👑</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Cartes Épiques :</span>
                            <span class="font-bold text-purple-400 font-mono">{{ $epicsCount }} 🔮</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Record de Puissance :</span>
                            <span class="font-bold text-cyan-300 font-mono">{{ $maxPowerScore }} pts</span>
                        </div>
                    </div>
                </div>

                {{-- Bloc 3 : Économie & Atelier --}}
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-4 shadow-lg backdrop-blur-md">
                    <div class="flex items-center gap-2.5 text-amber-400 font-bold text-sm">
                        <span class="text-lg">🏛️</span>
                        <span>Économie & Atelier</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Héros recyclés :</span>
                            <span class="font-bold text-amber-300 font-mono">{{ $recycledCount }} ♻️</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Aimant à Pouvoirs :</span>
                            <span class="font-bold text-white font-mono">Niv. {{ $workshopLevels['rare_magnet'] }}/5</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Forge des Héros :</span>
                            <span class="font-bold text-white font-mono">Niv. {{ $workshopLevels['hero_forge'] }}/5</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/50">
                            <span class="text-slate-400">Coffre de Guerre :</span>
                            <span class="font-bold text-white font-mono">Niv. {{ $workshopLevels['arena_bonus'] }}/3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 4. ARMOIRE À TROPHÉES & HAUTS FAITS (SUCCÈS)                              --}}
        {{-- ========================================================================= --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                <h2 class="text-lg sm:text-xl font-black text-white font-display flex items-center gap-2">
                    <span class="text-amber-400">🏆</span>
                    <span>Salle des Trophées & Hauts Faits</span>
                </h2>
                <span class="text-xs text-slate-400 font-mono">
                    {{ collect($achievementsList)->where('is_unlocked', true)->count() }} / {{ count($achievementsList) }} accomplis
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($achievementsList as $ach)
                    <div class="p-4 rounded-2xl border transition-all duration-300 {{ $ach['is_unlocked'] ? 'bg-gradient-to-br from-amber-950/20 via-slate-900 to-slate-950 border-amber-400/40 shadow-lg shadow-amber-500/10' : 'bg-slate-950/60 border-slate-800 opacity-60' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0 border {{ $ach['is_unlocked'] ? 'bg-amber-400/10 border-amber-400/40' : 'bg-slate-900 border-slate-800 grayscale' }}">
                                <span>{{ $ach['icon'] }}</span>
                            </div>

                            <div class="space-y-1 min-w-0">
                                <h4 class="text-xs font-black truncate {{ $ach['is_unlocked'] ? 'text-amber-300' : 'text-slate-400' }}">
                                    {{ $ach['name'] }}
                                </h4>
                                <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ $ach['description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[10px]">
                            @if ($ach['is_unlocked'])
                                <span class="text-emerald-400 font-bold flex items-center gap-1">
                                    <span>✅</span>
                                    <span>{{ $ach['unlocked_at'] }}</span>
                                </span>
                            @else
                                <span class="text-slate-500 font-semibold flex items-center gap-1">
                                    <span>🔒</span>
                                    <span>Verrouillé</span>
                                </span>
                            @endif

                            <span class="text-amber-400 font-mono font-bold">
                                +{{ $ach['reward'] }} 🪙
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    @else
        {{-- ========================================================================= --}}
        {{-- ONGLET 2 : PARAMÈTRES DU COMPTE (SÉCURITÉ & IDENTIFIANTS)                 --}}
        {{-- ========================================================================= --}}
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl backdrop-blur-md">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl backdrop-blur-md">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-rose-500/20 shadow-xl backdrop-blur-md">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    @endif

    {{-- MODALE OFFICIELLE ET COMPLÈTE D'INSPECTION DE CARTE --}}
    <x-character-modal :character="$inspectedCharacter" />
</div>

