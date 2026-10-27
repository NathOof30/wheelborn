<div class="space-y-8 pb-16">

    {{-- ========================================================================= --}}
    {{-- 1. BANNIÈRE JOUEUR NOBLE (HERO HEADER EN CARTEL MAJESTUEUX)                --}}
    {{-- ========================================================================= --}}
    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900/90 via-slate-950/90 to-indigo-950/40 p-6 sm:p-8 backdrop-blur-md shadow-2xl">
        {{-- Aura céleste d'arrière-plan --}}
        <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full blur-3xl pointer-events-none opacity-20" style="background-color: {{ $league->color() }};"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full blur-3xl pointer-events-none opacity-10 bg-indigo-500"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- Identité Joueur & Blason de Ligue --}}
            <div class="flex items-center gap-5">
                {{-- Blason de Ligue animé --}}
                <div class="relative shrink-0">
                    <div 
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl flex flex-col items-center justify-center border-2 shadow-xl bg-slate-950/80 transition-transform duration-500 hover:scale-105"
                        style="border-color: {{ $league->color() }}; box-shadow: 0 0 25px {{ $league->color() }}40;"
                    >
                        <span class="text-3xl sm:text-4xl filter drop-shadow">{{ $league->icon() }}</span>
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
            <div class="w-full md:w-80 bg-slate-950/80 p-4 rounded-2xl border border-slate-800/80 space-y-2.5 shrink-0 shadow-lg">
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
                <span class="text-2xl">🏆</span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Classement Élo</span>
                    <span class="text-sm font-extrabold text-amber-300 font-mono">{{ $user->elo ?? 1000 }}</span>
                </div>
            </div>

            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                <span class="text-2xl">🎴</span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Collection</span>
                    <span class="text-sm font-extrabold text-white font-mono">{{ $collectionCount }} héros</span>
                </div>
            </div>

            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                <span class="text-2xl">⚔️</span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Duels disputés</span>
                    <span class="text-sm font-extrabold text-rose-300 font-mono">{{ $battlesCount }}</span>
                </div>
            </div>

            <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800 flex items-center gap-3">
                <span class="text-2xl">🎯</span>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Taux de victoire</span>
                    <span class="text-sm font-extrabold text-emerald-400 font-mono">{{ $winrate }}%</span>
                </div>
            </div>
        </div>

        {{-- Liens de navigation contextuels --}}
        <div class="mt-4 pt-4 border-t border-slate-800/40 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a 
                    href="{{ route('leaderboard') }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition"
                >
                    <span>⬅️</span>
                    <span>Retour au Classement</span>
                </a>
            </div>

            @if ($isOwnProfile)
                <a 
                    href="{{ route('profile.edit') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition shadow-lg shadow-amber-500/20"
                >
                    <span>⚙️</span>
                    <span>Gérer mon Profil & Paramètres</span>
                </a>
            @else
                <a 
                    href="{{ route('arena') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black transition shadow-lg shadow-rose-600/20"
                >
                    <span>⚔️</span>
                    <span>Rejoindre l'Arène</span>
                </a>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. VITRINE DES 3 HÉROS FAVORIS (TCG CARTES PHYSIQUES 3D FLIP)             --}}
    {{-- ========================================================================= --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">⭐</span>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-white font-display">
                        Vitrine des Héros Phares
                    </h2>
                    <p class="text-xs text-slate-400">
                        Les 3 champions d'exception sélectionnés par {{ $user->name }}.
                    </p>
                </div>
            </div>
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
                                        <span class="text-xs text-amber-400 font-bold">
                                            {{ str_repeat('★', match($rarityKey) { 'legendary' => 5, 'epic' => 4, 'rare' => 3, default => 2 }) }}
                                        </span>
                                    </div>
                                    <h3 class="font-extrabold text-base text-white truncate font-display">
                                        {{ $character->name }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5 font-medium">
                                        {{ $character->race_label }} · {{ $character->weapon_label }}
                                    </p>
                                    <p class="text-[11px] text-amber-400/90 font-medium mt-0.5">
                                        {{ $character->power_label }}
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
                                class="absolute inset-0 w-full h-full rounded-3xl border border-indigo-500/50 bg-slate-950/95 overflow-hidden p-4 flex flex-col justify-between backface-hidden rotate-y-180 shadow-2xl cursor-pointer"
                                :class="{ 'pointer-events-auto': flipped, 'pointer-events-none': !flipped }"
                                style="height: 440px; min-height: 440px;"
                                title="Cliquer pour revenir au recto"
                            >
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <span class="text-xs font-bold text-amber-300 font-display flex items-center gap-1">
                                        <span>📜</span>
                                        <span>Fiche du Héros</span>
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click.stop="flipped = false"
                                            class="p-1 rounded-lg text-xs bg-slate-900 text-slate-400 hover:text-white border border-slate-800 transition cursor-pointer"
                                            title="Retourner la carte"
                                        >
                                            🔄
                                        </button>
                                        <span class="text-[10px] text-indigo-400 bg-indigo-950/80 px-2 py-0.5 rounded-full border border-indigo-800">
                                            Vitrine
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-3 my-auto">
                                    <div class="space-y-1.5 text-xs">
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-rose-400 font-semibold">❤️ Points de Vie</span>
                                                <span class="font-mono text-white">{{ $character->hp }} / {{ $character->max_hp }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($character->hp / $character->max_hp) * 100) }}%"></div>
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
                    {{-- Emplacement Vide --}}
                    <div class="w-full rounded-3xl border-2 border-dashed border-slate-800/80 bg-slate-950/40 p-6 flex flex-col items-center justify-center text-center mx-auto" style="height: 440px; min-height: 440px; max-width: 290px;">
                        <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-2xl mb-4">
                            <span class="opacity-30">⭐</span>
                        </div>
                        <h4 class="text-sm font-black text-slate-400">Emplacement Libre #{{ $slot + 1 }}</h4>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px]">
                            Aucun champion exposé sur cet emplacement par le joueur.
                        </p>
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
                        <span class="text-emerald-400">Victoires :</span>
                        <span class="font-bold text-emerald-400 font-mono">{{ $winsCount }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-rose-400">Défaites :</span>
                        <span class="font-bold text-rose-400 font-mono">{{ $lossesCount }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-slate-400">Matchs nuls :</span>
                        <span class="font-bold text-slate-300 font-mono">{{ $drawsCount }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-300 font-semibold">Taux de victoire :</span>
                        <span class="font-black text-amber-300 font-mono">{{ $winrate }}%</span>
                    </div>
                </div>
            </div>

            {{-- Bloc 2 : Collection & Héros --}}
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-4 shadow-lg backdrop-blur-md">
                <div class="flex items-center gap-2.5 text-cyan-400 font-bold text-sm">
                    <span class="text-lg">🎴</span>
                    <span>Collection & Destin</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-slate-400">Héros en caserne :</span>
                        <span class="font-bold text-white font-mono">{{ $collectionCount }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-amber-400">Légendaires possédés :</span>
                        <span class="font-bold text-amber-300 font-mono">{{ $legendariesCount }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-indigo-400">Synergies découvertes :</span>
                        <span class="font-bold text-indigo-300 font-mono">{{ $synergiesCount }} / 16</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-300 font-semibold">Ligue atteinte :</span>
                        <span class="font-bold text-white font-mono" style="color: {{ $league->color() }}">{{ $league->icon() }} {{ $league->name }}</span>
                    </div>
                </div>
            </div>

            {{-- Bloc 3 : Économie & Forge --}}
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-4 shadow-lg backdrop-blur-md">
                <div class="flex items-center gap-2.5 text-amber-400 font-bold text-sm">
                    <span class="text-lg">⚒️</span>
                    <span>Économie & Forge</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-slate-400">Cartes recyclées :</span>
                        <span class="font-bold text-white font-mono">{{ $recycledCount }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/50">
                        <span class="text-slate-400">Niveau d'Atelier :</span>
                        <span class="font-bold text-amber-300 font-mono">Niveau {{ $workshopLevel }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-300 font-semibold">Rang Élo :</span>
                        <span class="font-bold text-white font-mono">{{ $user->elo ?? 1000 }} Élo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. SALLE DES TROPHÉES & SUCCÈS DÉBLOQUÉS                                    --}}
    {{-- ========================================================================= --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
            <h2 class="text-lg sm:text-xl font-black text-white font-display flex items-center gap-2">
                <span class="text-amber-400">🏆</span>
                <span>Salle des Trophées</span>
            </h2>
            <span class="text-xs font-mono text-slate-400">
                {{ collect($achievementsList)->where('unlocked', true)->count() }} / {{ count($achievementsList) }} accomplis
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($achievementsList as $ach)
                <div class="p-4 rounded-2xl border transition-all duration-300 {{ $ach['unlocked'] ? 'bg-gradient-to-br from-amber-950/30 via-slate-900 to-slate-950 border-amber-500/40 shadow-[0_0_15px_rgba(251,191,36,0.15)]' : 'bg-slate-950/60 border-slate-800 opacity-60' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0 border {{ $ach['unlocked'] ? 'bg-amber-500/20 border-amber-400/50 text-amber-300 shadow-inner' : 'bg-slate-900 border-slate-800 grayscale' }}">
                            <span>{{ $ach['icon'] }}</span>
                        </div>

                        <div class="space-y-1 min-w-0">
                            <h4 class="text-xs font-black truncate {{ $ach['unlocked'] ? 'text-white' : 'text-slate-400' }}">
                                {{ $ach['title'] }}
                            </h4>
                            <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $ach['description'] }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[10px]">
                        @if ($ach['unlocked'])
                            <span class="text-emerald-400 font-semibold flex items-center gap-1">
                                <span>✅</span>
                                <span>Débloqué le {{ $ach['unlocked_at'] }}</span>
                            </span>
                        @else
                            <span class="text-slate-500 font-semibold flex items-center gap-1">
                                <span>🔒</span>
                                <span>Verrouillé</span>
                            </span>
                        @endif
                        <span class="text-amber-400 font-mono font-bold">
                            +{{ $ach['reward_coins'] }} 🪙
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODALE OFFICIELLE ET COMPLÈTE D'INSPECTION DE CARTE                       --}}
    {{-- ========================================================================= --}}
    <x-character-modal :character="$inspectedCharacter" :readOnly="true" />

</div>

