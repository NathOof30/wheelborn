@extends('newda.layout')

@section('content')
@php
    $workshopIcons = [
        'magnet' => '<path d="M12 10 V24 C12 31.7 17.4 38 24 38 C30.6 38 36 31.7 36 24 V10 H29 V24 C29 27.6 26.8 30.5 24 30.5 C21.2 30.5 19 27.6 19 24 V10 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><line x1="12" y1="16" x2="19" y2="16" stroke="currentColor" stroke-width="2.4"/><line x1="29" y1="16" x2="36" y2="16" stroke="currentColor" stroke-width="2.4"/>',
        'anvil' => '<path d="M7 18 H35 L41 23 H31 L34 36 H14 L17 23 H7 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="M16 11 H32 V18 H16 Z" fill="none" stroke="currentColor" stroke-width="2.3"/><path d="M24 5 V9 M18 7 L20 9.5 M30 7 L28 9.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>',
        'chest' => '<rect x="8" y="16" width="32" height="22" rx="2.5" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="M8 22 H40 M18 16 V11 C18 9.5 19.5 8.5 21 8.5 H27 C28.5 8.5 30 9.5 30 11 V16" fill="none" stroke="currentColor" stroke-width="2.4"/><rect x="21" y="20" width="6" height="7" rx="1" fill="currentColor"/>',
    ];
@endphp
<section class="page-screen page-workshop" aria-label="Atelier">
    <div class="workshop-bar">
        <div class="workshop-balance tabular-nums">
            <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true">
                <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                <polygon points="10,5.5 13,10 10,14.5 7,10" fill="currentColor"/>
            </svg>
            <strong id="workshop-balance">{{ number_format($hud['coins'], 0, ',', ' ') }}</strong>
            <span>écus</span>
        </div>
    </div>

    <div class="workshop-cards-grid" id="workshop-cards-grid">
        @foreach ($workshopCards as $card)
            @php
                $lvl = (int) $card['level'];
                $max = (int) $card['max_level'];
                $isMaxed = $lvl >= $max;
                $nextCost = $isMaxed ? null : (int) ($card['costs'][$lvl] ?? 0);
                $canAfford = ! $isMaxed && $hud['coins'] >= $nextCost;
            @endphp
            <article class="workshop-card {{ $isMaxed ? 'is-maxed' : '' }}" data-workshop-card="{{ $card['key'] }}">
                <div class="workshop-card-head">
                    <div class="workshop-emblem">
                        <svg class="workshop-svg" viewBox="0 0 48 48" aria-hidden="true">
                            {!! $workshopIcons[$card['icon_key']] ?? $workshopIcons['anvil'] !!}
                        </svg>
                    </div>
                    <div class="workshop-title-wrap">
                        <h2 class="workshop-card-title">{{ $card['name'] }}</h2>
                        <span class="workshop-level-pill tabular-nums" data-ws-level>{{ $lvl }}/{{ $max }}</span>
                    </div>
                </div>

                <div class="workshop-segments" data-ws-segments aria-label="Progression">
                    @for ($i = 1; $i <= $max; $i++)
                        <span class="workshop-seg {{ $i <= $lvl ? 'is-filled' : '' }}"></span>
                    @endfor
                </div>

                <div class="workshop-stats tabular-nums" data-ws-bonus>
                    @if ($card['key'] === 'rare_magnet')
                        <span class="ws-current">+{{ $lvl * $card['bonus_per_level'] }}% Mythique</span>
                        @if (! $isMaxed)
                            <span class="ws-arrow" aria-hidden="true">→</span>
                            <span class="ws-next">+{{ ($lvl + 1) * $card['bonus_per_level'] }}%</span>
                        @endif
                    @elseif ($card['key'] === 'hero_forge')
                        <span class="ws-current">+{{ $lvl * $card['bonus_hp'] }} PV · +{{ $lvl * $card['bonus_str'] }} FOR</span>
                        @if (! $isMaxed)
                            <span class="ws-arrow" aria-hidden="true">→</span>
                            <span class="ws-next">+{{ ($lvl + 1) * $card['bonus_hp'] }} · +{{ ($lvl + 1) * $card['bonus_str'] }}</span>
                        @endif
                    @else
                        @php
                            $curMult = $lvl > 0 ? (float) $card['multipliers'][$lvl - 1] : 1.0;
                            $nextMult = ! $isMaxed ? (float) $card['multipliers'][$lvl] : null;
                        @endphp
                        <span class="ws-current">×{{ number_format($curMult, 2, ',', '') }} Arène</span>
                        @if ($nextMult !== null)
                            <span class="ws-arrow" aria-hidden="true">→</span>
                            <span class="ws-next">×{{ number_format($nextMult, 2, ',', '') }}</span>
                        @endif
                    @endif
                </div>

                <div class="workshop-card-foot">
                    <button
                        type="button"
                        class="btn-brass workshop-buy-btn"
                        data-workshop-buy="{{ $card['key'] }}"
                        @disabled($isMaxed || ! $canAfford)
                    >
                        @if ($isMaxed)
                            <span>Max</span>
                        @else
                            <span data-ws-btn-label>Améliorer · {{ number_format($nextCost, 0, ',', ' ') }}</span>
                        @endif
                    </button>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
