@extends('newda.layout')

@section('content')
@php
    $rarityCounts = [
        'all' => count($initialFolios),
    ];
    foreach ($rarities as $r) {
        $rarityCounts[$r['value']] = count(array_filter($initialFolios, fn (array $f): bool => $f['rarity'] === $r['value']));
    }
@endphp
<section class="page-screen page-grimoire" aria-label="Grimoire">
    <div class="grimoire-toolbar">
        <div class="rarity-filters" role="group" aria-label="Filtrer par rareté">
            <button type="button" class="filter-chip is-active" data-rarity-filter="all">
                <span>Tous</span>
                <span class="filter-chip-count tabular-nums" data-filter-count="all">{{ $rarityCounts['all'] }}</span>
            </button>

            @foreach ($rarities as $rarity)
                <button type="button" class="filter-chip" data-rarity-filter="{{ $rarity['value'] }}" style="--chip-hex: {{ $rarity['seal_hex'] }};">
                    <span class="rarity-dot" aria-hidden="true"></span>
                    <span>{{ $rarity['label'] }}</span>
                    <span class="filter-chip-count tabular-nums" data-filter-count="{{ $rarity['value'] }}">{{ $rarityCounts[$rarity['value']] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        <span class="source-indicator" id="grimoire-source-badge" hidden>Vitrine</span>
    </div>

    <div class="folios-grid" id="folios-grid" @if (empty($initialFolios)) hidden @endif>
        @foreach ($initialFolios as $folio)
            @include('newda.partials.folio-card', ['folio' => $folio, 'iconRegistry' => $iconRegistry])
        @endforeach
    </div>

    <div id="gallery-empty-state" class="empty-folio-state" @if (! empty($initialFolios)) hidden @endif>
        <div class="empty-folio-silhouette" aria-hidden="true">
            <svg viewBox="0 0 120 150" class="empty-folio-svg">
                <rect x="8" y="8" width="104" height="134" rx="4" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="6 5"/>
                <circle cx="60" cy="64" r="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-dasharray="3 3"/>
                <path d="M60 48 V80 M44 64 H76" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="28" y1="108" x2="92" y2="108" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity="0.6"/>
                <line x1="40" y1="120" x2="80" y2="120" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity="0.4"/>
            </svg>
        </div>
        <h2 class="empty-folio-title" id="gallery-empty-title">Grimoire vierge</h2>
        <a id="gallery-empty-cta" href="{{ route('newda.invocation') }}" class="btn-brass">Invoquer</a>
        <button type="button" id="gallery-reset-filter-btn" class="btn-cartouche" hidden>Tout afficher</button>
    </div>
</section>
@endsection
