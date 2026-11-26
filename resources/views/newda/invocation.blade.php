@extends('newda.layout')

@section('content')
<section class="page-screen page-invocation" aria-label="Tirage par Volvelle">
    <div class="invocation-grid">
        <div class="volvelle-column">
            <div class="volvelle-stage" id="volvelle-stage">
                <svg class="volvelle-pointer" viewBox="0 0 32 40" aria-hidden="true">
                    <polygon points="16,38 3,6 16,13 29,6" fill="#C49A3A" stroke="#3B2A10" stroke-width="1.8"/>
                    <circle cx="16" cy="10" r="3.4" fill="#F5E4B8" stroke="#3B2A10" stroke-width="1.3"/>
                </svg>

                <svg id="volvelle-svg" class="volvelle-svg" viewBox="0 0 500 500" role="img" aria-label="Volvelle à trois anneaux : Races, Armes, Pouvoirs">
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
                        <clipPath id="volvelle-clip-25" clipPathUnits="userSpaceOnUse">
                            <circle cx="0" cy="0" r="12.5"/>
                        </clipPath>
                        <clipPath id="volvelle-clip-30" clipPathUnits="userSpaceOnUse">
                            <circle cx="0" cy="0" r="15"/>
                        </clipPath>
                        <clipPath id="volvelle-clip-34" clipPathUnits="userSpaceOnUse">
                            <circle cx="0" cy="0" r="17"/>
                        </clipPath>
                    </defs>

                    <circle cx="250" cy="250" r="245" fill="url(#volvelleRim)" stroke="#4A3618" stroke-width="3"/>

                    <g id="ring-powers" class="volvelle-ring volvelle-ring--powers"></g>
                    <circle id="ring-glow-powers" class="volvelle-ring-glow" cx="250" cy="250" r="213" fill="none" stroke="#F0CC6B" stroke-width="58" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                    <circle cx="250" cy="250" r="184" fill="none" stroke="#4A3618" stroke-width="2.4"/>

                    <g id="ring-weapons" class="volvelle-ring volvelle-ring--weapons"></g>
                    <circle id="ring-glow-weapons" class="volvelle-ring-glow" cx="250" cy="250" r="155" fill="none" stroke="#F0CC6B" stroke-width="58" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                    <circle cx="250" cy="250" r="126" fill="none" stroke="#4A3618" stroke-width="2.4"/>

                    <g id="ring-races" class="volvelle-ring volvelle-ring--races"></g>
                    <circle id="ring-glow-races" class="volvelle-ring-glow" cx="250" cy="250" r="96" fill="none" stroke="#F0CC6B" stroke-width="60" filter="url(#ringGlowFilter)" opacity="0" pointer-events="none"/>
                    <circle cx="250" cy="250" r="66" fill="#D9CBA8" stroke="#4A3618" stroke-width="2.6"/>
                </svg>

                <div class="volvelle-center-trigger">
                    <button type="button" id="btn-invoke-seal" class="btn-wax-seal" aria-label="Invoquer">
                        <span>Invoquer</span>
                    </button>
                </div>
            </div>

            <div class="emergency-bar" id="emergency-spin-bar" hidden>
                <button type="button" class="btn-brass btn-brass--sm" id="btn-emergency-spin">
                    <svg class="btn-icon" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M10 1.5 C10 1.5 3.5 10 3.5 14 C3.5 17.3 6.4 19.5 10 19.5 C13.6 19.5 16.5 17.3 16.5 14 C16.5 10 10 1.5 10 1.5 Z" fill="currentColor"/>
                    </svg>
                    <span id="emergency-spin-label">Jeton d'Urgence · {{ $workshop['emergency_spin']['costs'][0] }} écus</span>
                </button>
            </div>
        </div>

        <div class="ritual-column">
            <ol class="sequence-steps" aria-label="Étapes du tirage">
                <li class="seq-pill" id="step-race" data-step="race">
                    <span class="seq-pill-num">1</span>
                    <span class="seq-pill-icon" data-step-icon>?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Race</span>
                        <strong class="seq-pill-value" data-step-value>—</strong>
                    </span>
                </li>
                <li class="seq-pill" id="step-weapon" data-step="weapon">
                    <span class="seq-pill-num">2</span>
                    <span class="seq-pill-icon" data-step-icon>?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Arme</span>
                        <strong class="seq-pill-value" data-step-value>—</strong>
                    </span>
                </li>
                <li class="seq-pill" id="step-power" data-step="power">
                    <span class="seq-pill-num">3</span>
                    <span class="seq-pill-icon" data-step-icon>?</span>
                    <span class="seq-pill-body">
                        <span class="seq-pill-label">Pouvoir</span>
                        <strong class="seq-pill-value" data-step-value>—</strong>
                    </span>
                </li>
            </ol>

            <div class="ritual-stage" id="ritual-stage" aria-live="polite">
                <div id="ritual-content" class="ritual-idle">
                    <svg class="ritual-idle-emblem" viewBox="0 0 120 120" aria-hidden="true">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
                        <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.2"/>
                        <polygon points="60,14 68,52 106,60 68,68 60,106 52,68 14,60 52,52" fill="none" stroke="currentColor" stroke-width="1.3"/>
                    </svg>
                    <p class="ritual-cue">Pressez le sceau</p>
                </div>

                <div class="ritual-actions" id="ritual-actions" hidden>
                    <button type="button" class="btn-brass" id="btn-seal-folio">
                        Sceller
                    </button>
                    <button type="button" class="btn-cartouche" id="btn-transmute-folio">
                        Transmuter (+0)
                    </button>
                </div>
            </div>

            <p class="ritual-status" id="ritual-status" aria-live="polite"></p>
        </div>
    </div>
</section>
@endsection
