<div class="space-y-6">
    <style>
        @keyframes workshopUnlock {
            0%   { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(184, 146, 58, 0); }
            40%  { transform: scale(1.02); box-shadow: 0 0 32px 4px rgba(184, 146, 58, 0.45); }
            100% { transform: scale(1);    box-shadow: 0 0 0 0 rgba(184, 146, 58, 0); }
        }
        @keyframes workshopBadge {
            0%   { opacity: 0; transform: translate(-50%, 6px) scale(0.85); }
            20%  { opacity: 1; transform: translate(-50%, 0) scale(1.06); }
            80%  { opacity: 1; transform: translate(-50%, 0) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -6px) scale(1); }
        }
        .workshop-unlock { animation: workshopUnlock 0.9s cubic-bezier(0.23, 1, 0.32, 1); }
        .workshop-unlock-badge { animation: workshopBadge 2.2s ease-out forwards; }
    </style>

    @if ($notification)
        <div
            wire:key="workshop-toast-{{ md5($notification) }}"
            x-data="{
                timer: null,
                init() { this.timer = setTimeout(() => $wire.dismissNotification(), 4500); },
                destroy() { if (this.timer) clearTimeout(this.timer); }
            }"
            class="fixed top-20 right-4 sm:right-6 z-50 max-w-md p-4 rounded-lg shadow-2xl border-2 {{ $notificationType === 'error' ? 'bg-parchment-bright border-wax text-wax' : ($notificationType === 'success' ? 'bg-parchment-bright border-forest text-forest' : 'bg-parchment-bright border-brass text-ink') }}"
            role="status"
        >
            <div class="flex items-center justify-between gap-3">
                <span class="font-body font-bold text-sm">{{ $notification }}</span>
                <button type="button" wire:click="dismissNotification" class="font-bold text-ink-muted hover:text-ink" aria-label="Fermer">&times;</button>
            </div>
        </div>
    @endif

    {{-- Bandeau Trésorerie --}}
    <div class="codex-panel codex-panel--framed p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">L'Atelier du Destin</h1>
            <p class="font-body text-sm sm:text-base italic text-ink-muted">
                Investissez l'or du recyclage et des victoires dans des améliorations permanentes.
            </p>
        </div>

        <div class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-parchment-bright border-2 border-brass shadow-inner">
            <span class="font-display text-xs uppercase font-bold tracking-wider text-ink-muted">Trésorerie</span>
            <span class="font-display text-2xl font-bold text-brass-dark">
                {{ $isAdmin ? '∞' : number_format($coins, 0, ',', ' ') }}
            </span>
            <span class="text-lg" aria-hidden="true">🪙</span>
        </div>
    </div>

    {{-- 3 Cartes d'Amélioration Symétriques --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach ($this->upgrades as $key => $upgrade)
            @php
                $unlocked = $justUnlocked === $key;
                $insufficient = ! $upgrade['is_maxed'] && $upgrade['missing'] > 0;
            @endphp

            <div
                wire:key="upgrade-{{ $key }}-{{ $upgrade['level'] }}"
                class="codex-panel codex-panel--framed relative flex flex-col p-5 sm:p-6 transition-colors duration-300 {{ $upgrade['is_maxed'] ? 'border-brass' : ($unlocked ? 'border-forest' : '') }} {{ $unlocked ? 'workshop-unlock' : '' }}"
            >
                @if ($unlocked)
                    <span class="workshop-unlock-badge absolute -top-3.5 left-1/2 px-3 py-1 rounded-full font-display text-[11px] font-bold bg-forest text-parchment-bright shadow-md whitespace-nowrap">
                        ✨ Niveau {{ $upgrade['level'] }} débloqué !
                    </span>
                @endif

                {{-- En-tête --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-11 h-11 rounded-full bg-parchment-shade/80 border border-brass-dark/45 flex items-center justify-center text-2xl shrink-0" aria-hidden="true">
                            {{ $upgrade['icon'] }}
                        </span>
                        <h3 class="font-display text-base font-bold text-ink leading-snug">
                            {{ $upgrade['name'] }}
                        </h3>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-md font-display text-xs font-bold {{ $upgrade['is_maxed'] ? 'bg-brass/25 text-brass-dark border border-brass' : 'bg-parchment-bright text-ink-sepia border border-parchment-border' }}">
                        Niv. {{ $upgrade['level'] }}/{{ $upgrade['max_level'] }}
                    </span>
                </div>

                {{-- Jauge segmentée en laiton --}}
                <div class="flex gap-1.5 mt-5">
                    @for ($i = 1; $i <= $upgrade['max_level']; $i++)
                        <div class="workshop-seg {{ $i <= $upgrade['level'] ? 'is-filled' : '' }}"></div>
                    @endfor
                </div>

                {{-- Effets --}}
                <div class="mt-5 space-y-2.5 flex-1">
                    <div class="p-3 rounded-md bg-parchment-bright border border-parchment-border">
                        <span class="block font-display text-[10px] font-bold uppercase tracking-wider text-ink-muted mb-1">Effet actuel</span>
                        <span class="font-body text-sm {{ $upgrade['level'] > 0 ? 'text-forest font-bold' : 'italic text-ink-muted' }}">
                            {{ $upgrade['current_effect'] }}
                        </span>
                    </div>

                    @if ($upgrade['next_effect'])
                        <div class="p-3 rounded-md bg-parchment-light/60 border border-dashed border-brass-dark/50">
                            <span class="block font-display text-[10px] font-bold uppercase tracking-wider text-brass-dark mb-1">Au niveau {{ $upgrade['level'] + 1 }}</span>
                            <span class="font-body text-sm font-semibold text-ink">
                                {{ $upgrade['next_effect'] }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Bouton d'Achat ou Sceau Max --}}
                <div class="mt-5 pt-4 border-t border-parchment-border/60">
                    @if ($upgrade['is_maxed'])
                        <div class="w-full py-2.5 px-3 rounded-md text-center font-display text-xs font-bold uppercase tracking-wider bg-brass/20 text-brass-dark border border-brass">
                            👑 Niveau maximum atteint
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="purchase('{{ $key }}')"
                            wire:loading.attr="disabled"
                            @disabled($insufficient)
                            class="btn-brass w-full"
                        >
                            <span wire:loading.remove wire:target="purchase('{{ $key }}')">
                                Améliorer · {{ number_format($upgrade['next_cost'], 0, ',', ' ') }} 🪙
                            </span>
                            <span wire:loading wire:target="purchase('{{ $key }}')">Amélioration...</span>
                        </button>
                        @if ($insufficient)
                            <p class="mt-2 text-center font-body text-xs font-bold text-wax">
                                Fonds insuffisants : il manque {{ number_format($upgrade['missing'], 0, ',', ' ') }} 🪙
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
