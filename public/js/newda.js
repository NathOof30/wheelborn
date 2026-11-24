(() => {
    'use strict';

    const bootstrapEl = document.getElementById('codex-bootstrap-data');
    if (!bootstrapEl) {
        return;
    }

    const DATA = JSON.parse(bootstrapEl.textContent || '{}');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const indexById = (list, key = 'id') => {
        const map = {};
        (list || []).forEach((item) => {
            map[item[key]] = item;
        });
        return map;
    };

    const RACES = DATA.races || [];
    const WEAPONS = DATA.weapons || [];
    const POWERS = DATA.powers || [];
    const RARITIES = DATA.rarities || [];
    const SYNERGIES = DATA.synergies || {};
    const PAPER_DOLL_MAP = DATA.paperDollMap || {};
    const CONFIG = DATA.config || {};

    const RACE_BY_ID = indexById(RACES);
    const WEAPON_BY_ID = indexById(WEAPONS);
    const POWER_BY_ID = indexById(POWERS);
    const RARITY_BY_VAL = indexById(RARITIES, 'value');

    const NAME_PREFIXES = [
        'Var', 'Zor', 'Lyr', 'Kor', 'Nym', 'Val', 'Ryn', 'Thal',
        'Drak', 'Zeph', 'Mal', 'Syr', 'Mor', 'Ael', 'Tor', 'Vex',
        'Sol', 'Nyx', 'Ign', 'Cryo', 'Kael', 'Rha', 'Vol', 'Orn',
    ];
    const NAME_SUFFIXES = [
        'k', 'an', 'a', 'r', 'on', 'is', 'en', 'or',
        'ath', 'ys', 'mir', 'th', 'us', 'ix', 'el', 'ar',
    ];
    const ROBOT_PREFIXES = ['MK', 'Unit', 'Core', 'Nexus', 'Volt', 'Zero'];

    const state = {
        mode: 'showcase', // 'showcase' | 'connected'
        rarityFilter: 'all',
        humanOnlyPool: false,
        spinning: false,
        pendingFolio: null,
        sessionFolios: [...(DATA.showcaseFolios || [])],
        folioCounter: (DATA.showcaseFolios || []).length,
        hud: { ...(DATA.hud || {}) },
        rotations: { powers: 0, weapons: 0, races: 0 },
        ringsGeometry: { powers: [], weapons: [], races: [] },
        renderedLuckyRing: false,
        benchCosmos: false,
        benchHalo: true,
        duelRunning: false,
        duelTimers: [],
    };

    /* ======================================================================
       UTILITAIRES SÛRS & SVG
       ====================================================================== */

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

    const weightedPick = (items) => {
        const total = items.reduce((acc, item) => acc + Number(item.weight || 1), 0);
        let cursor = Math.random() * total;
        for (const item of items) {
            cursor -= Number(item.weight || 1);
            if (cursor <= 0) {
                return item;
            }
        }
        return items[0];
    };

    const generateName = (raceId) => {
        if (raceId === 'robot' && Math.random() > 0.35) {
            const pre = ROBOT_PREFIXES[randInt(0, ROBOT_PREFIXES.length - 1)];
            return `${pre}-${randInt(10, 99)}`;
        }
        const pre = NAME_PREFIXES[randInt(0, NAME_PREFIXES.length - 1)];
        const suf = NAME_SUFFIXES[randInt(0, NAME_SUFFIXES.length - 1)];
        const full = (pre + suf).toLowerCase();
        return full.charAt(0).toUpperCase() + full.slice(1);
    };

    const powerSigilSvg = (powerId, hex, className = 'codex-sigil-watermark') => {
        const shapes = {
            fire: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="0.9" stroke-dasharray="3 3"/><polygon points="60,10 70,42 104,42 76,62 86,96 60,76 34,96 44,62 16,42 50,42" fill="none" stroke="currentColor" stroke-width="1.3"/><polygon points="60,18 72,48 102,60 72,72 60,102 48,72 18,60 48,48" fill="none" stroke="currentColor" stroke-width="1.1"/>',
            poison: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="36" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="20" fill="none" stroke="currentColor" stroke-width="1.2" stroke-dasharray="5 3"/><path d="M60 12 C86 12,102 36,92 60 C82 84,54 96,36 82 C20 70,22 44,40 34 C56 25,74 36,72 54 C70 68,54 74,48 64" fill="none" stroke="currentColor" stroke-width="1.5"/>',
            ice: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,12 102,84 18,84" fill="none" stroke="currentColor" stroke-width="1.4"/><polygon points="60,108 18,36 102,36" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="14" fill="none" stroke="currentColor" stroke-width="1.2"/>',
            lightning: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="24" y="24" width="72" height="72" fill="none" stroke="currentColor" stroke-width="1.2"/><rect x="24" y="24" width="72" height="72" transform="rotate(45 60 60)" fill="none" stroke="currentColor" stroke-width="1.2"/><path d="M66 16 L46 58 L64 58 L52 104" fill="none" stroke="currentColor" stroke-width="1.8"/>',
            regeneration: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="44" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="76" cy="60" r="30" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M60 22 C72 40,72 80,60 98 C48 80,48 40,60 22 Z" fill="none" stroke="currentColor" stroke-width="1.3"/>',
            gravity: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(-24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><ellipse cx="60" cy="60" rx="48" ry="18" transform="rotate(24 60 60)" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="60" cy="60" r="16" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="60" cy="60" r="5" fill="currentColor"/>',
            teleportation: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="46" cy="52" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="74" cy="68" r="24" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M26 94 L94 26 M84 26 L94 26 L94 36" fill="none" stroke="currentColor" stroke-width="1.3"/>',
            invisibility: '<circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="1.3" stroke-dasharray="6 4"/><circle cx="60" cy="60" r="38" fill="none" stroke="currentColor" stroke-width="1.1" stroke-dasharray="3 4"/><path d="M18 60 Q60 26 102 60 Q60 94 18 60 Z" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="60" cy="60" r="11" fill="none" stroke="currentColor" stroke-width="1.3"/><line x1="24" y1="96" x2="96" y2="24" stroke="currentColor" stroke-width="1.2"/>',
        };
        return `<svg class="${className}" viewBox="0 0 120 120" style="color:${escapeHtml(hex)};" aria-hidden="true">${shapes[powerId] || shapes.invisibility}</svg>`;
    };

    const raceRuneSvg = (glyph) => {
        const shapes = {
            horns: '<path d="M12 10 C8 22,16 32,24 34 C32 32,40 22,36 10 C32 18,28 21,24 21 C20 21,16 18,12 10 Z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="24" cy="30" r="4" fill="none" stroke="currentColor" stroke-width="1.5"/>',
            gear: '<circle cx="24" cy="24" r="11" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="24" cy="24" r="4.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M24 6 V11 M24 37 V42 M6 24 H11 M37 24 H42 M11 11 L15 15 M33 33 L37 37 M37 11 L33 15 M15 33 L11 37" stroke="currentColor" stroke-width="2"/>',
            leaf: '<path d="M12 36 C12 18,24 10,36 10 C36 24,28 36,12 36 Z M12 36 L30 18" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            helix: '<path d="M14 10 C34 16,14 32,34 38 M34 10 C14 16,34 32,14 38" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            monolith: '<polygon points="24,8 36,16 34,38 14,38 12,16" fill="none" stroke="currentColor" stroke-width="1.8"/><line x1="24" y1="8" x2="24" y2="38" stroke="currentColor" stroke-width="1.2"/>',
            chalice: '<path d="M14 12 H34 L29 25 C28 28,20 28,19 25 Z M24 27 V38 M17 38 H31" fill="none" stroke="currentColor" stroke-width="1.7"/>',
            astrolabe: '<circle cx="24" cy="24" r="14" fill="none" stroke="currentColor" stroke-width="1.5"/><ellipse cx="24" cy="24" rx="14" ry="6" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="24" cy="24" r="2.5" fill="currentColor"/>',
            compass: '<circle cx="24" cy="24" r="14" fill="none" stroke="currentColor" stroke-width="1.5"/><polygon points="24,8 28,24 24,40 20,24" fill="none" stroke="currentColor" stroke-width="1.4"/>',
        };
        return `<svg class="heraldic-rune" viewBox="0 0 48 48" aria-hidden="true">${shapes[glyph] || shapes.compass}</svg>`;
    };

    const renderPortraitHtml = (folio) => {
        const sigil = powerSigilSvg(folio.power_id, folio.power_pigment_hex);
        if (folio.has_portrait && folio.layers && folio.layers.race) {
            const haloClass = folio.layers.is_pale_aura ? 'is-contrast-halo' : '';
            const weaponImg = folio.layers.weapon
                ? `<img class="codex-doll-layer codex-doll-layer--weapon" src="${escapeHtml(folio.layers.weapon)}" alt="${escapeHtml(folio.weapon_name)}" loading="lazy">`
                : '';
            const auraImg = folio.layers.aura
                ? `<img class="codex-doll-layer codex-doll-layer--aura ${folio.layers.is_pale_aura ? 'is-pale-aura' : ''}" src="${escapeHtml(folio.layers.aura)}" alt="Aura ${escapeHtml(folio.power_name)}" loading="lazy">`
                : '';
            return `
                <div class="codex-portrait-frame">
                    ${sigil}
                    <div class="codex-doll ${haloClass}" style="--halo-pigment:${escapeHtml(folio.power_pigment_soft)};">
                        <div class="codex-doll-halo"></div>
                        <img class="codex-doll-layer codex-doll-layer--race" src="${escapeHtml(folio.layers.race)}" alt="${escapeHtml(folio.race_name)}" loading="lazy">
                        ${weaponImg}
                        ${auraImg}
                    </div>
                </div>
            `;
        }

        return `
            <div class="codex-portrait-frame">
                ${sigil}
                <div class="heraldic-blason" data-blason-race="${escapeHtml(folio.race_id)}">
                    <span class="heraldic-initial">${escapeHtml(folio.race_initial)}</span>
                    ${raceRuneSvg(folio.race_glyph)}
                    <span class="heraldic-caption">Blason ${escapeHtml(folio.race_name)}</span>
                    <span class="heraldic-origin">${escapeHtml(folio.weapon_name)} · ${escapeHtml(folio.power_name)}</span>
                </div>
            </div>
        `;
    };

    const renderFolioCardHtml = (folio) => {
        const favBadge = folio.is_favorite ? '<span class="folio-badge-mini" title="Table d\'Honneur">★ Honneur</span>' : '';
        const lockBadge = folio.is_locked ? '<span class="folio-badge-mini folio-badge-mini--lock" title="Sceau de Garde">⛨ Garde</span>' : '';
        const synergies = folio.synergies || [];
        const featuredSyn = synergies.find((s) => Boolean(s.is_unstable)) || synergies[0] || null;
        const extraSynCount = Math.max(0, synergies.length - 1);
        const synStrip = featuredSyn
            ? `<div class="folio-synergy-strip ${featuredSyn.is_unstable ? 'folio-synergy-strip--unstable' : ''}">
                <span>${featuredSyn.is_unstable ? 'Fissure Instable' : 'Accord de Sceau'}</span>
                <span>${escapeHtml(featuredSyn.name)}${extraSynCount > 0 ? ` (+${extraSynCount})` : ''}</span>
               </div>`
            : '';
        const synVerso = synergies.length > 0
            ? `<div class="verso-section">${synergies.map((s) => `
                <div><strong style="color:${s.is_unstable ? '#8A1A14' : '#8A6E20'};">${s.is_unstable ? 'Fissure Arcanique' : 'Accord de Sceau'} · ${escapeHtml(s.name)} :</strong> ${escapeHtml(s.description)}</div>
              `).join('')}</div>`
            : '';

        const hpPct = Math.min(100, Math.round((folio.hp / 140) * 100));

        return `
            <article class="folio-card" data-folio-id="${escapeHtml(folio.id)}" data-rarity="${escapeHtml(folio.rarity)}">
                <div class="folio-card-inner">
                    <div class="folio-face folio-face--front">
                        <div class="folio-topbar">
                            <div class="folio-ref-group">
                                <span class="folio-ref">FOLIO N° ${escapeHtml(folio.folio_number)}</span>
                                ${favBadge}${lockBadge}
                            </div>
                            <span class="power-medallion tabular-nums" title="Power Score">${escapeHtml(folio.power_score)}</span>
                        </div>
                        <div class="folio-title-row">
                            <span class="folio-lettrine" aria-hidden="true">${escapeHtml(folio.initial)}</span>
                            <div>
                                <h3 class="folio-name">${escapeHtml(folio.name)}</h3>
                                <div class="folio-sub">${escapeHtml(folio.race_name)} · ${escapeHtml(folio.weapon_name)}</div>
                            </div>
                        </div>
                        ${renderPortraitHtml(folio)}
                        <dl class="scribe-list" style="margin-top:8px;">
                            <div class="scribe-row"><dt>Race</dt><span class="scribe-dots"></span><dd>${escapeHtml(folio.race_name)}</dd></div>
                            <div class="scribe-row"><dt>Arme</dt><span class="scribe-dots"></span><dd>${escapeHtml(folio.weapon_name)}</dd></div>
                            <div class="scribe-row"><dt>Pouvoir</dt><span class="scribe-dots"></span><dd style="color:${escapeHtml(folio.power_pigment_hex)};">${escapeHtml(folio.power_name)}</dd></div>
                        </dl>
                        ${synStrip}
                        <div class="folio-footer">
                            <p class="folio-note">${escapeHtml(folio.note)}</p>
                            <span class="rarity-wax-chip" style="--seal-hex:${escapeHtml(folio.seal_hex)};" title="${escapeHtml(folio.seal_name)}">${escapeHtml(folio.rarity_label)}</span>
                        </div>
                        <button type="button" class="folio-flip-btn" data-flip-folio>Consulter le Verso ↻</button>
                    </div>
                    <div class="folio-face folio-face--back">
                        <div class="folio-topbar">
                            <span class="folio-ref">VERSO · FOLIO N° ${escapeHtml(folio.folio_number)}</span>
                            <span class="power-medallion tabular-nums">${escapeHtml(folio.power_score)}</span>
                        </div>
                        <div class="ink-stat-list tabular-nums">
                            <div>
                                <div class="ink-stat-head"><span>Vitalité (PV)</span><span>${escapeHtml(folio.hp)} / 140</span></div>
                                <div class="ink-meter"><div class="ink-meter-fill ink-meter-fill--hp" style="width:${hpPct}%;"></div></div>
                            </div>
                            <div>
                                <div class="ink-stat-head"><span>Force</span><span>${escapeHtml(folio.strength)} / 100</span></div>
                                <div class="ink-meter"><div class="ink-meter-fill" style="width:${Math.min(100, folio.strength)}%;"></div></div>
                            </div>
                            <div>
                                <div class="ink-stat-head"><span>Vitesse</span><span>${escapeHtml(folio.speed)} / 100</span></div>
                                <div class="ink-meter"><div class="ink-meter-fill" style="width:${Math.min(100, folio.speed)}%;"></div></div>
                            </div>
                            <div>
                                <div class="ink-stat-head"><span>Défense</span><span>${escapeHtml(folio.defense)} / 100</span></div>
                                <div class="ink-meter"><div class="ink-meter-fill" style="width:${Math.min(100, folio.defense)}%;"></div></div>
                            </div>
                        </div>
                        <div class="verso-section">
                            <strong>Passifs :</strong> ${escapeHtml(folio.race_passive)} — ${escapeHtml(folio.race_passive_desc)}
                            <br>${escapeHtml(folio.weapon_passive)} — ${escapeHtml(folio.weapon_passive_desc)}
                        </div>
                        ${synVerso}
                        <dl class="scribe-list" style="margin-top:auto;">
                            <div class="scribe-row"><dt>Transmutation</dt><span class="scribe-dots"></span><dd class="tabular-nums">+${escapeHtml(folio.recycle_value)} écus</dd></div>
                            <div class="scribe-row"><dt>Cachet</dt><span class="scribe-dots"></span><dd>${escapeHtml(folio.seal_name)}</dd></div>
                        </dl>
                        <button type="button" class="folio-flip-btn" data-flip-folio>Retourner au Recto ↺</button>
                    </div>
                </div>
            </article>
        `;
    };

    /* ======================================================================
       HUD MANUSCRIT (Mises à jour bac à sable)
       ====================================================================== */

    const renderHud = () => {
        const inkDropsEl = document.getElementById('hud-ink-drops');
        const inkTextEl = document.getElementById('hud-ink-text');
        const pityRunesEl = document.getElementById('hud-pity-runes');
        const pityTextEl = document.getElementById('hud-pity-text');
        const coinsEl = document.getElementById('hud-coins');
        const sealBtn = document.getElementById('btn-invoke-seal');

        const maxInk = Number(state.hud.spin_max || 3);
        const curInk = Math.max(0, Number(state.hud.spin_charges || 0));
        const pityMax = Number(state.hud.pity_threshold || 10);
        const pityCur = Math.min(pityMax, Math.max(0, Number(state.hud.pity_counter || 0)));
        const isLuckyReady = pityCur >= pityMax;

        if (inkDropsEl) {
            let html = '';
            for (let i = 0; i < maxInk; i++) {
                html += `<svg class="ink-drop ${i >= curInk ? 'is-spent' : ''}" viewBox="0 0 16 20"><path d="M8 1.5 C8 1.5 2 9.5 2 13.5 C2 16.8 4.7 19 8 19 C11.3 19 14 16.8 14 13.5 C14 9.5 8 1.5 8 1.5 Z"/></svg>`;
            }
            inkDropsEl.innerHTML = html;
        }
        if (inkTextEl) {
            inkTextEl.textContent = `${curInk}/${maxInk}`;
        }
        if (pityRunesEl) {
            let html = '';
            for (let i = 0; i < pityMax; i++) {
                const cls = isLuckyReady ? 'is-ready' : (i < pityCur ? 'is-lit' : '');
                html += `<span class="pity-pip ${cls}"></span>`;
            }
            pityRunesEl.innerHTML = html;
        }
        if (pityTextEl) {
            pityTextEl.textContent = `${pityCur}/${pityMax}`;
        }
        if (coinsEl) {
            coinsEl.textContent = `${Number(state.hud.coins || 0).toLocaleString('fr-FR')} écus`;
        }
        if (sealBtn) {
            sealBtn.classList.toggle('btn-wax-seal--gold', isLuckyReady);
            sealBtn.innerHTML = isLuckyReady
                ? '<span>Grand Rituel</span><span style="font-size:0.62rem;">✦ 10/10 ✦</span>'
                : '<span>Invoquer</span><span style="font-size:0.62rem;opacity:0.85;">✦ Sceau ✦</span>';
        }
    };

    /* ======================================================================
       VOLVELLE SVG 3 ANNEAUX PROPORTIONNELS
       ====================================================================== */

    const polarToCartesian = (cx, cy, r, degFromTop) => {
        const rad = ((degFromTop - 90) * Math.PI) / 180;
        return {
            x: cx + r * Math.cos(rad),
            y: cy + r * Math.sin(rad),
        };
    };

    const annularSectorPath = (cx, cy, rInner, rOuter, startDeg, endDeg) => {
        const span = endDeg - startDeg;
        const largeArc = span > 180 ? 1 : 0;
        const p1 = polarToCartesian(cx, cy, rOuter, startDeg);
        const p2 = polarToCartesian(cx, cy, rOuter, endDeg);
        const p3 = polarToCartesian(cx, cy, rInner, endDeg);
        const p4 = polarToCartesian(cx, cy, rInner, startDeg);

        return [
            `M ${p1.x.toFixed(2)} ${p1.y.toFixed(2)}`,
            `A ${rOuter} ${rOuter} 0 ${largeArc} 1 ${p2.x.toFixed(2)} ${p2.y.toFixed(2)}`,
            `L ${p3.x.toFixed(2)} ${p3.y.toFixed(2)}`,
            `A ${rInner} ${rInner} 0 ${largeArc} 0 ${p4.x.toFixed(2)} ${p4.y.toFixed(2)}`,
            'Z',
        ].join(' ');
    };

    const drawRing = (groupEl, items, rInner, rOuter, options = {}) => {
        if (!groupEl) {
            return [];
        }
        const cx = 250;
        const cy = 250;
        const total = items.reduce((acc, item) => acc + Number(item.weight || 1), 0);
        let cursor = 0;
        const geometry = [];
        let svgMarkup = '';

        items.forEach((item, idx) => {
            const span = (Number(item.weight || 1) / total) * 360;
            const startDeg = cursor;
            const endDeg = cursor + span;
            const midDeg = startDeg + span / 2;
            cursor = endDeg;

            geometry.push({ id: item.id, startDeg, endDeg, midDeg });

            const path = annularSectorPath(cx, cy, rInner, rOuter, startDeg, endDeg);
            const fill = options.fillFor
                ? options.fillFor(item, idx)
                : (idx % 2 === 0 ? '#EFE4CC' : '#E0D1B0');
            const textColor = options.textFor ? options.textFor(item, idx) : '#3A2A1A';
            const rLabel = (rInner + rOuter) / 2;
            const labelPos = polarToCartesian(cx, cy, rLabel, midDeg);
            const labelText = span < 20 ? item.name.slice(0, 5) + '.' : item.name;
            const fontSize = span < 20 ? 8.2 : (options.fontSize || (span < 24 ? 8.8 : 10.5));

            svgMarkup += `<path d="${path}" fill="${fill}" stroke="#5A4530" stroke-width="1"/>`;
            svgMarkup += `
                <text
                    x="${labelPos.x.toFixed(1)}"
                    y="${labelPos.y.toFixed(1)}"
                    fill="${textColor}"
                    font-family="Cormorant Garamond, Georgia, serif"
                    font-size="${fontSize}"
                    font-weight="700"
                    text-anchor="middle"
                    dominant-baseline="central"
                    transform="rotate(${midDeg.toFixed(1)} ${labelPos.x.toFixed(1)} ${labelPos.y.toFixed(1)})"
                >${escapeHtml(labelText)}</text>
            `;
        });

        groupEl.innerHTML = svgMarkup;
        return geometry;
    };

    const isPityReady = () => Number(state.hud.pity_counter || 0) >= Number(state.hud.pity_threshold || 10);

    const getActivePowerPool = (forceLucky = null) => {
        const isLucky = forceLucky !== null ? forceLucky : isPityReady();
        return isLucky ? POWERS.filter((p) => p.tier !== 'common') : POWERS;
    };

    const drawPowerRing = (isLucky) => {
        state.renderedLuckyRing = Boolean(isLucky);
        state.ringsGeometry.powers = drawRing(
            document.getElementById('ring-powers'),
            getActivePowerPool(isLucky),
            184,
            242,
            {
                fillFor: (p) => p.pigment_hex,
                textFor: () => '#F5EDD8',
                fontSize: 10,
            }
        );
    };

    const drawAllVolvelleRings = () => {
        drawPowerRing(isPityReady());

        state.ringsGeometry.weapons = drawRing(
            document.getElementById('ring-weapons'),
            WEAPONS,
            126,
            184,
            {
                fillFor: (_w, i) => (i % 2 === 0 ? '#F2E7CE' : '#DFCFAF'),
                fontSize: 10.2,
            }
        );

        state.ringsGeometry.races = drawRing(
            document.getElementById('ring-races'),
            RACES,
            66,
            126,
            {
                fillFor: (r, i) => {
                    if (state.humanOnlyPool) {
                        return r.id === 'human' ? '#D8B860' : '#D5C6A5';
                    }
                    return i % 2 === 0 ? '#E8D9BA' : '#D9C8A2';
                },
                textFor: (r) => (state.humanOnlyPool && r.id !== 'human' ? '#8A7555' : '#3A2A1A'),
                fontSize: 9.2,
            }
        );
    };

    const setVolvelleControlsDisabled = (disabled) => {
        ['btn-invoke-seal', 'btn-pool-all', 'btn-pool-human', 'btn-charge-pity'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.disabled = disabled;
        });
    };

    const computeNextRotation = (currentRot, midDeg, fullSpins, clockwise = true) => {
        const targetMod = (360 - (midDeg % 360) + 360) % 360;
        const currentMod = ((currentRot % 360) + 360) % 360;
        if (clockwise) {
            const delta = (targetMod - currentMod + 360) % 360;
            return currentRot + fullSpins * 360 + delta;
        }
        const deltaBack = (currentMod - targetMod + 360) % 360;
        return currentRot - fullSpins * 360 - deltaBack;
    };

    /* ======================================================================
       MOTEUR SANDBOX DE TIRAGE (Fidèle à GameConfig + Table creuse PHP)
       ====================================================================== */

    const rollSandboxFolio = (isLuckySpin) => {
        const race = state.humanOnlyPool
            ? (RACE_BY_ID.human || RACES[0])
            : weightedPick(RACES);
        const weapon = weightedPick(WEAPONS);
        const power = weightedPick(getActivePowerPool());

        const synKey = `${race.id}:${weapon.id}:${power.id}`;
        const synergies = SYNERGIES[synKey] || [];
        const isUnstable = synergies.some((s) => Boolean(s.is_unstable));

        const base = CONFIG.baseStats || {
            hp_min: 55, hp_max: 75,
            strength_min: 45, strength_max: 70,
            speed_min: 40, speed_max: 68,
            defense_min: 42, defense_max: 68,
        };

        let hp = randInt(base.hp_min, base.hp_max) + (race.mods.hp || 0) + (weapon.mods.hp || 0);
        let strength = randInt(base.strength_min, base.strength_max) + (race.mods.strength || 0) + (weapon.mods.strength || 0);
        let speed = randInt(base.speed_min, base.speed_max) + (race.mods.speed || 0) + (weapon.mods.speed || 0);
        let defense = randInt(base.defense_min, base.defense_max) + (race.mods.defense || 0) + (weapon.mods.defense || 0);

        if (race.is_mutant) {
            hp += randInt(-10, 20);
            strength += randInt(-10, 20);
            speed += randInt(-12, 18);
            defense += randInt(-10, 18);
        }

        synergies.forEach((syn) => {
            const eff = syn.effects || {};
            if (eff.strength_bonus) strength += eff.strength_bonus;
            if (eff.defense_bonus) defense += eff.defense_bonus;
            if (eff.speed_bonus) speed += eff.speed_bonus;
            if (eff.regen_bonus) hp += Math.round(eff.regen_bonus * 0.5);
        });

        hp = Math.max(30, Math.min(140, Math.round(hp)));
        strength = Math.max(15, Math.min(100, Math.round(strength)));
        speed = Math.max(10, Math.min(100, Math.round(speed)));
        defense = Math.max(10, Math.min(100, Math.round(defense)));

        const pw = CONFIG.powerWeights || { strength: 0.32, hp: 0.24, defense: 0.24, speed: 0.20, synergy_bonus: 3, unstable_bonus: 2 };
        const rawScore = (hp * pw.hp) + (strength * pw.strength) + (defense * pw.defense) + (speed * pw.speed)
            + (synergies.length * pw.synergy_bonus) + (isUnstable ? pw.unstable_bonus : 0);
        const powerScore = Math.max(20, Math.min(100, Math.round(rawScore)));

        const rWeights = isLuckySpin ? CONFIG.rarityWeightsLucky : CONFIG.rarityWeightsNormal;
        const rarityPool = Object.keys(rWeights || {}).map((k) => ({ id: k, weight: rWeights[k] })).filter((x) => x.weight > 0);
        let rarityKey = weightedPick(rarityPool).id;
        if (powerScore >= 86 && synergies.length >= 2 && rarityKey === 'common') rarityKey = 'rare';
        if (powerScore >= 95 && synergies.length >= 2 && rarityKey === 'rare') rarityKey = 'epic';

        const rarity = RARITY_BY_VAL[rarityKey] || RARITIES[0];
        const recycleValue = Math.max(35, Math.round(((powerScore * 1.2) + (synergies.length * 25)) * Number(rarity.recycle_multiplier || 1)));

        const humanLayers = race.id === 'human' && PAPER_DOLL_MAP[weapon.id] && PAPER_DOLL_MAP[weapon.id][power.id]
            ? PAPER_DOLL_MAP[weapon.id][power.id]
            : null;
        const hasPortrait = Boolean(humanLayers && humanLayers.race);
        const name = generateName(race.id);
        state.folioCounter += 1;

        return {
            id: `session-${Date.now()}-${state.folioCounter}`,
            folio_number: String(state.folioCounter).padStart(3, '0'),
            name,
            initial: name.charAt(0).toUpperCase(),
            race_id: race.id,
            race_name: race.name,
            race_gloss: race.codex_gloss,
            race_initial: race.codex_initial,
            race_glyph: race.codex_glyph,
            race_passive: race.passive,
            race_passive_desc: race.passive_desc,
            weapon_id: weapon.id,
            weapon_name: weapon.name,
            weapon_gloss: weapon.codex_gloss,
            weapon_passive: weapon.passive,
            weapon_passive_desc: weapon.passive_desc,
            power_id: power.id,
            power_name: power.name,
            power_tier: power.tier,
            power_tier_label: power.tier_label,
            power_pigment_name: power.pigment_name,
            power_pigment_hex: power.pigment_hex,
            power_pigment_soft: power.pigment_soft,
            rarity: rarity.value,
            rarity_label: rarity.label,
            seal_hex: rarity.seal_hex,
            seal_name: rarity.seal_name,
            hp,
            strength,
            speed,
            defense,
            power_score: powerScore,
            recycle_value: recycleValue,
            is_locked: false,
            is_favorite: false,
            is_unstable: isUnstable,
            synergies,
            note: isLuckySpin
                ? 'Invoqué lors du Grand Rituel (10/10) — les sceaux Communs furent écartés.'
                : `Imprimé à l'encre de ${power.pigment_name} sur le Sceau-Volvelle.`,
            has_portrait: hasPortrait,
            layers: hasPortrait
                ? humanLayers
                : { race: null, weapon: null, aura: null, is_invisibility: false, is_pale_aura: false },
        };
    };

    const runInvocationRitual = () => {
        if (state.spinning) {
            return;
        }

        const statusEl = document.getElementById('ritual-status');
        const contentEl = document.getElementById('ritual-content');
        const actionsEl = document.getElementById('ritual-actions');
        const sealBtn = document.getElementById('btn-invoke-seal');

        if (Number(state.hud.spin_charges || 0) <= 0) {
            if (statusEl) {
                statusEl.textContent = 'Votre encrier est à sec (0/3). Cliquez « Remplir l\'Encrier » pour poursuivre le test.';
            }
            return;
        }

        state.spinning = true;
        state.pendingFolio = null;
        if (actionsEl) actionsEl.hidden = true;
        setVolvelleControlsDisabled(true);

        const isLuckySpin = isPityReady();
        if (state.renderedLuckyRing !== isLuckySpin) {
            drawPowerRing(isLuckySpin);
        }
        state.hud.spin_charges = Math.max(0, Number(state.hud.spin_charges) - 1);

        const folio = rollSandboxFolio(isLuckySpin);

        // Étape 1 : Rotation décalée des 3 anneaux vers le secteur tiré
        const powerGeo = state.ringsGeometry.powers.find((g) => g.id === folio.power_id) || state.ringsGeometry.powers[0];
        const weaponGeo = state.ringsGeometry.weapons.find((g) => g.id === folio.weapon_id) || state.ringsGeometry.weapons[0];
        const raceGeo = state.ringsGeometry.races.find((g) => g.id === folio.race_id) || state.ringsGeometry.races[0];

        state.rotations.powers = computeNextRotation(state.rotations.powers, powerGeo.midDeg, 4, true);
        state.rotations.weapons = computeNextRotation(state.rotations.weapons, weaponGeo.midDeg, 3, false);
        state.rotations.races = computeNextRotation(state.rotations.races, raceGeo.midDeg, 2, true);

        const ringP = document.getElementById('ring-powers');
        const ringW = document.getElementById('ring-weapons');
        const ringR = document.getElementById('ring-races');
        if (ringP) ringP.style.transform = `rotate(${state.rotations.powers}deg)`;
        if (ringW) ringW.style.transform = `rotate(${state.rotations.weapons}deg)`;
        if (ringR) ringR.style.transform = `rotate(${state.rotations.races}deg)`;

        if (statusEl) {
            statusEl.textContent = isLuckySpin
                ? 'Grand Rituel en cours — Alignement des anneaux arcaniques…'
                : 'Alignement du Sceau-Volvelle…';
        }
        if (contentEl) {
            contentEl.innerHTML = `
                <div style="text-align:center;padding:18px 8px;">
                    ${powerSigilSvg(folio.power_id, folio.power_pigment_hex, 'ritual-sigil-trace')}
                    <p class="folio-note" style="font-size:0.94rem;">Traçage du sceau <strong>${escapeHtml(folio.power_name)}</strong> (${escapeHtml(folio.power_pigment_name)})…</p>
                </div>
            `;
        }

        const revealDelay = prefersReducedMotion ? 80 : 1850;
        const stampDelay = prefersReducedMotion ? 120 : 2350;

        window.setTimeout(() => {
            if (statusEl) {
                statusEl.textContent = 'Impression du Folio dans le vélin…';
            }
            const synBadges = (folio.synergies || []).map((s) => `
                <div class="folio-synergy-strip ${s.is_unstable ? 'folio-synergy-strip--unstable' : ''}" style="margin-top:8px;">
                    <span>${s.is_unstable ? 'Fissure Instable' : 'Accord de Sceau'}</span>
                    <span>${escapeHtml(s.name)}</span>
                </div>
            `).join('');

            if (contentEl) {
                contentEl.innerHTML = `
                    <div class="ritual-folio-reveal">
                        <div class="folio-topbar">
                            <span class="folio-ref">NOUVEAU FOLIO N° ${escapeHtml(folio.folio_number)}</span>
                            <span class="power-medallion tabular-nums" id="ritual-score-chip" style="opacity:0;">${escapeHtml(folio.power_score)}</span>
                        </div>
                        <div class="folio-title-row">
                            <span class="folio-lettrine">${escapeHtml(folio.initial)}</span>
                            <div>
                                <h3 class="folio-name">${escapeHtml(folio.name)}</h3>
                                <div class="folio-sub">${escapeHtml(folio.race_name)} · ${escapeHtml(folio.weapon_name)} · ${escapeHtml(folio.power_name)}</div>
                            </div>
                        </div>
                        ${renderPortraitHtml(folio)}
                        <dl class="scribe-list tabular-nums" style="margin-top:8px;">
                            <div class="scribe-row"><dt>PV / Force</dt><span class="scribe-dots"></span><dd>${folio.hp} PV · ${folio.strength} FOR</dd></div>
                            <div class="scribe-row"><dt>Vitesse / Défense</dt><span class="scribe-dots"></span><dd>${folio.speed} VIT · ${folio.defense} DEF</dd></div>
                        </dl>
                        ${synBadges}
                        <div class="folio-footer" style="margin-top:10px;">
                            <p class="folio-note">${escapeHtml(folio.note)}</p>
                            <span id="ritual-wax-chip" class="rarity-wax-chip" style="--seal-hex:${escapeHtml(folio.seal_hex)};opacity:0;">${escapeHtml(folio.rarity_label)}</span>
                        </div>
                    </div>
                `;
            }
        }, revealDelay);

        window.setTimeout(() => {
            const scoreChip = document.getElementById('ritual-score-chip');
            const waxChip = document.getElementById('ritual-wax-chip');
            if (scoreChip) {
                scoreChip.style.opacity = '1';
                scoreChip.classList.add('wax-stamp-drop');
            }
            if (waxChip) {
                waxChip.style.opacity = '1';
                waxChip.classList.add('wax-stamp-drop');
            }

            state.pendingFolio = folio;
            state.hud.pity_counter = isLuckySpin
                ? 0
                : Math.min(Number(state.hud.pity_threshold || 10), Number(state.hud.pity_counter || 0) + 1);
            renderHud();

            const transmuteBtn = document.getElementById('btn-transmute-folio');
            if (transmuteBtn) {
                transmuteBtn.textContent = `Transmuter (+${folio.recycle_value} écus)`;
            }
            if (actionsEl) actionsEl.hidden = false;
            if (statusEl) {
                const pityNote = isPityReady() ? ' · Grand Rituel (10/10) prêt pour la prochaine invocation !' : '';
                statusEl.textContent = `Cachet ${folio.rarity_label} apposé — Power Score ${folio.power_score}.${pityNote}`;
            }
            setVolvelleControlsDisabled(false);
            state.spinning = false;
        }, stampDelay);
    };

    /* ======================================================================
       BANC D'ESSAI PAPER DOLL (Chapitre III)
       ====================================================================== */

    const updateBench = () => {
        const weaponId = document.getElementById('bench-weapon')?.value || 'sword';
        const powerId = document.getElementById('bench-power')?.value || 'lightning';
        const rarityVal = document.getElementById('bench-rarity')?.value || 'epic';

        const power = POWER_BY_ID[powerId] || POWERS[0];
        const rarity = RARITY_BY_VAL[rarityVal] || RARITIES[0];
        const layers = (PAPER_DOLL_MAP[weaponId] && PAPER_DOLL_MAP[weaponId][powerId]) || {
            race: null, weapon: null, aura: null, is_invisibility: false, is_pale_aura: false,
        };

        const stageEl = document.getElementById('bench-stage');
        const sigilSlot = document.getElementById('bench-sigil-slot');
        const dollEl = document.getElementById('bench-doll');
        const imgRace = document.getElementById('bench-img-race');
        const imgWeapon = document.getElementById('bench-img-weapon');
        const imgAura = document.getElementById('bench-img-aura');
        const diagEl = document.getElementById('bench-diagnostic');

        if (stageEl) {
            stageEl.classList.toggle('is-cosmos', state.benchCosmos);
            stageEl.style.borderColor = state.benchCosmos ? '#334155' : rarity.seal_hex;
        }
        if (sigilSlot) {
            sigilSlot.innerHTML = powerSigilSvg(power.id, power.pigment_hex);
        }
        if (dollEl) {
            dollEl.style.setProperty('--halo-pigment', power.pigment_soft);
            dollEl.classList.toggle('is-contrast-halo', state.benchHalo && !state.benchCosmos && layers.is_pale_aura);
        }
        if (imgRace && layers.race) {
            imgRace.src = layers.race;
        }
        if (imgWeapon) {
            if (layers.weapon) {
                imgWeapon.src = layers.weapon;
                imgWeapon.hidden = false;
            } else {
                imgWeapon.hidden = true;
            }
        }
        if (imgAura) {
            if (layers.aura) {
                imgAura.src = layers.aura;
                imgAura.hidden = false;
                imgAura.classList.toggle('is-pale-aura', Boolean(layers.is_pale_aura));
            } else {
                imgAura.hidden = true;
            }
        }
        if (diagEl) {
            const parts = ['<code>human.webp</code>'];
            if (layers.weapon) {
                parts.push(`<code>${escapeHtml(weaponId)}.webp</code>`);
            } else if (layers.is_invisibility) {
                parts.push('<em>arme de base masquée (règle Invisibilité)</em>');
            } else {
                parts.push(`<em>${escapeHtml(weaponId)} sans asset WebP</em>`);
            }
            if (layers.aura) {
                parts.push(`<code>${escapeHtml(weaponId)}_${escapeHtml(powerId)}.webp</code>`);
            }
            const note = layers.is_pale_aura
                ? (state.benchHalo ? 'Aura pâle détectée : halo sépia + contour d\'encre actifs (sans mix-blend-screen).' : 'Halo contraste désactivé : comparez la perte de lisibilité sur vélin clair.')
                : (layers.is_invisibility ? 'Règle PaperDollService active : seul l\'outline translucide est affiché.' : `Rendu standard sur ${state.benchCosmos ? 'fond Cosmos sombre' : 'vélin Codex'} (cachet ${escapeHtml(rarity.label)}).`);
            diagEl.innerHTML = `<strong>Diagnostic PaperDollService :</strong> ${parts.join(' + ')}. ${note}`;
        }
    };

    /* ======================================================================
       GALERIE 3D & MODE CONNECTÉ LECTURE SEULE
       ====================================================================== */

    const getCurrentPool = () => {
        if (state.mode === 'connected' && DATA.connected) {
            return DATA.connected.folios || [];
        }
        return state.sessionFolios;
    };

    const renderGallery = () => {
        const gridEl = document.getElementById('folios-grid');
        const emptyEl = document.getElementById('gallery-empty-state');
        const countEl = document.getElementById('gallery-count-label');
        if (!gridEl) {
            return;
        }

        const pool = getCurrentPool();
        const filtered = state.rarityFilter === 'all'
            ? pool
            : pool.filter((f) => f.rarity === state.rarityFilter);

        gridEl.innerHTML = filtered.map(renderFolioCardHtml).join('');

        if (emptyEl) {
            const isEmpty = filtered.length === 0;
            emptyEl.hidden = !isEmpty;
            if (isEmpty) {
                const isFilteredOut = pool.length > 0 && state.rarityFilter !== 'all';
                const titleEl = document.getElementById('gallery-empty-title');
                const descEl = document.getElementById('gallery-empty-desc');
                const ctaEl = document.getElementById('gallery-empty-cta');
                const resetBtn = document.getElementById('gallery-reset-filter-btn');
                if (titleEl) {
                    titleEl.innerHTML = isFilteredOut
                        ? '<strong>Aucun Folio de cette rareté</strong> — Ce filtre ne renvoie aucune entrée dans le chapitre courant.'
                        : '<strong>Folio vierge</strong> — Aucune entité n\'a encore été scellée dans ce chapitre.';
                }
                if (descEl) {
                    descEl.textContent = isFilteredOut
                        ? 'Réinitialisez le filtre pour réafficher l\'ensemble des Folios consignés.'
                        : 'Pressez le Sceau-Volvelle au Chapitre II pour imprimer votre premier héros dans le Grimoire.';
                }
                if (ctaEl) ctaEl.hidden = isFilteredOut;
                if (resetBtn) resetBtn.hidden = !isFilteredOut;
            }
        }
        const showcaseToggleBtn = document.querySelector('[data-source-toggle="showcase"]');
        if (showcaseToggleBtn) {
            showcaseToggleBtn.textContent = `Mode Vitrine (${state.sessionFolios.length})`;
        }
        if (countEl) {
            const suffix = state.mode === 'connected' && DATA.connected
                ? ` · Grimoire de ${DATA.connected.user_name} (lecture seule)`
                : '';
            countEl.textContent = `${filtered.length} Folio${filtered.length > 1 ? 's' : ''} affiché${filtered.length > 1 ? 's' : ''}${suffix}`;
        }
    };

    /* ======================================================================
       DUEL SUR L'ÉTABLI (Chapitre IV)
       ====================================================================== */

    const populateDuelSelectors = () => {
        const leftSel = document.getElementById('duel-left-select');
        const rightSel = document.getElementById('duel-right-select');
        if (!leftSel || !rightSel) {
            return;
        }

        const pool = getCurrentPool().length >= 2 ? getCurrentPool() : state.sessionFolios;
        const prevLeft = leftSel.value;
        const prevRight = rightSel.value;

        const opts = pool.map((f) => `<option value="${escapeHtml(f.id)}">${escapeHtml(f.name)} (${escapeHtml(f.power_score)})</option>`).join('');
        leftSel.innerHTML = opts;
        rightSel.innerHTML = opts;

        if (pool.some((f) => f.id === prevLeft)) {
            leftSel.value = prevLeft;
        }
        if (pool.some((f) => f.id === prevRight)) {
            rightSel.value = prevRight;
        } else if (pool.length > 4) {
            rightSel.value = pool[4].id;
        } else if (pool.length > 1) {
            rightSel.value = pool[1].id;
        }

        renderDuelCards();
    };

    const getDuelFolio = (selectId, fallbackIdx) => {
        const pool = getCurrentPool().length >= 2 ? getCurrentPool() : state.sessionFolios;
        const val = document.getElementById(selectId)?.value;
        return pool.find((f) => f.id === val) || pool[fallbackIdx] || pool[0];
    };

    const renderSingleDuelCard = (container, folio, currentHp) => {
        if (!container || !folio) {
            return;
        }
        const hp = Math.max(0, currentHp);
        const pct = Math.max(0, Math.min(100, Math.round((hp / folio.hp) * 100)));
        container.style.setProperty('--duel-glow', folio.power_pigment_hex);
        container.innerHTML = `
            <span class="duel-stamp duel-stamp--win">VICTORIEUX</span>
            <span class="duel-stamp duel-stamp--lose">VAINCU</span>
            <div class="folio-topbar">
                <span class="folio-ref">${escapeHtml(folio.race_name)} · ${escapeHtml(folio.weapon_name)}</span>
                <span class="power-medallion tabular-nums">${escapeHtml(folio.power_score)}</span>
            </div>
            <h3 class="folio-name" style="margin-bottom:8px;">${escapeHtml(folio.name)}</h3>
            ${renderPortraitHtml(folio)}
            <div class="ink-stat-list tabular-nums" style="margin-top:10px;margin-bottom:4px;">
                <div>
                    <div class="ink-stat-head">
                        <span>Vitalité</span>
                        <span data-duel-hp>${hp} / ${folio.hp} PV</span>
                    </div>
                    <div class="ink-meter"><div class="ink-meter-fill ink-meter-fill--hp" data-duel-bar style="width:${pct}%;"></div></div>
                </div>
            </div>
            <div class="folio-sub" style="color:${escapeHtml(folio.power_pigment_hex)};">
                Sceau : ${escapeHtml(folio.power_name)} (${escapeHtml(folio.power_pigment_name)})
            </div>
        `;
    };

    const clearDuelTimers = () => {
        state.duelTimers.forEach((id) => window.clearTimeout(id));
        state.duelTimers = [];
        state.duelRunning = false;
    };

    const renderDuelCards = () => {
        clearDuelTimers();
        const leftEl = document.getElementById('duel-card-left');
        const rightEl = document.getElementById('duel-card-right');
        const chronicleEl = document.getElementById('duel-chronicle');
        const leftFolio = getDuelFolio('duel-left-select', 0);
        const rightFolio = getDuelFolio('duel-right-select', 4);

        [leftEl, rightEl].forEach((el) => el && el.classList.remove('is-winner', 'is-loser', 'is-channeling'));
        renderSingleDuelCard(leftEl, leftFolio, leftFolio.hp);
        renderSingleDuelCard(rightEl, rightFolio, rightFolio.hp);
        if (chronicleEl) {
            chronicleEl.innerHTML = '<p><em>Les deux parchemins reposent face à face sur le chêne sombre. Brisez leurs sceaux pour libérer leurs pouvoirs.</em></p>';
        }
    };

    const runDuelSimulation = () => {
        if (state.duelRunning) {
            return;
        }
        renderDuelCards();
        state.duelRunning = true;

        const leftFolio = getDuelFolio('duel-left-select', 0);
        const rightFolio = getDuelFolio('duel-right-select', 4);
        const leftEl = document.getElementById('duel-card-left');
        const rightEl = document.getElementById('duel-card-right');
        const chronicleEl = document.getElementById('duel-chronicle');
        if (chronicleEl) chronicleEl.innerHTML = '';

        const fighters = [
            { side: 'left', folio: leftFolio, hp: leftFolio.hp, el: leftEl },
            { side: 'right', folio: rightFolio, hp: rightFolio.hp, el: rightEl },
        ];

        const firstIdx = fighters[0].folio.speed >= fighters[1].folio.speed ? 0 : 1;
        const turns = [];
        let simHp = [fighters[0].hp, fighters[1].hp];

        for (let round = 1; round <= 6 && simHp[0] > 0 && simHp[1] > 0; round++) {
            const order = [firstIdx, 1 - firstIdx];
            for (const atkIdx of order) {
                if (simHp[0] <= 0 || simHp[1] <= 0) break;
                const defIdx = 1 - atkIdx;
                const atk = fighters[atkIdx].folio;
                const def = fighters[defIdx].folio;
                const synBonus = (atk.synergies || []).length * 4;
                const baseDmg = Math.max(12, Math.round((atk.strength * 0.56) - (def.defense * 0.22) + synBonus + randInt(3, 11)));
                const dmg = atk.is_unstable ? Math.round(baseDmg * (0.75 + Math.random() * 0.65)) : baseDmg;
                simHp[defIdx] = Math.max(0, simHp[defIdx] - dmg);

                turns.push({
                    round,
                    atkIdx,
                    defIdx,
                    dmg,
                    remainingHp: simHp[defIdx],
                    line: `Tour ${round} — Le sceau ${atk.power_name} de ${atk.name} s'embrase (${atk.weapon_name}) et inflige ${dmg} dégâts à ${def.name} (${simHp[defIdx]} PV restants).`,
                });
            }
        }

        const winnerIdx = simHp[0] >= simHp[1] ? 0 : 1;
        const stepMs = prefersReducedMotion ? 40 : 620;

        turns.forEach((turn, idx) => {
            const tId = window.setTimeout(() => {
                const atkF = fighters[turn.atkIdx];
                const defF = fighters[turn.defIdx];
                fighters.forEach((f) => f.el && f.el.classList.remove('is-channeling'));
                if (atkF.el) atkF.el.classList.add('is-channeling');

                if (defF.el) {
                    const hpSpan = defF.el.querySelector('[data-duel-hp]');
                    const hpBar = defF.el.querySelector('[data-duel-bar]');
                    if (hpSpan) hpSpan.textContent = `${turn.remainingHp} / ${defF.folio.hp} PV`;
                    if (hpBar) hpBar.style.width = `${Math.max(0, Math.round((turn.remainingHp / defF.folio.hp) * 100))}%`;
                }
                if (chronicleEl) {
                    const p = document.createElement('p');
                    p.textContent = turn.line;
                    chronicleEl.appendChild(p);
                }
            }, idx * stepMs);
            state.duelTimers.push(tId);
        });

        const endTimer = window.setTimeout(() => {
            fighters.forEach((f) => f.el && f.el.classList.remove('is-channeling'));
            const winner = fighters[winnerIdx];
            const loser = fighters[1 - winnerIdx];
            if (winner.el) winner.el.classList.add('is-winner');
            if (loser.el) loser.el.classList.add('is-loser');
            if (chronicleEl) {
                const p = document.createElement('p');
                p.innerHTML = `<strong>Verdict de l'Établi :</strong> ${escapeHtml(winner.folio.name)} triomphe et reçoit le sceau doré <em>VICTORIEUX</em>.`;
                chronicleEl.appendChild(p);
            }
            state.duelRunning = false;
        }, turns.length * stepMs + 220);
        state.duelTimers.push(endTimer);
    };

    /* ======================================================================
       ÉCOUTEURS D'ÉVÉNEMENTS & SIGNETS SCROLLSPY
       ====================================================================== */

    const bindEvents = () => {
        // Volvelle controls
        const btnAll = document.getElementById('btn-pool-all');
        const btnHuman = document.getElementById('btn-pool-human');
        if (btnAll && btnHuman) {
            btnAll.addEventListener('click', () => {
                if (state.spinning) return;
                state.humanOnlyPool = false;
                btnAll.classList.add('is-active');
                btnHuman.classList.remove('is-active');
                drawAllVolvelleRings();
            });
            btnHuman.addEventListener('click', () => {
                if (state.spinning) return;
                state.humanOnlyPool = true;
                btnHuman.classList.add('is-active');
                btnAll.classList.remove('is-active');
                drawAllVolvelleRings();
            });
        }

        document.getElementById('btn-charge-pity')?.addEventListener('click', () => {
            if (state.spinning) return;
            state.hud.pity_counter = Number(state.hud.pity_threshold || 10);
            renderHud();
            drawPowerRing(true);
            const statusEl = document.getElementById('ritual-status');
            if (statusEl) {
                statusEl.textContent = 'Grand Rituel chargé (10/10) : Feu et Poison retirés de l\'anneau extérieur !';
            }
        });

        document.getElementById('btn-refill-ink')?.addEventListener('click', () => {
            state.hud.spin_charges = Number(state.hud.spin_max || 3);
            renderHud();
            const statusEl = document.getElementById('ritual-status');
            if (statusEl) {
                statusEl.textContent = 'Encrier rechargé à 3/3 gouttes d\'encre.';
            }
        });

        document.getElementById('btn-invoke-seal')?.addEventListener('click', runInvocationRitual);

        document.getElementById('btn-seal-folio')?.addEventListener('click', () => {
            if (!state.pendingFolio) return;
            state.sessionFolios.unshift(state.pendingFolio);
            const sealedName = state.pendingFolio.name;
            state.pendingFolio = null;
            state.mode = 'showcase';
            state.rarityFilter = 'all';
            document.querySelectorAll('[data-source-toggle]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-source-toggle') === 'showcase');
            });
            document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-rarity-filter') === 'all');
            });
            if (state.renderedLuckyRing !== isPityReady()) {
                drawPowerRing(isPityReady());
            }
            renderGallery();
            populateDuelSelectors();
            const actionsEl = document.getElementById('ritual-actions');
            const statusEl = document.getElementById('ritual-status');
            if (actionsEl) actionsEl.hidden = true;
            if (statusEl) {
                statusEl.textContent = `« ${sealedName} » a été scellé dans la galerie du Chapitre III.`;
            }
        });

        document.getElementById('btn-transmute-folio')?.addEventListener('click', () => {
            if (!state.pendingFolio) return;
            const gained = Number(state.pendingFolio.recycle_value || 0);
            const name = state.pendingFolio.name;
            state.hud.coins = Number(state.hud.coins || 0) + gained;
            state.pendingFolio = null;
            if (state.renderedLuckyRing !== isPityReady()) {
                drawPowerRing(isPityReady());
            }
            renderHud();
            const actionsEl = document.getElementById('ritual-actions');
            const statusEl = document.getElementById('ritual-status');
            if (actionsEl) actionsEl.hidden = true;
            if (statusEl) {
                statusEl.textContent = `« ${name} » transmuté en +${gained} écus d'or (session bac à sable).`;
            }
        });

        // Banc d'essai Paper Doll
        ['bench-weapon', 'bench-power', 'bench-rarity'].forEach((id) => {
            document.getElementById(id)?.addEventListener('change', updateBench);
        });

        document.getElementById('btn-bench-backdrop')?.addEventListener('click', (e) => {
            state.benchCosmos = !state.benchCosmos;
            e.currentTarget.textContent = state.benchCosmos
                ? 'Fond : Sombre Cosmos (#0B1020)'
                : 'Fond : Vélin Codex (#EBE0C8)';
            e.currentTarget.classList.toggle('is-active', !state.benchCosmos);
            updateBench();
        });

        document.getElementById('btn-bench-halo')?.addEventListener('click', (e) => {
            state.benchHalo = !state.benchHalo;
            e.currentTarget.textContent = state.benchHalo
                ? 'Halo Contraste Auras Pâles : Actif'
                : 'Halo Contraste Auras Pâles : Désactivé';
            e.currentTarget.classList.toggle('is-active', state.benchHalo);
            updateBench();
        });

        // Flip 3D (délégation d'événements)
        document.getElementById('folios-grid')?.addEventListener('click', (e) => {
            const flipBtn = e.target.closest('[data-flip-folio]');
            if (!flipBtn) return;
            const card = flipBtn.closest('.folio-card');
            if (card) {
                card.classList.toggle('is-flipped');
            }
        });

        // Filtres de rareté
        document.querySelectorAll('[data-rarity-filter]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.rarityFilter = btn.getAttribute('data-rarity-filter') || 'all';
                document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                    b.classList.toggle('is-active', b === btn);
                });
                renderGallery();
            });
        });

        document.getElementById('gallery-reset-filter-btn')?.addEventListener('click', () => {
            state.rarityFilter = 'all';
            document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-rarity-filter') === 'all');
            });
            renderGallery();
        });

        // Mode connecté lecture seule (Mode Vitrine vs Mon Grimoire)
        document.querySelectorAll('[data-source-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.mode = btn.getAttribute('data-source-toggle') || 'showcase';
                document.querySelectorAll('[data-source-toggle]').forEach((b) => {
                    b.classList.toggle('is-active', b === btn);
                });
                renderGallery();
                populateDuelSelectors();
            });
        });

        // Duel
        document.getElementById('duel-left-select')?.addEventListener('change', renderDuelCards);
        document.getElementById('duel-right-select')?.addEventListener('change', renderDuelCards);
        document.getElementById('btn-run-duel')?.addEventListener('click', runDuelSimulation);
        document.getElementById('btn-reset-duel')?.addEventListener('click', renderDuelCards);

        // Atelier Alchimique paliers interactifs
        document.querySelectorAll('[data-magnet-tier]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const tier = Number(chip.getAttribute('data-magnet-tier') || 1);
                const cost = Number(chip.getAttribute('data-magnet-cost') || 200);
                const bonusPerLevel = Number((CONFIG.workshop && CONFIG.workshop.rare_magnet && CONFIG.workshop.rare_magnet.bonus_per_level) || 3);
                document.querySelectorAll('[data-magnet-tier]').forEach((c) => {
                    c.classList.toggle('is-bought', Number(c.getAttribute('data-magnet-tier')) <= tier);
                });
                const readout = document.getElementById('workshop-magnet-readout');
                if (readout) {
                    readout.innerHTML = `Simulation palier ${tier} : <strong>+${tier * bonusPerLevel}%</strong> vers Gravité / Téléportation / Invisibilité (coût du palier : ${cost.toLocaleString('fr-FR')} écus).`;
                }
            });
        });

        // Scrollspy pour les 5 signets de cuir
        const chapters = document.querySelectorAll('.codex-chapter[id]');
        const bookmarks = document.querySelectorAll('[data-chapter-link]');
        if ('IntersectionObserver' in window && chapters.length > 0) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const activeId = entry.target.id;
                        bookmarks.forEach((b) => {
                            b.classList.toggle('is-active', b.getAttribute('data-chapter-link') === activeId);
                        });
                    }
                });
            }, { rootMargin: '-25% 0px -60% 0px' });

            chapters.forEach((chapter) => observer.observe(chapter));
        }
    };

    /* ======================================================================
       INITIALISATION
       ====================================================================== */

    renderHud();
    drawAllVolvelleRings();
    updateBench();
    populateDuelSelectors();
    bindEvents();
})();
