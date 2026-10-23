<div class="space-y-6">
    {{-- Animation de déblocage : jouée par la carte recréée (wire:key change avec le niveau) après un achat --}}
    <style>
        @keyframes workshopUnlock {
            0%   { transform: scale(0.97); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
            40%  { transform: scale(1.03); box-shadow: 0 0 45px 6px rgba(52, 211, 153, 0.45); }
            100% { transform: scale(1);    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
        }
        @keyframes workshopBadge {
            0%   { opacity: 0; transform: translateY(6px) scale(0.8); }
            20%  { opacity: 1; transform: translateY(0) scale(1.1); }
            80%  { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-6px) scale(1); }
        }
        .workshop-unlock { animation: workshopUnlock 0.9s cubic-bezier(0.23, 1, 0.32, 1); }
        .workshop-unlock-badge { animation: workshopBadge 2.2s ease-out forwards; }
    </style>

    {{-- Notification (disparaît seule après quelques secondes) --}}
    @if ($notification)
        <div
            wire:key="workshop-toast-{{ md5($notification) }}"
            x-data
            x-init="setTimeout(() => $wire.dismissNotification(), 4500)"
            class="fixed top-20 right-6 z-50 max-w-md p-4 rounded-xl shadow-2xl border backdrop-blur-md {{ $notificationType === 'error' ? 'bg-rose-950/90 border-rose-600 text-rose-200' : ($notificationType === 'success' ? 'bg-emerald-950/90 border-emerald-500 text-emerald-200' : 'bg-slate-900/90 border-slate-700 text-slate-200') }}"
        >
            <div class="flex items-center justify-between gap-3">
                <span class="font-medium text-sm">{{ $notification }}</span>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        </div>
    @endif

    {{-- Bandeau : solde disponible --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-2xl relative overflow-hidden flex flex-wrap items-center justify-between gap-4">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">L'Atelier du Destin</h1>
            <p class="mt-1 text-sm text-slate-400">Investis l'or du recyclage et des victoires dans des améliorations permanentes.</p>
        </div>
        <div class="relative flex items-center gap-2 bg-slate-950/70 border border-amber-500/30 rounded-xl px-4 py-2.5">
            <span class="text-xs uppercase font-medium text-slate-400">Solde</span>
            <span class="text-2xl font-black text-amber-400 font-mono">
                {{ $isAdmin ? '∞' : number_format($coins, 0, ',', ' ') }}
            </span>
            <span class="text-xl">🪙</span>
        </div>
    </div>

    {{-- Cartes d'amélioration --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach ($this->upgrades as $key => $upgrade)
            @php
                $unlocked = $justUnlocked === $key;
                $insufficient = !$upgrade['is_maxed'] && $upgrade['missing'] > 0;
            @endphp

            <div
                wire:key="upgrade-{{ $key }}-{{ $upgrade['level'] }}"
                class="relative flex flex-col bg-slate-900/90 border-2 rounded-2xl p-6 shadow-xl transition-colors duration-300 {{ $upgrade['is_maxed'] ? 'border-amber-400/60' : ($unlocked ? 'border-emerald-400/70' : 'border-slate-800') }} {{ $unlocked ? 'workshop-unlock' : '' }}"
            >
                @if ($unlocked)
                    <span class="workshop-unlock-badge absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/40 whitespace-nowrap">
                        ✨ Niveau {{ $upgrade['level'] }} débloqué !
                    </span>
                @endif

                {{-- En-tête --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl drop-shadow">{{ $upgrade['icon'] }}</span>
                        <h3 class="text-lg font-extrabold text-white leading-tight">{{ $upgrade['name'] }}</h3>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold font-mono {{ $upgrade['is_maxed'] ? 'bg-amber-400/20 text-amber-300 border border-amber-400/40' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                        Niv. {{ $upgrade['level'] }}/{{ $upgrade['max_level'] }}
                    </span>
                </div>

                {{-- Barre de progression : un segment par niveau --}}
                <div class="flex gap-1.5 mt-5">
                    @for ($i = 1; $i <= $upgrade['max_level']; $i++)
                        <div class="h-2.5 flex-1 rounded-full transition-colors duration-500 {{ $i <= $upgrade['level'] ? ($upgrade['is_maxed'] ? 'bg-gradient-to-r from-amber-400 to-yellow-300' : 'bg-gradient-to-r from-emerald-500 to-teal-300') : 'bg-slate-800' }} {{ $unlocked && $i === $upgrade['level'] ? 'animate-pulse' : '' }}"></div>
                    @endfor
                </div>

                {{-- Effets --}}
                <div class="mt-5 space-y-2 text-sm flex-1">
                    <div class="bg-slate-950/60 border border-slate-800 rounded-lg p-3">
                        <span class="block text-[10px] uppercase tracking-wider text-slate-500 mb-0.5">Effet actuel</span>
                        <span class="{{ $upgrade['level'] > 0 ? 'text-emerald-300 font-semibold' : 'text-slate-500' }}">{{ $upgrade['current_effect'] }}</span>
                    </div>
                    @if ($upgrade['next_effect'])
                        <div class="bg-slate-950/30 border border-dashed border-slate-700 rounded-lg p-3">
                            <span class="block text-[10px] uppercase tracking-wider text-slate-500 mb-0.5">Au niveau {{ $upgrade['level'] + 1 }}</span>
                            <span class="text-slate-300">{{ $upgrade['next_effect'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Achat --}}
                <div class="mt-5">
                    @if ($upgrade['is_maxed'])
                        <div class="w-full py-3 rounded-xl text-center text-sm font-bold bg-amber-400/10 text-amber-300 border border-amber-400/30">
                            👑 Niveau maximum atteint
                        </div>
                    @else
                        <button
                            wire:click="purchase('{{ $key }}')"
                            wire:loading.attr="disabled"
                            @disabled($insufficient)
                            class="w-full py-3 rounded-xl text-sm font-extrabold transition-all duration-200 disabled:cursor-not-allowed {{ $insufficient ? 'bg-slate-800 text-slate-500 border border-rose-500/30' : 'bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/25 active:scale-95' }}"
                        >
                            <span wire:loading.remove wire:target="purchase('{{ $key }}')">
                                Améliorer · {{ number_format($upgrade['next_cost'], 0, ',', ' ') }} 🪙
                            </span>
                            <span wire:loading wire:target="purchase('{{ $key }}')">Amélioration...</span>
                        </button>
                        @if ($insufficient)
                            <p class="mt-2 text-center text-xs font-semibold text-rose-400">
                                Fonds insuffisants : il manque {{ number_format($upgrade['missing'], 0, ',', ' ') }} 🪙
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
