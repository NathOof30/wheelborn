@extends('newda.layout')

@section('content')
@php
    $initialPool = $connectedSnapshot !== null ? $connectedSnapshot['folios'] : $showcaseFolios;
    $hasEnoughFighters = count($initialPool) >= 2;
@endphp
<section class="page-screen page-arena" aria-label="Arène">
    <div id="arena-insufficient-banner" class="arena-empty-banner" @if ($hasEnoughFighters) hidden @endif>
        <svg class="arena-empty-icon" viewBox="0 0 48 48" aria-hidden="true">
            <path d="M10 38 L36 12 M28 10 H38 V20 M10 10 L38 38 M38 38 L32 38 M10 32 L16 38" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="arena-empty-copy">
            <h2 class="arena-empty-title">2 héros requis pour le duel</h2>
        </div>
        <div class="arena-empty-actions">
            <a href="{{ route('newda.invocation') }}" class="btn-brass">Invoquer</a>
            <button type="button" class="btn-cartouche" id="btn-arena-demo">Essayer avec 2 champions de démo</button>
        </div>
    </div>

    <div id="arena-workbench-wrap" @if (! $hasEnoughFighters) hidden @endif>
        <div class="arena-topbar">
            <div class="duel-selectors">
                <label class="selector-field">
                    <span class="selector-tag">I</span>
                    <select id="duel-left-select" class="codex-select" aria-label="Combattant gauche">
                        @foreach ($initialPool as $index => $folio)
                            <option value="{{ $folio['id'] }}" @selected($index === 0)>{{ $folio['name'] }} ({{ $folio['power_score'] }})</option>
                        @endforeach
                    </select>
                </label>

                <label class="selector-field">
                    <span class="selector-tag">II</span>
                    <select id="duel-right-select" class="codex-select" aria-label="Combattant droit">
                        @foreach ($initialPool as $index => $folio)
                            <option value="{{ $folio['id'] }}" @selected($index === min(4, max(0, count($initialPool) - 1)))>{{ $folio['name'] }} ({{ $folio['power_score'] }})</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="arena-demo-chip" id="arena-demo-bar" hidden>
                <span>Mode démo</span>
                <button type="button" class="arena-demo-exit" id="btn-arena-exit-demo" aria-label="Quitter le mode démo">&times;</button>
            </div>
        </div>

        <div class="duel-workbench">
            <div class="duel-arena-grid">
                <div class="duel-slot" id="duel-card-left" style="--duel-glow: {{ $initialLeftFolio['power_pigment_hex'] ?? '#B8923A' }};">
                    <div class="duel-hp-hud tabular-nums">
                        <span class="duel-hp-label">PV</span>
                        <div class="duel-hp-track"><div class="duel-hp-fill" data-duel-bar style="width: 100%;"></div></div>
                        <span class="duel-hp-val" data-duel-hp>{{ $initialLeftFolio['hp'] ?? 0 }}<small>/{{ $initialLeftFolio['hp'] ?? 0 }}</small></span>
                    </div>
                    <div class="duel-card-stage">
                        <span class="duel-stamp duel-stamp--win">VICTORIEUX</span>
                        <span class="duel-stamp duel-stamp--lose">VAINCU</span>
                        <div class="duel-card-mount" data-duel-card-mount>
                            @if ($initialLeftFolio !== null)
                                @include('newda.partials.folio-card', ['folio' => $initialLeftFolio, 'iconRegistry' => $iconRegistry, 'interactiveActions' => false])
                            @endif
                        </div>
                    </div>
                </div>

                <div class="duel-vs-medallion" aria-hidden="true">VS</div>

                <div class="duel-slot" id="duel-card-right" style="--duel-glow: {{ $initialRightFolio['power_pigment_hex'] ?? '#B8923A' }};">
                    <div class="duel-hp-hud tabular-nums">
                        <span class="duel-hp-label">PV</span>
                        <div class="duel-hp-track"><div class="duel-hp-fill" data-duel-bar style="width: 100%;"></div></div>
                        <span class="duel-hp-val" data-duel-hp>{{ $initialRightFolio['hp'] ?? 0 }}<small>/{{ $initialRightFolio['hp'] ?? 0 }}</small></span>
                    </div>
                    <div class="duel-card-stage">
                        <span class="duel-stamp duel-stamp--win">VICTORIEUX</span>
                        <span class="duel-stamp duel-stamp--lose">VAINCU</span>
                        <div class="duel-card-mount" data-duel-card-mount>
                            @if ($initialRightFolio !== null)
                                @include('newda.partials.folio-card', ['folio' => $initialRightFolio, 'iconRegistry' => $iconRegistry, 'interactiveActions' => false])
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="duel-actions">
                <button type="button" class="btn-brass" id="btn-run-duel">Combattre</button>
                <button type="button" class="btn-cartouche btn-cartouche--icon" id="btn-reset-duel" aria-label="Réinitialiser le duel" title="Réinitialiser">↺</button>
            </div>

            <div class="duel-chronicle" id="duel-chronicle" aria-live="polite"></div>
        </div>
    </div>
</section>
@endsection
