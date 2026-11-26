<div
    class="space-y-6"
    @if(! $isAdmin && $spinCharges < $maxCharges) wire:poll.10s="refreshState" @endif
>
    {{-- Notification Codex --}}
    @if ($notification)
        <div
            role="status"
            class="codex-alert {{ $notificationType === 'error' ? 'codex-alert--error' : ($notificationType === 'success' ? 'codex-alert--success' : ($notificationType === 'warning' ? 'codex-alert--warning' : 'codex-alert--info')) }}"
        >
            <span>{{ $notification }}</span>
            <button type="button" wire:click="dismissNotification" class="font-display font-bold px-2 py-0.5 rounded hover:bg-black/5 cursor-pointer" aria-label="Fermer">&times;</button>
        </div>
    @endif

    {{-- Barre Admin & Forge Personnalisée --}}
    @if ($isAdmin)
        <div class="space-y-4">
            <div class="codex-panel codex-panel--gold p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-full bg-brass/20 border border-brass-dark/50 flex items-center justify-center text-lg shrink-0" aria-hidden="true">👑</span>
                    <div>
                        <h3 class="font-display font-bold text-sm text-ink flex items-center gap-2">
                            <span>Atelier Administrateur</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-display font-bold bg-wax/15 text-wax border border-wax/40">ACTIF</span>
                        </h3>
                        <p class="text-sm text-ink-sepia">Tirages illimités · Pièces infinies · Contrôles de test</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="adminAddCoins(50000)" class="btn-cartouche btn-cartouche--sm">
                        +50 000 🪙
                    </button>
                    <button type="button" wire:click="adminTriggerSuperSpin" class="btn-cartouche btn-cartouche--sm">
                        🌟 Forcer Super Spin (10/10)
                    </button>
                    <button type="button" wire:click="adminResetPity" class="btn-cartouche btn-cartouche--sm">
                        Reset Pity
                    </button>
                    <button type="button" wire:click="toggleAdminForge" class="btn-brass btn-brass--sm">
                        <span>🧪 Forge Personnalisée</span>
                        <span class="text-xs opacity-80">{{ $showAdminForge ? '▲' : '▼' }}</span>
                    </button>
                </div>
            </div>

            @if ($showAdminForge)
                <div class="codex-panel codex-panel--gold p-5 space-y-5">
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-parchment-border">
                        <div>
                            <h4 class="font-display font-bold text-base text-ink">
                                Forge Personnalisée &amp; Simulateur de Roue
                            </h4>
                            <p class="text-sm text-ink-sepia">
                                Composez une combinaison Race, Arme, Pouvoir et Rareté pour tester le Paper Doll et la Volvelle.
                            </p>
                        </div>
                        <button type="button" wire:click="toggleAdminForge" class="btn-cartouche btn-cartouche--sm" aria-label="Fermer la Forge">&times;</button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <div class="lg:col-span-7 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="codex-well p-3">
                                    <label for="admin-race" class="block font-display text-xs font-bold uppercase tracking-wider text-ink-sepia mb-1.5">
                                        1. Race
                                    </label>
                                    <select id="admin-race" wire:model.live="adminSelectedRace" class="codex-select w-full">
                                        @foreach ($this->adminAllRaces as $r)
                                            <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="codex-well p-3">
                                    <label for="admin-weapon" class="block font-display text-xs font-bold uppercase tracking-wider text-ink-sepia mb-1.5">
                                        2. Arme
                                    </label>
                                    <select id="admin-weapon" wire:model.live="adminSelectedWeapon" class="codex-select w-full">
                                        @foreach ($this->adminAllWeapons as $w)
                                            <option value="{{ $w['id'] }}">{{ $w['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="codex-well p-3">
                                    <label for="admin-power" class="block font-display text-xs font-bold uppercase tracking-wider text-ink-sepia mb-1.5">
                                        3. Pouvoir
                                    </label>
                                    <select id="admin-power" wire:model.live="adminSelectedPower" class="codex-select w-full">
                                        @foreach ($this->adminAllPowers as $p)
                                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="codex-well p-3">
                                    <label for="admin-rarity" class="block font-display text-xs font-bold uppercase tracking-wider text-ink-sepia mb-1.5">
                                        4. Palier Rareté
                                    </label>
                                    <select id="admin-rarity" wire:model.live="adminSelectedRarity" class="codex-select w-full">
                                        @foreach (\App\Enums\Rarity::cases() as $rarity)
                                            <option value="{{ $rarity->value }}">{{ $rarity->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Diagnostic Calques & Synergies --}}
                            <div class="codex-well p-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-display text-xs font-bold uppercase text-ink-sepia">Calques :</span>
                                    <span class="px-2 py-0.5 rounded font-display text-xs font-bold border {{ $this->adminAssetStatus['race'] ? 'bg-forest/10 border-forest/40 text-forest' : 'bg-parchment-shade/60 border-parchment-border text-ink-muted' }}">
                                        Race {{ $this->adminAssetStatus['race'] ? '✓' : 'Blason' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded font-display text-xs font-bold border {{ $this->adminAssetStatus['weapon'] ? 'bg-forest/10 border-forest/40 text-forest' : 'bg-parchment-shade/60 border-parchment-border text-ink-muted' }}">
                                        Arme {{ $this->adminAssetStatus['weapon'] ? '✓' : '—' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded font-display text-xs font-bold border {{ $this->adminAssetStatus['aura'] ? 'bg-brass/20 border-brass-dark/40 text-brass-dark' : 'bg-parchment-shade/60 border-parchment-border text-ink-muted' }}">
                                        Aura {{ $this->adminAssetStatus['aura'] ? '✓' : '—' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    @forelse ($this->adminDetectedSynergies as $syn)
                                        <span class="px-2.5 py-0.5 rounded-full font-display text-xs font-bold border {{ ! empty($syn['is_unstable']) ? 'bg-wax/10 border-wax/40 text-wax' : 'bg-brass/20 border-brass-dark/40 text-brass-dark' }}">
                                            {{ ! empty($syn['is_unstable']) ? '⚡' : '✦' }} {{ $syn['name'] }}
                                        </span>
                                    @empty
                                        <span class="text-sm italic text-ink-muted">Aucune synergie directe</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="button" wire:click="adminSpawnCustom(false)" class="btn-brass flex-1">
                                    Révélation Directe
                                </button>
                                <button type="button" wire:click="adminSpawnCustom(true)" class="btn-wax flex-1">
                                    Lancer la Roue Synchronisée
                                </button>
                            </div>
                        </div>

                        {{-- Aperçu Live via la vraie Carte Universelle --}}
                        <div class="lg:col-span-5 flex flex-col items-center gap-2">
                            <span class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">
                                Aperçu Live · Carte Universelle
                            </span>
                            <x-folio-card :card="$this->adminPreviewCard" :showOwnerActions="false" folioKey="admin-forge-preview" />
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- En-tête & Cartouches Énergie / Pity --}}
    <div class="codex-panel p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-xl sm:text-2xl text-ink tracking-wide">
                La Roue du Destin
            </h1>
            <p class="text-sm text-ink-sepia">
                Scellez une race, une arme et un pouvoir pour invoquer un combattant.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Charges + Chrono Live --}}
            <div class="codex-well px-3.5 py-2 flex items-center gap-2.5">
                <span class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">Charges :</span>
                @if ($isAdmin)
                    <span class="font-display font-bold text-sm text-brass-dark tabular-nums">∞ Illimité</span>
                @else
                    <div class="flex items-center gap-1" aria-label="{{ $spinCharges }} sur {{ $maxCharges }} charges">
                        @for ($i = 1; $i <= $maxCharges; $i++)
                            <svg class="w-4 h-4 {{ $i <= $spinCharges ? 'text-wax' : 'text-ink-muted/30' }}" viewBox="0 0 18 22" aria-hidden="true">
                                <path d="M9 1.5 C9 1.5 2.5 10 2.5 14.2 C2.5 17.8 5.4 20.5 9 20.5 C12.6 20.5 15.5 17.8 15.5 14.2 C15.5 10 9 1.5 9 1.5 Z" fill="currentColor"/>
                            </svg>
                        @endfor
                        <span class="font-display text-xs font-bold text-ink tabular-nums ml-1">({{ $spinCharges }}/{{ $maxCharges }})</span>
                    </div>

                    @if ($spinCharges < $maxCharges)
                        <div
                            wire:key="spin-timer-{{ $spinCharges }}-{{ $nextRegenSeconds }}"
                            x-data="{
                                remaining: {{ (int) $nextRegenSeconds }},
                                interval: null,
                                formatTime() {
                                    const s = Math.max(0, parseInt(this.remaining, 10) || 0);
                                    const m = Math.floor(s / 60);
                                    const sec = s % 60;
                                    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
                                },
                                init() {
                                    this.interval = setInterval(() => {
                                        if (this.remaining > 0) {
                                            this.remaining--;
                                        } else {
                                            clearInterval(this.interval);
                                            $wire.call('refreshState');
                                        }
                                    }, 1000);
                                },
                                destroy() {
                                    if (this.interval) clearInterval(this.interval);
                                }
                            }"
                            class="px-2 py-0.5 rounded-full bg-parchment-bright border border-brass-dark/40 font-display text-xs font-bold text-ink-sepia tabular-nums"
                        >
                            +1 dans <strong x-text="formatTime()" class="text-ink">{{ sprintf('%02d:%02d', floor($nextRegenSeconds / 60), $nextRegenSeconds % 60) }}</strong>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Jauge de Chance (Pity 10/10) --}}
            <div class="codex-well px-3.5 py-2 flex items-center gap-2.5">
                <span class="font-display text-xs font-bold uppercase tracking-wider text-ink-sepia">Chance :</span>
                <div class="w-20 h-2 rounded-full bg-ink-sepia/15 overflow-hidden border border-brass-dark/30">
                    <div
                        class="h-full rounded-full transition-all duration-500 {{ $pityCounter >= $pityMax ? 'bg-gradient-to-r from-brass-dark via-brass to-brass-light animate-pulse' : 'bg-gradient-to-r from-wax to-brass' }}"
                        style="width: {{ min(100, ($pityCounter / $pityMax) * 100) }}%"
                    ></div>
                </div>
                <span class="font-display text-xs font-bold text-brass-dark tabular-nums">{{ $pityCounter }}/{{ $pityMax }}</span>
                @if ($pityCounter >= $pityMax)
                    <span class="px-2 py-0.5 rounded-full bg-brass/25 border border-brass-dark/50 font-display text-[11px] font-bold text-brass-dark">
                        ✦ SUPER SPIN
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Scène Principale : Volvelle 3 Anneaux (gauche) + Rituel & Carte Universelle (droite) --}}
    <div
        x-data="codexVolvelle({
            wheels: @js($this->wheels),
            icons: @js($this->volvelleIcons),
            pigments: @js($this->volvellePigments),
            initialRotations: @js($wheelRotations),
            initialSteps: @js($initialSteps),
        })"
        class="invocation-grid"
    >
        {{-- Colonne Gauche : Volvelle SVG 3 Anneaux + Sceau Central + Ticket d'Urgence --}}
        <div class="volvelle-column">
            <div class="volvelle-stage">
                <svg class="volvelle-pointer" viewBox="0 0 32 40" aria-hidden="true">
                    <polygon points="16,38 3,6 16,13 29,6" fill="#C49A3A" stroke="#3B2A10" stroke-width="1.8"/>
                    <circle cx="16" cy="10" r="3.4" fill="#F5E4B8" stroke="#3B2A10" stroke-width="1.3"/>
                </svg>

                <div wire:ignore class="w-full h-full">
                    <svg class="volvelle-svg" viewBox="0 0 500 500" role="img" aria-label="Volvelle à trois anneaux : Races, Armes, Pouvoirs">
                        <defs>
                            <radialGradient id="volvelleRim" cx="50%" cy="50%" r="50%">
                                <stop offset="86%" stop-color="#EBE0C8"/>
                                <stop offset="96%" stop-color="#C8B48A"/>
                                <stop offset="100%" stop-color="#7D6239"/>
                            </radialGradient>
                            <filter id="ringGlowFilter" x="-15%" y="-15%" width="130%" height="130%">
                                <feGaussianBlur stdDeviation="3.5" result="blur"/>
                                <feMerge>
                                    <feMergeNode in="blur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                            <clipPath id="volvelle-clip-25" clipPathUnits="userSpaceOnUse"><circle cx="0" cy="0" r="12.5"/></clipPath>
                            <clipPath id="volvelle-clip-30" clipPathUnits="userSpaceOnUse"><circle cx="0" cy="0" r="15"/></clipPath>
                            <clipPath id="volvelle-clip-34" clipPathUnits="userSpaceOnUse"><circle cx="0" cy="0" r="17"/></clipPath>
                        </defs>

                        <circle cx="250" cy="250" r="245" fill="url(#volvelleRim)" stroke="#4A3618" stroke-width="3"/>

                        <g x-ref="ringPowers" class="volvelle-ring volvelle-ring--powers"></g>
                        <circle class="volvelle-ring-glow" :class="{ 'is-active-ring': activeRing === 'power' }" cx="250" cy="250" r="213" fill="none" stroke="#F0CC6B" stroke-width="58" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                        <circle cx="250" cy="250" r="184" fill="none" stroke="#4A3618" stroke-width="2.4"/>

                        <g x-ref="ringWeapons" class="volvelle-ring volvelle-ring--weapons"></g>
                        <circle class="volvelle-ring-glow" :class="{ 'is-active-ring': activeRing === 'weapon' }" cx="250" cy="250" r="155" fill="none" stroke="#F0CC6B" stroke-width="58" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                        <circle cx="250" cy="250" r="126" fill="none" stroke="#4A3618" stroke-width="2.4"/>

                        <g x-ref="ringRaces" class="volvelle-ring volvelle-ring--races"></g>
                        <circle class="volvelle-ring-glow" :class="{ 'is-active-ring': activeRing === 'race' }" cx="250" cy="250" r="96" fill="none" stroke="#F0CC6B" stroke-width="60" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                        <circle cx="250" cy="250" r="66" fill="#D9CBA8" stroke="#4A3618" stroke-width="2.6"/>
                    </svg>
                </div>

                {{-- Sceau Central 1:1 --}}
                <div class="volvelle-center-trigger">
                    <button
                        type="button"
                        wire:click="spin"
                        wire:loading.attr="disabled"
                        @disabled((! $isAdmin && $spinCharges <= 0) || ! empty($revealedCharacter) || $isSpinning)
                        class="btn-wax-seal {{ $pityCounter >= $pityMax ? 'btn-wax-seal--gold' : '' }}"
                        title="{{ ! empty($revealedCharacter) ? 'Finalisez le tirage ci-dessous' : 'Lancer la Roue' }}"
                    >
                        <span wire:loading.remove wire:target="spin" class="flex flex-col items-center gap-0.5 leading-tight">
                            @if ($pityCounter >= $pityMax)
                                <span>Super Spin !</span>
                            @else
                                <span>Lancer la Roue</span>
                            @endif
                            <small class="text-[10px] opacity-90 font-semibold normal-case tracking-normal">
                                @if ($isAdmin)
                                    ∞ Illimité
                                @elseif ($spinCharges > 0)
                                    1 charge
                                @else
                                    Recharge en cours
                                @endif
                            </small>
                        </span>
                        <span wire:loading wire:target="spin" class="text-[11px]">
                            Tirage...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Ticket d'Urgence si 0 charge --}}
            @if (! $isAdmin && $spinCharges <= 0 && empty($revealedCharacter) && ! $isSpinning)
                <div class="w-full max-w-sm flex flex-col items-center gap-1.5 pt-1">
                    @if ($emergencyCost !== null)
                        <button
                            type="button"
                            wire:click="buyEmergencySpin"
                            wire:loading.attr="disabled"
                            @disabled($coins < $emergencyCost)
                            class="btn-brass btn-brass--sm w-full"
                        >
                            <span wire:loading.remove wire:target="buyEmergencySpin">🎟️ Ticket d'Urgence : +1 charge ({{ $emergencyCost }} 🪙)</span>
                            <span wire:loading wire:target="buyEmergencySpin">Achat en cours...</span>
                        </button>
                        <span class="text-xs font-display font-semibold text-ink-sepia">
                            @if ($coins < $emergencyCost)
                                Pas assez de pièces ({{ $coins }}/{{ $emergencyCost }} 🪙)
                            @else
                                {{ $emergencyRemaining }} achat{{ $emergencyRemaining > 1 ? 's' : '' }} restant{{ $emergencyRemaining > 1 ? 's' : '' }} aujourd'hui
                            @endif
                        </span>
                    @else
                        <span class="text-xs font-display font-semibold text-ink-muted">
                            🎟️ Tickets d'Urgence épuisés pour aujourd'hui
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Colonne Droite : 3 Pilules Séquentielles + Scène de Révélation --}}
        <div class="flex flex-col gap-4">
            <ol class="sequence-steps" aria-label="Étapes du tirage">
                <li class="seq-pill" :class="{ 'is-active': activeRing === 'race', 'is-locked': Boolean(stepState.race) }">
                    <span class="seq-pill-num">1</span>
                    <span class="seq-pill-icon" x-html="stepState.race ? stepIconHtml('races', stepState.race.id, stepState.race.name) : '?'">?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Race</span>
                        <strong class="seq-pill-value" x-text="stepState.race ? stepState.race.name : '—'">—</strong>
                    </span>
                </li>

                <li class="seq-pill" :class="{ 'is-active': activeRing === 'weapon', 'is-locked': Boolean(stepState.weapon) }">
                    <span class="seq-pill-num">2</span>
                    <span class="seq-pill-icon" x-html="stepState.weapon ? stepIconHtml('weapons', stepState.weapon.id, stepState.weapon.name) : '?'">?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Arme</span>
                        <strong class="seq-pill-value" x-text="stepState.weapon ? stepState.weapon.name : '—'">—</strong>
                    </span>
                </li>

                <li class="seq-pill" :class="{ 'is-active': activeRing === 'power', 'is-locked': Boolean(stepState.power) }" :style="stepState.power ? '--step-accent:' + stepState.power.pigment_hex : ''">
                    <span class="seq-pill-num">3</span>
                    <span class="seq-pill-icon" x-html="stepState.power ? stepIconHtml('powers', stepState.power.id, stepState.power.name) : '?'">?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Pouvoir</span>
                        <strong class="seq-pill-value" x-text="stepState.power ? stepState.power.name : '—'">—</strong>
                    </span>
                </li>
            </ol>

            <div class="ritual-stage" aria-live="polite">
                @if ($revealedCharacter && ! $isSpinning)
                    <div class="w-full flex flex-col items-center gap-4">
                        <span class="px-3.5 py-1 rounded-full bg-brass/20 border border-brass-dark/45 font-display text-xs font-bold uppercase tracking-wider text-brass-dark">
                            Finalisez le tirage ci-dessous
                        </span>

                        <x-folio-card
                            :card="$revealedCharacter"
                            :showOwnerActions="false"
                            :animateStamp="true"
                            folioKey="wheel-reveal-{{ $revealedCharacter['id'] }}"
                        />

                        {{-- Wrapper Externe : Actions Garder / Recycler --}}
                        <div class="flex flex-wrap items-center justify-center gap-3 w-full max-w-[316px]">
                            <button
                                type="button"
                                wire:click="keepCharacter"
                                wire:loading.attr="disabled"
                                class="btn-brass flex-1 whitespace-nowrap"
                            >
                                <span wire:loading.remove wire:target="keepCharacter">Garder</span>
                                <span wire:loading wire:target="keepCharacter">Ajout...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="recycleCharacter"
                                wire:loading.attr="disabled"
                                class="btn-cartouche flex-1 whitespace-nowrap tabular-nums"
                            >
                                <span wire:loading.remove wire:target="recycleCharacter">Recycler (+{{ $revealedCharacter['recycle_value'] }} 🪙)</span>
                                <span wire:loading wire:target="recycleCharacter">Recyclage...</span>
                            </button>
                        </div>
                    </div>
                @elseif ($isSpinning)
                    <div
                        wire:key="volvelle-spinning-guard-{{ $wheelRotations['power'] }}"
                        x-data="{
                            fallbackTimer: null,
                            init() {
                                this.fallbackTimer = setTimeout(() => {
                                    $wire.call('finishSpin');
                                }, 5600);
                            },
                            destroy() {
                                if (this.fallbackTimer) clearTimeout(this.fallbackTimer);
                            }
                        }"
                        class="ritual-idle"
                    >
                        <svg class="ritual-idle-emblem animate-spin-slow" viewBox="0 0 120 120" aria-hidden="true">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
                            <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.2"/>
                            <polygon points="60,14 68,52 106,60 68,68 60,106 52,68 14,60 52,52" fill="none" stroke="currentColor" stroke-width="1.3"/>
                        </svg>
                        <p class="ritual-cue">Les roues tournent...</p>
                        <p class="text-sm text-ink-sepia italic">L'encre et les sceaux gravent votre combattant.</p>
                    </div>
                @else
                    <div class="ritual-idle">
                        <svg class="ritual-idle-emblem" viewBox="0 0 120 120" aria-hidden="true">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
                            <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.2"/>
                            <polygon points="60,14 68,52 106,60 68,68 60,106 52,68 14,60 52,52" fill="none" stroke="currentColor" stroke-width="1.3"/>
                        </svg>
                        <p class="ritual-cue">Pressez le sceau central</p>
                        <p class="text-sm text-ink-sepia max-w-xs">
                            Tournez la Volvelle pour invoquer un héros, puis cliquez sur sa carte pour révéler ses statistiques et capacités.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
