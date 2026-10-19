@props([
    'character' => null,
    'raceId' => null,
    'weaponId' => null,
    'powerId' => null,
    'rarity' => null,
])

@php
    // Extraction universelle (gère objet Model ou array)
    if ($character instanceof \App\Models\Character) {
        $raceId = $character->race_id;
        $weaponId = $character->weapon_id;
        $powerId = $character->power_id;
        $rarityKey = $character->rarity instanceof \App\Enums\Rarity ? $character->rarity->value : $character->rarity;
        $raceData = $character->race_data;
        $weaponData = $character->weapon_data;
        $powerData = $character->power_data;
    } elseif (is_array($character)) {
        $raceId = $character['race_id'] ?? $raceId;
        $weaponId = $character['weapon_id'] ?? $weaponId;
        $powerId = $character['power_id'] ?? $powerId;
        $rarityKey = $character['rarity'] ?? $rarity;
        $raceData = \App\Data\RaceData::find($raceId);
        $weaponData = \App\Data\WeaponData::find($weaponId);
        $powerData = \App\Data\PowerData::find($powerId);
    } else {
        $rarityKey = $rarity;
        $raceData = $raceId ? \App\Data\RaceData::find($raceId) : null;
        $weaponData = $weaponId ? \App\Data\WeaponData::find($weaponId) : null;
        $powerData = $powerId ? \App\Data\PowerData::find($powerId) : null;
    }

    $layers = \App\Services\PaperDollService::resolveLayers($raceId, $weaponId, $powerId, $rarityKey);
    $hasVisualLayer = !empty($layers['race']) || !empty($layers['weapon']);

    $glowColor = match($rarityKey) {
        'legendary' => 'bg-amber-500/25',
        'epic'      => 'bg-purple-500/25',
        'rare'      => 'bg-cyan-500/20',
        default     => 'bg-slate-500/15',
    };
@endphp

@if ($hasVisualLayer)
    {{-- ========================================================================= --}}
    {{-- RENDU PAPER DOLL : SANS CADRE/OUTLINE INTERNE, FOND TRANSPARENT (270x270) --}}
    {{-- ========================================================================= --}}
    <div 
        {{ $attributes->merge(['class' => 'relative flex items-center justify-center select-none group/avatar shrink-0 mx-auto']) }}
        style="width: 100%; max-width: 270px; aspect-ratio: 1 / 1;"
    >
        {{-- Halo d'ambiance doux selon la rareté --}}
        <div class="absolute w-4/5 h-4/5 rounded-full blur-3xl {{ $glowColor }} pointer-events-none" style="z-index: 1;"></div>

        {{-- Layer 0 : Fond d'aura / Décor de rareté --}}
        @if ($layers['background'])
            <img 
                src="{{ asset($layers['background']) }}" 
                alt="Fond {{ $rarityKey }}" 
                class="pointer-events-none transition-transform duration-500 group-hover/avatar:scale-105" 
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 5;"
                loading="lazy"
            >
        @endif

        {{-- Layer 1 : Corps / Silhouette de la Race (Humain, etc.) --}}
        @if ($layers['race'])
            <img 
                src="{{ asset($layers['race']) }}" 
                alt="{{ $raceData['name'] ?? $raceId }}" 
                class="pointer-events-none filter drop-shadow-[0_8px_20px_rgba(0,0,0,0.6)] transition-transform duration-300 group-hover/avatar:scale-[1.02]" 
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; z-index: 10;"
                loading="lazy"
            >
        @endif

        {{-- Layer 2 : Effet Élémentaire / Pouvoir (Flammes, Éclairs, etc.) --}}
        @if ($layers['power'])
            <img 
                src="{{ asset($layers['power']) }}" 
                alt="{{ $powerData['name'] ?? $powerId }}" 
                class="pointer-events-none mix-blend-screen opacity-90 transition-opacity duration-300" 
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; z-index: 20;"
                loading="lazy"
            >
        @endif

        {{-- Layer 3 : Arme tenue (Épée, Bâton, etc.) — STRICTEMENT AU PREMIER PLAN (z-index: 30) --}}
        @if ($layers['weapon'])
            <img 
                src="{{ asset($layers['weapon']) }}" 
                alt="{{ $weaponData['name'] ?? $weaponId }}" 
                class="pointer-events-none filter drop-shadow-[0_4px_12px_rgba(0,0,0,0.5)] transition-transform duration-300 group-hover/avatar:scale-[1.03]" 
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; z-index: 30;"
                loading="lazy"
            >
        @endif
    </div>
@else
    {{-- ========================================================================= --}}
    {{-- FALLBACK PLACEHOLDER (QUAND LES ASSETS DE CETTE COMBINAISON MANQUENT)      --}}
    {{-- ========================================================================= --}}
    <div 
        {{ $attributes->merge(['class' => 'relative rounded-2xl bg-gradient-to-b from-slate-900/90 via-slate-950 to-slate-950 border-2 border-dashed border-slate-700/80 p-3 flex flex-col items-center justify-center select-none overflow-hidden shrink-0 mx-auto']) }}
        style="width: 100%; max-width: 250px; aspect-ratio: 1 / 1;"
    >
        {{-- Halo d'ambiance --}}
        <div class="absolute w-32 h-32 rounded-full blur-3xl {{ $glowColor }} pointer-events-none"></div>

        {{-- Piédestal d'avatar avec emojis --}}
        <div class="relative w-24 h-24 rounded-2xl bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/80 shadow-2xl flex items-center justify-center p-3 my-auto">
            {{-- Avatar Race --}}
            <span class="text-5xl filter drop-shadow-[0_8px_16px_rgba(0,0,0,0.8)]">
                {{ $raceData['icon'] ?? '👤' }}
            </span>

            {{-- Arme (coin bas droite) --}}
            <div class="absolute -bottom-2 -right-2 w-7 h-7 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center text-xs shadow-lg" title="{{ $weaponData['name'] ?? 'Arme' }}">
                {{ $weaponData['icon'] ?? '⚔️' }}
            </div>

            {{-- Pouvoir (coin haut gauche) --}}
            <div class="absolute -top-2 -left-2 w-7 h-7 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center text-xs shadow-lg animate-pulse" title="{{ $powerData['name'] ?? 'Pouvoir' }}">
                {{ $powerData['icon'] ?? '🔮' }}
            </div>
        </div>

        {{-- Mention placeholder --}}
        <div class="absolute bottom-2 text-center text-[9px] text-slate-500 font-medium">
            Artwork en attente
        </div>
    </div>
@endif
