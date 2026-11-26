@extends('newda.layout')

@section('content')
@php
    $leagueIcons = [
        'bronze' => '<path d="M12 3 L19 6.5 V12.5 C19 16.8 15.8 19.8 12 21 C8.2 19.8 5 16.8 5 12.5 V6.5 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'silver' => '<path d="M12 3 L19 6.5 V12.5 C19 16.8 15.8 19.8 12 21 C8.2 19.8 5 16.8 5 12.5 V6.5 Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7 V16 M8.5 11.5 H15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'gold' => '<polygon points="12,3 14.8,8.8 21,9.6 16.4,14 17.6,20.2 12,17.2 6.4,20.2 7.6,14 3,9.6 9.2,8.8" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>',
        'platinum' => '<polygon points="12,2.5 20,9.5 12,21.5 4,9.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><line x1="4" y1="9.5" x2="20" y2="9.5" stroke="currentColor" stroke-width="1.6"/>',
        'master' => '<path d="M3 17 L5.5 7 L10 11.5 L12 5 L14 11.5 L18.5 7 L21 17 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><line x1="4.5" y1="20" x2="19.5" y2="20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    ];
    $currentStandings = $standingsByLeague[$activeLeague] ?? [];
    $topThree = array_slice($currentStandings, 0, 3);
    $podiumOrder = [];
    if (isset($topThree[1])) {
        $podiumOrder[] = $topThree[1];
    }
    if (isset($topThree[0])) {
        $podiumOrder[] = $topThree[0];
    }
    if (isset($topThree[2])) {
        $podiumOrder[] = $topThree[2];
    }
    $restStandings = array_slice($currentStandings, 3);
@endphp
<section class="page-screen page-leaderboard" aria-label="Classement des Ligues">
    <div class="league-tabs" role="tablist" aria-label="Ligues">
        @foreach ($leagues as $league)
            @php
                $range = $league['max_elo'] !== null
                    ? $league['min_elo'].'–'.$league['max_elo'].' Élo'
                    : $league['min_elo'].'+ Élo';
            @endphp
            <button
                type="button"
                role="tab"
                id="league-tab-{{ $league['value'] }}"
                class="league-tab {{ $league['value'] === $activeLeague ? 'is-active' : '' }}"
                aria-selected="{{ $league['value'] === $activeLeague ? 'true' : 'false' }}"
                aria-controls="rang-tabpanel"
                tabindex="{{ $league['value'] === $activeLeague ? '0' : '-1' }}"
                data-league-tab="{{ $league['value'] }}"
                style="--league-color: {{ $league['color'] }};"
                title="{{ $league['label'] }} ({{ $range }})"
            >
                <svg class="league-tab-icon" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $leagueIcons[$league['value']] ?? $leagueIcons['silver'] !!}
                </svg>
                <span>{{ $league['label'] }}</span>
            </button>
        @endforeach
    </div>

    <div id="rang-tabpanel" role="tabpanel" aria-labelledby="league-tab-{{ $activeLeague }}">
    <div class="podium-stage" id="rang-podium">
        @foreach ($podiumOrder as $entry)
            <button
                type="button"
                class="podium-card podium-card--rank-{{ $entry['rank'] }}"
                data-inspect-rival="{{ $entry['id'] }}"
                style="--league-color: {{ $entry['league_color'] }};"
            >
                <span class="podium-rank tabular-nums">#{{ $entry['rank'] }}</span>
                <strong class="podium-name">{{ $entry['name'] }}</strong>
                <span class="podium-elo tabular-nums">{{ number_format($entry['elo'], 0, ',', ' ') }}</span>
                <span class="podium-hero-chip">
                    {!! $iconRegistry->renderInlineIcon('races', $entry['champion']['race_id'], 'podium-mini-icon', $entry['champion']['race_name']) !!}
                    <span>{{ $entry['champion']['name'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <div class="rang-table" id="rang-table">
        @foreach ($restStandings as $entry)
            <div class="rang-row">
                <span class="rang-pos tabular-nums">#{{ $entry['rank'] }}</span>
                <span class="rang-name">{{ $entry['name'] }}</span>
                <span class="rang-hero">
                    {!! $iconRegistry->renderInlineIcon('races', $entry['champion']['race_id'], 'rang-mini-icon', $entry['champion']['race_name']) !!}
                    <span>{{ $entry['champion']['name'] }}</span>
                </span>
                <span class="rang-winrate tabular-nums" title="{{ $entry['wins'] }}V / {{ $entry['losses'] }}D">{{ $entry['winrate'] }}%</span>
                <span class="rang-elo tabular-nums">{{ number_format($entry['elo'], 0, ',', ' ') }}</span>
                <button
                    type="button"
                    class="rang-inspect-btn"
                    data-inspect-rival="{{ $entry['id'] }}"
                    aria-label="Inspecter {{ $entry['name'] }}"
                    title="Inspecter"
                >
                    <svg class="hud-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M2 12 Q12 4 22 12 Q12 20 2 12 Z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3.2" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
    </div>

    @if ($pinnedPlayer !== null)
        <div class="rang-pinned" style="--league-color: {{ $pinnedPlayer['league_color'] }};">
            <span class="rang-pinned-tag">Vous</span>
            <span class="rang-pos tabular-nums">#{{ $pinnedPlayer['rank'] }}</span>
            <strong class="rang-name">{{ $pinnedPlayer['name'] }}</strong>
            <span class="rang-league-pill">{{ $pinnedPlayer['league_label'] }}</span>
            <span class="rang-elo tabular-nums">{{ number_format($pinnedPlayer['elo'], 0, ',', ' ') }} Élo</span>
            @if ($pinnedPlayer['champion'] !== null)
                <button
                    type="button"
                    class="rang-inspect-btn"
                    data-inspect-rival="{{ $pinnedPlayer['id'] }}"
                    aria-label="Inspecter votre champion {{ $pinnedPlayer['champion']['name'] }}"
                    title="Inspecter mon champion"
                >
                    <svg class="hud-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M2 12 Q12 4 22 12 Q12 20 2 12 Z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3.2" fill="currentColor"/>
                    </svg>
                </button>
            @endif
        </div>
    @endif
</section>

<div id="rang-inspector-modal" class="codex-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="inspector-title" hidden>
    <div class="codex-modal">
        <div class="codex-modal-head">
            <div>
                <h2 id="inspector-title" class="codex-modal-title"></h2>
                <div id="inspector-meta" class="codex-modal-meta tabular-nums"></div>
            </div>
            <button type="button" class="sandbox-close" id="btn-inspector-close" aria-label="Fermer">&times;</button>
        </div>
        <div id="inspector-body" class="codex-modal-body"></div>
    </div>
</div>
@endsection
