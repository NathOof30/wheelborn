<div class="space-y-6" @if(!$isAdmin && $spinCharges < $maxCharges) wire:poll.10s="refreshState" @endif>
    {{-- Toast Notification --}}
    @if ($notification)
        <div class="fixed top-20 right-6 z-50 max-w-md p-4 rounded-xl shadow-2xl border backdrop-blur-md transition-all duration-300 {{ $notificationType === 'error' ? 'bg-rose-950/90 border-rose-600 text-rose-200' : ($notificationType === 'success' ? 'bg-emerald-950/90 border-emerald-500 text-emerald-200' : 'bg-slate-900/90 border-slate-700 text-slate-200') }}">
            <div class="flex items-center justify-between gap-3">
                <span class="font-medium text-sm">{{ $notification }}</span>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        </div>
    @endif

    {{-- Admin Dev Toolbar if Admin --}}
    @if ($isAdmin)
        <div class="bg-gradient-to-r from-purple-950/80 via-slate-900 to-indigo-950/80 border border-purple-500/40 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 backdrop-blur-md shadow-xl">
            <div class="flex items-center gap-3">
                <span class="text-2xl filter drop-shadow">👑</span>
                <div>
                    <h3 class="font-extrabold text-sm text-purple-300 flex items-center gap-2">
                        <span>Mode Administrateur & Développeur</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30">ACTIF</span>
                    </h3>
                    <p class="text-xs text-slate-400">Tirages illimités • Pièces infinies • Contrôles de tests rapides</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="adminAddCoins(50000)" class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95">
                    +50 000 🪙
                </button>
                <button wire:click="adminTriggerSuperSpin" class="px-3 py-1.5 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 border border-yellow-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95">
                    🌟 Forcer Super Spin (10/10)
                </button>
                <button wire:click="adminResetPity" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-lg text-xs font-bold transition">
                    Reset Pity
                </button>
                <button wire:click="adminForceTestSpin('sword')" class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 flex items-center gap-1" title="Tester le Paper Doll avec Humain + Épée">
                    <span>⚔️</span>
                    <span>Test Épée</span>
                </button>
                <button wire:click="adminForceTestSpin('staff')" class="px-3 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 border border-cyan-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 flex items-center gap-1" title="Tester le Paper Doll avec Humain + Bâton">
                    <span>🪄</span>
                    <span>Test Bâton</span>
                </button>
                <button wire:click="adminForceTestSpin('bow')" class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 flex items-center gap-1" title="Tester le Paper Doll avec Humain + Arc">
                    <span>🏹</span>
                    <span>Test Arc</span>
                </button>
                <button wire:click="adminForceTestSpin('dagger')" class="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 flex items-center gap-1" title="Tester le Paper Doll avec Humain + Dague">
                    <span>🗡️</span>
                    <span>Test Dague</span>
                </button>
                <button wire:click="adminForceTestSpin('hammer')" class="px-3 py-1.5 bg-orange-500/20 hover:bg-orange-500/30 text-orange-300 border border-orange-500/40 rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 flex items-center gap-1" title="Tester le Paper Doll avec Humain + Marteau">
                    <span>🔨</span>
                    <span>Test Marteau</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Main Wheel Section --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl relative overflow-hidden backdrop-blur-md">
        {{-- Ambient background glows --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-4xl mx-auto">
            {{-- Header épuré : titre noble uniquement --}}
            <div class="text-center mb-6">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight heading-majestic">
                    La Roue du Destin
                </h1>
            </div>

            {{-- HUD Stats : Capsules compactes et élégantes --}}
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-8">
                {{-- Capsule 1 : Charges de Spin --}}
                <div class="bg-slate-950/80 border border-slate-800/80 rounded-full px-4 py-2 flex items-center gap-2.5 backdrop-blur-md shadow-md">
                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Charges :</span>
                    <div class="flex items-center gap-1">
                        @if ($isAdmin)
                            <span class="text-amber-400 font-black text-sm">⚡ ∞</span>
                        @else
                            @for ($i = 1; $i <= $maxCharges; $i++)
                                <span class="text-base transition-all duration-300 {{ $i <= $spinCharges ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)] scale-110' : 'grayscale opacity-25 filter' }}">
                                    ⚡
                                </span>
                            @endfor
                            <span class="text-xs font-mono text-slate-400 ml-1">({{ $spinCharges }}/{{ $maxCharges }})</span>
                        @endif
                    </div>

                    {{-- Timer de recharge dynamique --}}
                    @if (!$isAdmin && $spinCharges < $maxCharges)
                        <div
                            wire:key="spin-timer-{{ $spinCharges }}-{{ $nextRegenSeconds }}"
                            x-data="{
                                remaining: {{ (int) $nextRegenSeconds }},
                                formatTime() {
                                    let s = Math.max(0, parseInt(this.remaining, 10) || 0);
                                    let m = Math.floor(s / 60);
                                    let sec = s % 60;
                                    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
                                },
                                init() {
                                    let interval = setInterval(() => {
                                        if (this.remaining > 0) {
                                            this.remaining--;
                                        } else {
                                            clearInterval(interval);
                                            $wire.call('refreshState');
                                        }
                                    }, 1000);
                                }
                            }"
                            class="text-[11px] font-mono text-cyan-400 bg-cyan-950/60 px-2 py-0.5 rounded-full border border-cyan-800/40 ml-1"
                        >
                            <span>+1 dans <strong x-text="formatTime()" class="text-cyan-200">{{ sprintf('%02d:%02d', floor($nextRegenSeconds / 60), $nextRegenSeconds % 60) }}</strong></span>
                        </div>
                    @endif
                </div>

                {{-- Capsule 2 : Jauge de Chance (Pity) --}}
                <div class="bg-slate-950/80 border border-slate-800/80 rounded-full px-4 py-2 flex items-center gap-2.5 backdrop-blur-md shadow-md">
                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Chance :</span>
                    <div class="w-20 bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-yellow-300 h-full transition-all duration-500 rounded-full {{ $pityCounter >= $pityMax ? 'animate-pulse' : '' }}"
                             style="width: {{ min(100, ($pityCounter / $pityMax) * 100) }}%"></div>
                    </div>
                    <span class="text-xs font-mono font-bold text-amber-400">{{ $pityCounter }}/{{ $pityMax }}</span>
                    @if ($pityCounter >= $pityMax)
                        <span class="text-[10px] uppercase font-black tracking-widest text-amber-300 animate-pulse bg-amber-500/20 px-2 py-0.5 rounded-full border border-amber-400/40">
                            ✨ SUPER SPIN
                        </span>
                    @endif
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- CAS 1 : CARTE PHYSIQUE RÉVÉLÉE (REPRISE EXACTE DE LA COLLECTION AVEC FLIP) --}}
            {{-- ========================================================================= --}}
            @if ($revealedCharacter && !$isSpinning)
                @php
                    $rarityKey = $revealedCharacter['rarity'] instanceof \App\Enums\Rarity ? $revealedCharacter['rarity']->value : $revealedCharacter['rarity'];
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
                    $raceData = \App\Data\RaceData::find($revealedCharacter['race_id']);
                    $weaponData = \App\Data\WeaponData::find($revealedCharacter['weapon_id']);
                    $powerData = \App\Data\PowerData::find($revealedCharacter['power_id']);
                @endphp

                <div class="space-y-6">
                    <div class="text-center">
                        <span class="inline-block px-3 py-1 bg-amber-500/15 border border-amber-500/30 rounded-full text-xs font-bold text-amber-300 uppercase tracking-widest animate-pulse">
                            ⚠️ Finalisez le tirage ci-dessous
                        </span>
                    </div>

                    {{-- Composant Carte Physique avec 3D Flip au Clic --}}
                    <div
                        x-data="{ flipped: false }"
                        class="perspective-1000 w-full max-w-[290px] mx-auto group select-none transition-transform duration-300 hover:-translate-y-1"
                        style="height: 440px; min-height: 440px;"
                    >
                        <div
                            class="relative w-full h-full preserve-3d card-flip-transition"
                            :class="{ 'rotate-y-180': flipped }"
                            style="height: 440px; min-height: 440px;"
                        >
                            {{-- ========================================================================= --}}
                            {{-- FACE RECTO (CLIC POUR FLIP VERS LE VERSO)                                  --}}
                            {{-- ========================================================================= --}}
                            <div
                                @click="flipped = true"
                                class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} overflow-hidden p-4 flex flex-col justify-between backface-hidden shadow-xl cursor-pointer"
                                :class="{ 'pointer-events-none': flipped }"
                                style="height: 440px; min-height: 440px;"
                                title="Cliquer pour retourner la carte"
                            >
                                {{-- En-tête Recto --}}
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider {{ $badgeStyle }}">
                                        ★ {{ ucfirst($rarityKey) }}
                                    </span>

                                    <div class="flex items-center gap-1 bg-slate-950/80 px-2.5 py-0.5 rounded-lg border border-slate-800">
                                        <span class="text-xs text-amber-400">⚡</span>
                                        <span class="font-extrabold text-sm text-white font-mono">{{ $revealedCharacter['power_score'] }}</span>
                                    </div>
                                </div>

                                {{-- Scène Visuelle : Avatar Composite Paper Doll --}}
                                <div class="flex-1 my-1.5 flex items-center justify-center">
                                    <x-character-avatar :character="$revealedCharacter" class="max-w-[210px]" />
                                </div>

                                {{-- Pied de carte Recto : Titre & Décoration --}}
                                <div class="text-center pt-2 border-t border-slate-800/80">
                                    {{-- Étoiles de Rareté --}}
                                    <div class="flex items-center justify-center gap-1 mb-1">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span class="text-xs {{ $s <= $stars ? 'text-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.6)]' : 'text-slate-800' }}">
                                                ★
                                            </span>
                                        @endfor
                                    </div>
                                    <h3 class="text-sm font-black text-white truncate tracking-tight group-hover:text-amber-300 transition-colors">
                                        {{ $revealedCharacter['name'] }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5 font-medium">
                                        {{ $raceData['name'] ?? ucfirst($revealedCharacter['race_id']) }} • {{ $weaponData['name'] ?? ucfirst($revealedCharacter['weapon_id']) }}
                                        <span class="text-indigo-400 font-semibold">({{ $powerData['name'] ?? ucfirst($revealedCharacter['power_id']) }})</span>
                                    </p>
                                    <span class="text-[10px] text-slate-500 font-mono mt-1 block">
                                        🔄 Cliquer pour retourner
                                    </span>
                                </div>
                            </div>

                            {{-- ========================================================================= --}}
                            {{-- FACE VERSO (RÉVÉLÉE AU CLIC - STATISTIQUES & SYNERGIES)                    --}}
                            {{-- ========================================================================= --}}
                            <div
                                @click="if (!$event.target.closest('button, a')) flipped = false"
                                class="absolute inset-0 w-full h-full rounded-3xl border {{ $borderStyles }} bg-slate-950/95 backdrop-blur-md overflow-hidden p-4 flex flex-col justify-between backface-hidden rotate-y-180 shadow-2xl cursor-pointer"
                                :class="{ 'pointer-events-auto': flipped, 'pointer-events-none': !flipped }"
                                style="height: 440px; min-height: 440px;"
                                title="Cliquer pour revenir à la face avant"
                            >
                                <div>
                                    {{-- En-tête Verso : Nom, Rareté & Bouton Retourner --}}
                                    <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-800">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="px-2 py-0.5 rounded text-[9px] uppercase tracking-wider {{ $badgeStyle }} shrink-0">
                                                {{ ucfirst($rarityKey) }}
                                            </span>
                                            <h4 class="text-xs font-black text-white truncate" title="{{ $revealedCharacter['name'] }}">
                                                {{ $revealedCharacter['name'] }}
                                            </h4>
                                        </div>
                                        <span class="text-xs text-slate-400 p-1">🔄</span>
                                    </div>

                                    {{-- Tri-Slot Compact --}}
                                    <div class="grid grid-cols-3 gap-1 py-1 px-2 rounded-xl bg-slate-900 border border-slate-800/80 mb-2.5 text-center text-[11px]">
                                        <div>
                                            <span class="text-sm block">{{ $raceData['icon'] ?? '👤' }}</span>
                                            <span class="text-[9px] text-slate-400 truncate block">{{ $raceData['name'] ?? ucfirst($revealedCharacter['race_id']) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm block">{{ $weaponData['icon'] ?? '⚔️' }}</span>
                                            <span class="text-[9px] text-slate-400 truncate block">{{ $weaponData['name'] ?? ucfirst($revealedCharacter['weapon_id']) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm block">{{ $powerData['icon'] ?? '🔮' }}</span>
                                            <span class="text-[9px] text-indigo-300 truncate block">{{ $powerData['name'] ?? ucfirst($revealedCharacter['power_id']) }}</span>
                                        </div>
                                    </div>

                                    {{-- Barres de stats techniques --}}
                                    <div class="space-y-1 mb-2.5 bg-slate-900/60 p-2 rounded-xl border border-slate-800/50 text-[10px]">
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-rose-400 font-semibold">❤️ Points de Vie</span>
                                                <span class="font-mono text-white">{{ $revealedCharacter['hp'] }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, ($revealedCharacter['hp'] / 140) * 100) }}%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-amber-400 font-semibold">⚔️ Force</span>
                                                <span class="font-mono text-white">{{ $revealedCharacter['strength'] }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, ($revealedCharacter['strength'] / 100) * 100) }}%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                                                <span class="font-mono text-white">{{ $revealedCharacter['speed'] }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-cyan-500 h-full rounded-full" style="width: {{ min(100, ($revealedCharacter['speed'] / 100) * 100) }}%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between mb-0.5">
                                                <span class="text-emerald-400 font-semibold">🛡️ Défense</span>
                                                <span class="font-mono text-white">{{ $revealedCharacter['defense'] }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, ($revealedCharacter['defense'] / 100) * 100) }}%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Synergies actives --}}
                                    @if (!empty($revealedCharacter['synergies_data']))
                                        <div class="space-y-1">
                                            @foreach ($revealedCharacter['synergies_data'] as $syn)
                                                <div class="px-2 py-0.5 rounded text-[10px] font-bold border flex items-center justify-between {{ !empty($syn['is_unstable']) ? 'bg-amber-950/60 border-amber-600/70 text-amber-200' : 'bg-indigo-950/50 border-indigo-500/50 text-indigo-200' }}">
                                                    <span>{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                                    @if (!empty($syn['is_unstable']))
                                                        <span class="text-[9px] text-amber-400 font-mono">Instable</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="px-2 py-1 rounded bg-slate-900/50 border border-slate-800/40 text-center">
                                            <span class="text-[10px] text-slate-500 italic">Aucune synergie active</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-center pt-1 border-t border-slate-800/60">
                                    <span class="text-[10px] text-slate-500 font-mono">
                                        🔄 Cliquer pour retourner la carte
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions : Garder ou Recycler (aligné sur les 290px de la carte physique) --}}
                    <div class="flex items-center justify-center gap-2 pt-4 mx-auto" style="max-width: 290px; width: 100%;">
                        <button
                            wire:click="keepCharacter"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2 px-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all duration-200 text-center disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5 cursor-pointer transform hover:-translate-y-0.5 whitespace-nowrap"
                        >
                            <span wire:loading.remove wire:target="keepCharacter">✅ Garder</span>
                            <span wire:loading wire:target="keepCharacter" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Ajout...</span>
                            </span>
                        </button>
                        <button
                            wire:click="recycleCharacter"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2 px-3 bg-slate-900/90 hover:bg-slate-800 text-amber-300 border border-amber-500/30 font-bold text-xs rounded-xl shadow-md transition-all duration-200 text-center disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5 cursor-pointer transform hover:-translate-y-0.5 whitespace-nowrap"
                        >
                            <span wire:loading.remove wire:target="recycleCharacter">♻️ Recycler (+{{ $revealedCharacter['recycle_value'] }} 🪙)</span>
                            <span wire:loading wire:target="recycleCharacter" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-amber-300 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Recyclage...</span>
                            </span>
                        </button>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- ========================================================================= --}}
            {{-- CAS 2 : LE RITUEL D'INVOCATION (LES 3 ROUES EN ROTATION DÉCALÉE)          --}}
            {{-- ========================================================================= --}}
            @elseif ($isSpinning)
                <div
                    x-data="{
                        init() {
                            // Sécurité absolue : déclenchement garanti du finishSpin après la fin de la dernière roue
                            setTimeout(() => {
                                $wire.call('finishSpin');
                            }, {{ (int) ($maxDuration ?? 5400) }} + 350);
                        }
                    }"
                    class="py-4 space-y-6"
                >
                    <div class="text-center">
                        <span class="text-xs uppercase font-extrabold tracking-widest text-amber-400 animate-pulse bg-amber-500/10 px-4 py-1.5 rounded-full border border-amber-500/30">
                            ✨ Les roues tournent... Les destins fusionnent ✨
                        </span>
                    </div>

                    {{-- Triptyque des 3 roues : Race, Arme, Pouvoir --}}
                    <div class="grid grid-cols-3 gap-3 sm:gap-6 max-w-2xl mx-auto">
                        @foreach ($this->wheels as $key => $wheel)
                            <div wire:key="spinning-wheel-col-{{ $key }}" class="flex flex-col items-center gap-2">
                                <span class="text-[10px] sm:text-xs uppercase font-bold tracking-widest text-slate-400">
                                    {{ $wheel['label'] }}
                                </span>

                                <div class="relative w-full max-w-[180px] aspect-square">
                                    {{-- Pointeur fixe --}}
                                    <div class="absolute left-1/2 -top-1 -translate-x-1/2 z-20 w-0 h-0 border-l-[9px] border-r-[9px] border-t-[16px] border-l-transparent border-r-transparent border-t-amber-400 drop-shadow-[0_0_6px_rgba(251,191,36,0.7)]"></div>

                                    {{-- Disque avec animation garantie via Alpine : démarre à l'angle initial puis anime vers l'angle cible --}}
                                    <div
                                        wire:key="spinning-wheel-disc-{{ $key }}-{{ $wheel['rotation'] }}"
                                        x-data="{
                                            angle: {{ (float) ($previousRotations[$key] ?? 0) }},
                                            target: {{ (float) $wheel['rotation'] }},
                                            init() {
                                                setTimeout(() => {
                                                    this.angle = this.target;
                                                }, 40);
                                            }
                                        }"
                                        class="absolute inset-0 rounded-full border-4 border-slate-700 overflow-hidden shadow-[0_0_25px_rgba(99,102,241,0.25)]"
                                        :style="'background: {{ $wheel['gradient'] }}; transform: rotate(' + angle + 'deg); transition: transform {{ $wheel['duration'] }}ms cubic-bezier(0.12, 0.8, 0.1, 1);'"
                                        @if ($key === $lastWheel) x-on:transitionend.self="$wire.finishSpin()" @endif
                                    >
                                        @foreach ($wheel['segments'] as $segment)
                                            <div class="absolute inset-0 flex justify-center pt-[7%]" style="transform: rotate({{ $segment['angle'] }}deg);">
                                                <span class="{{ $segment['small'] ? 'text-[10px] sm:text-sm' : 'text-base sm:text-2xl' }} leading-none select-none" title="{{ $segment['name'] }} ({{ $segment['chance'] }} %)">
                                                    {{ $segment['icon'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Moyeu central --}}
                                    <div class="absolute inset-[40%] z-10 rounded-full bg-slate-950 border-2 border-slate-600"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- CAS 3 : LE CADRAN CÉLESTE EN VEILLE (ROUE SLOW-SPIN + BOUTON COEUR)       --}}
            {{-- ========================================================================= --}}
            @else
                <div
                    wire:key="idle-wheel-container"
                    x-data="{
                        activeTab: 0,
                        timer: null,
                        pauseTimeout: null,
                        isPaused: false,
                        startAutoCycle() {
                            this.stopAutoCycle();
                            this.timer = setInterval(() => {
                                if (!this.isPaused) {
                                    this.activeTab = (this.activeTab + 1) % 3;
                                }
                            }, 2500);
                        },
                        stopAutoCycle() {
                            if (this.timer) {
                                clearInterval(this.timer);
                                this.timer = null;
                            }
                        },
                        selectTab(index) {
                            this.activeTab = index;
                            // Pause l'auto-rotation pendant 8 secondes pour laisser tout le temps de lire
                            this.isPaused = true;
                            if (this.pauseTimeout) clearTimeout(this.pauseTimeout);
                            this.pauseTimeout = setTimeout(() => {
                                this.isPaused = false;
                                this.pauseTimeout = null;
                            }, 8000);
                            this.startAutoCycle();
                        },
                        init() {
                            this.startAutoCycle();
                        },
                        destroy() {
                            this.stopAutoCycle();
                            if (this.pauseTimeout) clearTimeout(this.pauseTimeout);
                        }
                    }"
                    class="relative flex flex-col items-center justify-center py-2"
                >
                    {{-- Pointeur fixe doré en tête de roue --}}
                    <div class="w-0 h-0 border-l-[10px] border-r-[10px] border-t-[18px] border-l-transparent border-r-transparent border-t-amber-400 drop-shadow-[0_0_8px_rgba(251,191,36,0.8)] mx-auto -mb-2 z-30 relative"></div>

                    {{-- Roue majestueuse centrale (se met en pause au survol) --}}
                    <div
                        @mouseenter="isPaused = true"
                        @mouseleave="if (!pauseTimeout) isPaused = false"
                        class="relative w-[300px] h-[300px] sm:w-[370px] sm:h-[370px] rounded-full border-4 border-slate-700/80 shadow-[0_0_45px_rgba(99,102,241,0.22)] flex items-center justify-center overflow-hidden"
                    >
                        {{-- Couche rotative continue (spin-slow 22s) --}}
                        <div class="absolute inset-0 rounded-full animate-spin-slow">
                            @foreach (['race', 'weapon', 'power'] as $index => $key)
                                @php $w = $this->wheels[$key]; @endphp
                                <div
                                    class="absolute inset-0 rounded-full transition-opacity duration-700 ease-in-out"
                                    :class="activeTab === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
                                    style="background: {{ $w['gradient'] }};"
                                >
                                    @foreach ($w['segments'] as $segment)
                                        <div class="absolute inset-0 flex justify-center pt-[6%]" style="transform: rotate({{ $segment['angle'] }}deg);">
                                            <span class="{{ $segment['small'] ? 'text-xs sm:text-base' : 'text-lg sm:text-2xl' }} leading-none select-none drop-shadow" title="{{ $segment['name'] }} ({{ $segment['chance'] }} %)">
                                                {{ $segment['icon'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        {{-- Moyeu central fixe (ne tourne pas) contenant le Bouton d'Invocation --}}
                        <div class="absolute inset-[24%] z-20 rounded-full bg-slate-950/95 border-2 border-slate-700 shadow-[0_0_35px_rgba(0,0,0,0.95)] flex flex-col items-center justify-center p-3 text-center backdrop-blur-md">
                            <button
                                wire:click="spin"
                                wire:loading.attr="disabled"
                                @disabled((!$isAdmin && $spinCharges <= 0) || !empty($revealedCharacter) || $isSpinning)
                                class="group w-full h-full rounded-full flex flex-col items-center justify-center transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer {{ $pityCounter >= $pityMax ? 'bg-gradient-to-br from-amber-400 via-yellow-400 to-amber-600 text-slate-950 animate-neon-pulse shadow-[0_0_20px_rgba(251,191,36,0.6)]' : 'bg-gradient-to-br from-indigo-600 via-indigo-700 to-cyan-600 text-white hover:from-indigo-500 hover:to-cyan-500 shadow-[0_0_20px_rgba(99,102,241,0.4)]' }}"
                                title="Lancer la Roue"
                            >
                                <span wire:loading.remove wire:target="spin" class="flex flex-col items-center">
                                    <span class="text-3xl sm:text-4xl mb-1 group-hover:scale-110 transition-transform">🎡</span>
                                    <span class="font-black text-xs sm:text-sm uppercase tracking-wider">
                                        @if ($pityCounter >= $pityMax)
                                            🌟 Super Spin !
                                        @else
                                            Lancer la Roue
                                        @endif
                                    </span>
                                    <span class="text-[10px] font-semibold opacity-85 font-mono mt-0.5">
                                        @if ($isAdmin)
                                            ∞ Illimité
                                        @elseif ($pityCounter >= $pityMax)
                                            Rare+ Garanti
                                        @elseif ($spinCharges > 0)
                                            1 charge ⚡
                                        @else
                                            Recharge en cours
                                        @endif
                                    </span>
                                </span>
                                <span wire:loading wire:target="spin" class="flex flex-col items-center">
                                    <svg class="animate-spin h-6 w-6 text-current mb-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Lancement...</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Puces indicatrices cycliques sous la roue avec selectTab() --}}
                    <div class="flex items-center justify-center gap-2 mt-5">
                        <button
                            type="button"
                            @click="selectTab(0)"
                            class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer"
                            :class="activeTab === 0 ? 'bg-indigo-500/25 text-indigo-300 border border-indigo-500/50 shadow-[0_0_10px_rgba(99,102,241,0.3)]' : 'text-slate-500 hover:text-slate-400 bg-slate-900/50 border border-slate-800'"
                        >
                            <span>👤</span>
                            <span>Races</span>
                        </button>
                        <button
                            type="button"
                            @click="selectTab(1)"
                            class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer"
                            :class="activeTab === 1 ? 'bg-amber-500/25 text-amber-300 border border-amber-500/50 shadow-[0_0_10px_rgba(251,191,36,0.3)]' : 'text-slate-500 hover:text-slate-400 bg-slate-900/50 border border-slate-800'"
                        >
                            <span>⚔️</span>
                            <span>Armes</span>
                        </button>
                        <button
                            type="button"
                            @click="selectTab(2)"
                            class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer"
                            :class="activeTab === 2 ? 'bg-purple-500/25 text-purple-300 border border-purple-500/50 shadow-[0_0_10px_rgba(192,132,252,0.3)]' : 'text-slate-500 hover:text-slate-400 bg-slate-900/50 border border-slate-800'"
                        >
                            <span>🔮</span>
                            <span>Pouvoirs</span>
                        </button>
                    </div>

                    {{-- Ticket d'Urgence proposé si la réserve de charges est vide --}}
                    @if (!$isAdmin && $spinCharges <= 0 && empty($revealedCharacter) && !$isSpinning)
                        <div class="mt-4 flex flex-col items-center gap-1.5">
                            @if ($emergencyCost !== null)
                                <button
                                    wire:click="buyEmergencySpin"
                                    wire:loading.attr="disabled"
                                    @disabled($coins < $emergencyCost)
                                    class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/40 transition hover:scale-105 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading.remove wire:target="buyEmergencySpin">🎟️ Ticket d'Urgence : +1 charge ({{ $emergencyCost }} 🪙)</span>
                                    <span wire:loading wire:target="buyEmergencySpin">Achat en cours...</span>
                                </button>
                                <span class="text-[10px] text-slate-500">
                                    @if ($coins < $emergencyCost)
                                        Pas assez de pièces ({{ $coins }}/{{ $emergencyCost }} 🪙)
                                    @else
                                        {{ $emergencyRemaining }} achat{{ $emergencyRemaining > 1 ? 's' : '' }} restant{{ $emergencyRemaining > 1 ? 's' : '' }} aujourd'hui
                                    @endif
                                </span>
                            @else
                                <span class="text-[10px] text-slate-500">🎟️ Tickets d'Urgence épuisés pour aujourd'hui</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
