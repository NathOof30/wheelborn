<div
    wire:key="player-hud-bar"
    x-data="{
        coins: {{ (int) $coins }},
        elo: {{ (int) $elo }},
        spinCharges: {{ (int) $spinCharges }},
        bumpCoins: false,
        formatNumber(n) {
            return new Intl.NumberFormat('fr-FR').format(n);
        },
        updateStats(detail) {
            const data = Array.isArray(detail) ? (detail[0] || {}) : (detail || {});
            if (data.coins !== undefined) {
                if (data.coins !== this.coins) {
                    this.bumpCoins = true;
                    setTimeout(() => { this.bumpCoins = false; }, 600);
                }
                this.coins = data.coins;
            }
            if (data.elo !== undefined) this.elo = data.elo;
            if (data.spinCharges !== undefined) this.spinCharges = data.spinCharges;
        }
    }"
    @stats-updated.window="updateStats($event.detail)"
    class="flex items-center gap-3 bg-slate-950/60 px-4 py-1.5 rounded-full border border-slate-800 text-sm shadow-inner transition-all duration-300"
>
    <div
        class="flex items-center gap-1.5 text-amber-400 font-semibold transition-transform duration-200"
        :class="{ 'scale-110 text-amber-300 brightness-125': bumpCoins }"
        title="Pièces d'or"
    >
        <span>🪙</span>
        @if ($isAdmin)
            <span class="font-mono">∞</span>
        @else
            <span class="font-mono" x-text="formatNumber(coins)">{{ number_format($coins, 0, ',', ' ') }}</span>
        @endif
    </div>
    <div class="h-3 w-px bg-slate-800"></div>
    <div class="flex items-center gap-1.5 text-cyan-400 font-semibold" title="Points d'Élo">
        <span>{{ $leagueIcon }}</span>
        <span class="font-mono" x-text="elo">{{ $elo }}</span>
    </div>
    <div class="h-3 w-px bg-slate-800"></div>
    <div class="flex items-center gap-1.5 text-emerald-400 font-semibold" title="Charges de spin">
        <span>⚡</span>
        @if ($isAdmin)
            <span class="font-mono">∞</span>
        @else
            <span class="font-mono" x-text="spinCharges + '/3'">{{ $spinCharges }}/3</span>
        @endif
    </div>
</div>
