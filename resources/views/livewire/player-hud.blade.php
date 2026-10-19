<div
    x-data="{
        coins: {{ (int) $coins }},
        elo: {{ (int) $elo }},
        spinCharges: {{ (int) $spinCharges }},
        formatNumber(n) {
            return new Intl.NumberFormat('fr-FR').format(n);
        }
    }"
    @stats-updated.window="
        if ($event.detail.coins !== undefined) coins = $event.detail.coins;
        if ($event.detail.elo !== undefined) elo = $event.detail.elo;
        if ($event.detail.spinCharges !== undefined) spinCharges = $event.detail.spinCharges;
    "
    class="flex items-center gap-3 bg-slate-950/60 px-4 py-1.5 rounded-full border border-slate-800 text-sm shadow-inner transition-all duration-300"
>
    <div class="flex items-center gap-1.5 text-amber-400 font-semibold" title="Pièces d'or">
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
