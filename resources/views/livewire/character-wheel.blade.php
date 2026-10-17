<div class="space-y-6">
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
            </div>
        </div>
    @endif

    {{-- Main Wheel Section --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-10 shadow-2xl relative overflow-hidden backdrop-blur-md">
        {{-- Ambient background glows --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto text-center space-y-8">
            {{-- Header info --}}
            <div>
                <span class="inline-block px-3 py-1 bg-amber-500/10 border border-amber-500/20 rounded-full text-xs font-semibold text-amber-400 uppercase tracking-widest mb-3">
                    Générateur de Combattants
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    La Roue du Destin
                </h1>
                <p class="mt-2 text-slate-400 text-sm sm:text-base">
                    Actionne les rouleaux pour fusionner une Race, une Arme et un Pouvoir élémentaire.
                </p>
            </div>

            {{-- HUD Stats Grid --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Charges de Spin --}}
                <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4 flex flex-col items-center">
                    <span class="text-xs uppercase font-medium text-slate-400 tracking-wider">Charges de tirage</span>
                    <div class="flex items-center gap-2 mt-2">
                        @if ($isAdmin)
                            <span class="text-3xl text-amber-400 font-black animate-pulse drop-shadow-[0_0_12px_rgba(251,191,36,0.6)]">
                                ⚡ ∞
                            </span>
                        @else
                            @for ($i = 1; $i <= $maxCharges; $i++)
                                <span class="text-2xl transition-all duration-300 {{ $i <= $spinCharges ? 'text-amber-400 drop-shadow-[0_0_8px_rgba(251,191,36,0.5)] scale-110' : 'text-slate-700' }}">
                                    ⚡
                                </span>
                            @endfor
                        @endif
                    </div>
                    <span class="text-xs text-slate-500 mt-2 font-mono">
                        @if ($isAdmin)
                            <span class="text-amber-400 font-bold">Illimité (Compte Admin)</span>
                        @else
                            {{ $spinCharges }}/{{ $maxCharges }} disponibles
                        @endif
                    </span>
                </div>

                {{-- Jauge de Chance (Pity) --}}
                <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4 flex flex-col items-center">
                    <div class="w-full flex justify-between items-center text-xs">
                        <span class="uppercase font-medium text-slate-400 tracking-wider">Jauge de chance</span>
                        <span class="font-bold text-amber-400 font-mono">{{ $pityCounter }}/{{ $pityMax }}</span>
                    </div>
                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-800 h-2.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-yellow-300 h-full transition-all duration-500 rounded-full {{ $pityCounter >= $pityMax ? 'animate-pulse' : '' }}"
                             style="width: {{ min(100, ($pityCounter / $pityMax) * 100) }}%"></div>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-2">
                        @if ($pityCounter >= $pityMax)
                            <strong class="text-amber-400 font-bold">✨ SUPER SPIN PRÊT (Rare+ garanti !)</strong>
                        @else
                            {{ $pityMax - $pityCounter }} spins avant le Super Spin garanti
                        @endif
                    </span>
                </div>
            </div>

            {{-- Spin Action Button --}}
            <div class="pt-2">
                <button
                    wire:click="spin"
                    wire:loading.attr="disabled"
                    @disabled(!$isAdmin && $spinCharges <= 0)
                    class="w-full sm:w-auto px-10 py-5 rounded-xl font-extrabold text-lg uppercase tracking-wider transition-all duration-300 shadow-xl disabled:opacity-40 disabled:cursor-not-allowed {{ $pityCounter >= $pityMax ? 'bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 hover:shadow-amber-500/30 hover:scale-105 animate-neon-pulse' : 'bg-gradient-to-r from-indigo-500 to-cyan-500 text-white hover:from-indigo-600 hover:to-cyan-600 hover:shadow-cyan-500/25 hover:scale-105' }}"
                >
                    <span wire:loading.remove>
                        @if ($pityCounter >= $pityMax)
                            🌟 Déclencher le Super Spin !
                        @elseif ($isAdmin)
                            🎡 Lancer la Roue (Admin ∞)
                        @elseif ($spinCharges > 0)
                            🎡 Lancer la Roue (1 charge)
                        @else
                            ⏳ Rechargement en cours...
                        @endif
                    </span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Tirage en cours...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal / Reveal Card Section --}}
    @if ($revealedCharacter)
        <div class="bg-slate-900 border-2 rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden transition-all duration-500 {{ $revealedCharacter['rarity'] === 'legendary' ? 'border-amber-400 shadow-amber-500/20' : ($revealedCharacter['rarity'] === 'epic' ? 'border-purple-400 shadow-purple-500/20' : ($revealedCharacter['rarity'] === 'rare' ? 'border-cyan-400 shadow-cyan-500/20' : 'border-slate-700')) }}">
            {{-- Badge Rareté --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4 mb-6">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $revealedCharacter['rarity'] === 'legendary' ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : ($revealedCharacter['rarity'] === 'epic' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : ($revealedCharacter['rarity'] === 'rare' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-slate-700/40 text-slate-300 border border-slate-600')) }}">
                        ★ {{ ucfirst($revealedCharacter['rarity']) }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-2">
                        {{ $revealedCharacter['name'] }}
                    </h2>
                </div>

                {{-- Power Score Circle --}}
                <div class="flex items-center gap-3 bg-slate-950/80 px-4 py-2.5 rounded-xl border border-slate-800">
                    <span class="text-xs uppercase font-medium text-slate-400">Score Global</span>
                    <span class="text-3xl font-black text-amber-400 font-mono">
                        {{ $revealedCharacter['power_score'] }}
                    </span>
                </div>
            </div>

            {{-- 3 Slots (Race, Arme, Pouvoir) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs uppercase text-slate-400 block mb-1">Race</span>
                    <span class="text-lg font-bold text-white">{{ ucfirst($revealedCharacter['race_id']) }}</span>
                </div>
                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs uppercase text-slate-400 block mb-1">Arme</span>
                    <span class="text-lg font-bold text-white">{{ ucfirst($revealedCharacter['weapon_id']) }}</span>
                </div>
                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs uppercase text-slate-400 block mb-1">Pouvoir</span>
                    <span class="text-lg font-bold text-indigo-300">{{ ucfirst($revealedCharacter['power_id']) }}</span>
                </div>
            </div>

            {{-- Stats Bars --}}
            <div class="bg-slate-950/50 p-5 rounded-xl border border-slate-800/80 mb-6 space-y-3">
                <h3 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Statistiques de combat</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-rose-400 font-semibold">❤️ Points de Vie</span>
                            <span class="font-mono text-white">{{ $revealedCharacter['hp'] }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-rose-500 h-full" style="width: {{ min(100, ($revealedCharacter['hp'] / 140) * 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-amber-400 font-semibold">⚔️ Force</span>
                            <span class="font-mono text-white">{{ $revealedCharacter['strength'] }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full" style="width: {{ min(100, ($revealedCharacter['strength'] / 100) * 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-cyan-400 font-semibold">⚡ Vitesse</span>
                            <span class="font-mono text-white">{{ $revealedCharacter['speed'] }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-cyan-500 h-full" style="width: {{ min(100, ($revealedCharacter['speed'] / 100) * 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-emerald-400 font-semibold">🛡️ Défense</span>
                            <span class="font-mono text-white">{{ $revealedCharacter['defense'] }}</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: {{ min(100, ($revealedCharacter['defense'] / 100) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Synergies list if any --}}
            @if (!empty($revealedCharacter['synergies_data']))
                <div class="mb-6 space-y-2">
                    <h3 class="text-xs uppercase font-bold text-amber-400 tracking-wider">Synergies Actives</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($revealedCharacter['synergies_data'] as $syn)
                            <div class="p-3 rounded-lg border {{ !empty($syn['is_unstable']) ? 'bg-amber-950/40 border-amber-600/50 text-amber-200' : 'bg-slate-950/60 border-slate-800 text-slate-200' }} text-xs">
                                <span class="font-bold">{{ $syn['icon'] ?? '✨' }} {{ $syn['name'] }}</span>
                                <p class="text-slate-400 mt-1">{{ $syn['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button
                    wire:click="keepCharacter"
                    class="flex-1 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg transition-all duration-200 text-center"
                >
                    ✅ Garder dans la collection
                </button>
                <button
                    wire:click="recycleCharacter"
                    class="flex-1 px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 font-bold rounded-xl shadow-lg transition-all duration-200 text-center"
                >
                    ♻️ Recycler (+{{ $revealedCharacter['recycle_value'] }} 🪙)
                </button>
            </div>
        </div>
    @endif
</div>
