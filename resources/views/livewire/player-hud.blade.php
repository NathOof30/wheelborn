<div
    wire:key="player-hud-bar"
    x-data="{
        coins: {{ (int) $coins }},
        elo: {{ (int) $elo }},
        spinCharges: {{ (int) $spinCharges }},
        arenaTickets: {{ (int) $arenaTickets }},
        maxTickets: {{ (int) $maxTickets }},
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
            if (data.arenaTickets !== undefined) this.arenaTickets = data.arenaTickets;
        }
    }"
    @stats-updated.window="updateStats($event.detail)"
    class="codex-hud tabular-nums"
    aria-label="Ressources du joueur"
>
    <div
        class="hud-chip hud-chip--gold transition-transform duration-200"
        :class="{ 'scale-110 brightness-125': bumpCoins }"
        title="Pièces d'or"
    >
        <span aria-hidden="true">🪙</span>
        @if ($isAdmin)
            <span>∞</span>
        @else
            <span x-text="formatNumber(coins)">{{ number_format($coins, 0, ',', ' ') }}</span>
        @endif
    </div>

    <span class="hud-chip--sep" aria-hidden="true"></span>

    <div class="hud-chip" title="{{ $leagueLabel }} · {{ $elo }} Élo">
        <span aria-hidden="true">{{ $leagueIcon }}</span>
        <span x-text="elo">{{ $elo }}</span>
    </div>

    <span class="hud-chip--sep" aria-hidden="true"></span>

    <div class="hud-chip" title="Charges de tirage">
        <svg class="hud-icon text-brass-light" viewBox="0 0 18 22" aria-hidden="true">
            <path d="M9 1.5 C9 1.5 2.5 10 2.5 14.2 C2.5 17.8 5.4 20.5 9 20.5 C12.6 20.5 15.5 17.8 15.5 14.2 C15.5 10 9 1.5 9 1.5 Z" fill="currentColor"/>
        </svg>
        @if ($isAdmin)
            <span>∞</span>
        @else
            <span x-text="spinCharges + '/3'">{{ $spinCharges }}/3</span>
        @endif
    </div>

    <span class="hud-chip--sep" aria-hidden="true"></span>

    <div class="hud-chip" title="Tickets d'arène">
        <svg class="hud-icon text-brass-light" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @if ($isAdmin)
            <span>∞</span>
        @else
            <span x-text="arenaTickets + '/' + maxTickets">{{ $arenaTickets }}/{{ $maxTickets }}</span>
        @endif
    </div>
</div>
