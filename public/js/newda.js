(() => {
    'use strict';

    const bootstrapEl = document.getElementById('codex-bootstrap-data');
    if (!bootstrapEl) {
        return;
    }

    const DATA = JSON.parse(bootstrapEl.textContent || '{}');
    const PAGE = DATA.page || document.body.dataset.codexPage || 'invocation';
    const STORAGE_KEY = `wheelborn_newda_v2:${DATA.storageScope || 'guest'}`;
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
    const ICONS = DATA.icons || { races: {}, weapons: {}, powers: {} };
    const POWER_SIGILS = DATA.powerSigils || {};

    const RACE_BY_ID = indexById(RACES);
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

    /* ======================================================================
       STORE NAMESPACÉ (sessionStorage isolé guest vs user:ID)
       ====================================================================== */

    const createDefaultStore = () => ({
        hud: {
            spin_charges: Number(DATA.hud?.spin_charges ?? 3),
            spin_max: Number(DATA.hud?.spin_max ?? 3),
            pity_counter: Number(DATA.hud?.pity_counter ?? 0),
            pity_threshold: Number(DATA.hud?.pity_threshold ?? 10),
            coins: Number(DATA.hud?.coins ?? 250),
            elo: Number(DATA.hud?.elo ?? 1000),
            level: Number(DATA.hud?.level ?? 1),
        },
        sourceMode: DATA.isAuthenticated ? 'connected' : 'showcase',
        humanOnlyPool: false,
        connectedSealedFolios: [],
        showcaseSealedFolios: [],
        folioCounter: Number(DATA.showcaseCount ?? 6),
        pendingFolio: null,
        workshop: {
            rare_magnet: Number(DATA.workshopLevels?.rare_magnet ?? 0),
            hero_forge: Number(DATA.workshopLevels?.hero_forge ?? 0),
            arena_bonus: Number(DATA.workshopLevels?.arena_bonus ?? 0),
        },
        emergencyPurchases: 0,
    });

    const loadStore = () => {
        const defaults = createDefaultStore();
        try {
            const raw = window.sessionStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return defaults;
            }
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') {
                return defaults;
            }
            return {
                ...defaults,
                ...parsed,
                sourceMode: DATA.isAuthenticated
                    ? (parsed.sourceMode === 'showcase' ? 'showcase' : 'connected')
                    : 'showcase',
                hud: {
                    ...defaults.hud,
                    ...(parsed.hud || {}),
                },
                workshop: {
                    ...defaults.workshop,
                    ...(parsed.workshop || {}),
                },
                connectedSealedFolios: Array.isArray(parsed.connectedSealedFolios) ? parsed.connectedSealedFolios : [],
                showcaseSealedFolios: Array.isArray(parsed.showcaseSealedFolios) ? parsed.showcaseSealedFolios : [],
                pendingFolio: parsed.pendingFolio && typeof parsed.pendingFolio === 'object' ? parsed.pendingFolio : null,
            };
        } catch (_err) {
            return defaults;
        }
    };

    let store = loadStore();

    const saveStore = () => {
        try {
            window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(store));
        } catch (_err) {
            // Mode navigation privée ou quota plein : on continue en mémoire.
        }
    };

    const clearStore = () => {
        try {
            window.sessionStorage.removeItem(STORAGE_KEY);
        } catch (_err) {
            // Ignoré
        }
        store = createDefaultStore();
    };

    const state = {
        rarityFilter: 'all',
        spinning: false,
        spinTimers: [],
        rotations: { powers: 0, weapons: 0, races: 0 },
        ringsGeometry: { powers: [], weapons: [], races: [] },
        renderedLuckyRing: false,
        arenaDemoMode: false,
        duelRunning: false,
        duelTimers: [],
        prevArenaLeft: '',
        prevArenaRight: '',
        activeLeague: DATA.activeLeague || 'silver',
        lastInspectorTrigger: null,
    };

    /* ======================================================================
       UTILITAIRES, ICÔNES (SOURCE UNIQUE PHP) & CARTES
       ====================================================================== */

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

    const weightedPick = (items) => {
        const total = items.reduce((acc, item) => acc + Math.max(0, Number(item.weight || 0)), 0);
        let cursor = Math.random() * (total || 1);
        for (const item of items) {
            cursor -= Math.max(0, Number(item.weight || 0));
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

    const resolveIcon = (category, id) => {
        const cat = ICONS[category] || {};
        return cat[id] || { type: 'svg', viewBox: '0 0 48 48', markup: '' };
    };

    const renderHtmlIcon = (category, id, className = 'codex-icon', alt = '') => {
        const icon = resolveIcon(category, id);
        if (icon.type === 'image' && icon.url) {
            return `<img class="${escapeHtml(className)} codex-icon--custom" src="${escapeHtml(icon.url)}" alt="${escapeHtml(alt || id)}" loading="lazy">`;
        }
        return `<svg class="${escapeHtml(className)}" viewBox="${escapeHtml(icon.viewBox || '0 0 48 48')}" aria-hidden="true">${icon.markup || ''}</svg>`;
    };

    const renderPowerSigilSvg = (powerId, hex, className = 'codex-sigil-watermark') => {
        const paths = POWER_SIGILS[powerId] || POWER_SIGILS.invisibility || '';
        return `<svg class="${escapeHtml(className)}" viewBox="0 0 120 120" style="color:${escapeHtml(hex)};" aria-hidden="true">${paths}</svg>`;
    };

    const renderPortraitHtml = (folio) => {
        const sigil = renderPowerSigilSvg(folio.power_id, folio.power_pigment_hex);
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
                    ${renderHtmlIcon('races', folio.race_id, 'heraldic-rune', folio.race_name)}
                </div>
            </div>
        `;
    };

    const renderStatBarsHtml = (folio) => {
        const hpPct = Math.min(100, Math.round((folio.hp / 140) * 100));
        return `
            <div class="stat-bars tabular-nums">
                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 17 C4.5 13 2.5 9.5 2.5 6.5 C2.5 4 4.5 2.5 6.8 2.5 C8.3 2.5 9.4 3.3 10 4.4 C10.6 3.3 11.7 2.5 13.2 2.5 C15.5 2.5 17.5 4 17.5 6.5 C17.5 9.5 15.5 13 10 17 Z" fill="currentColor"/></svg>
                        PV
                    </span>
                    <div class="stat-track"><div class="stat-fill stat-fill--hp" style="width:${hpPct}%;"></div></div>
                    <span class="stat-val">${escapeHtml(folio.hp)}<small>/140</small></span>
                </div>
                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L13 6 V13 H7 V6 Z M5 13 H15 M10 13 V18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                        FOR
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width:${Math.min(100, folio.strength)}%;"></div></div>
                    <span class="stat-val">${escapeHtml(folio.strength)}<small>/100</small></span>
                </div>
                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><polygon points="11.5,2 4.5,11 9.5,11 8.5,18 15.5,9 10.5,9" fill="currentColor"/></svg>
                        VIT
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width:${Math.min(100, folio.speed)}%;"></div></div>
                    <span class="stat-val">${escapeHtml(folio.speed)}<small>/100</small></span>
                </div>
                <div class="stat-bar-row">
                    <span class="stat-bar-label">
                        <svg class="stat-icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 L16.5 4.8 V10 C16.5 14 13.5 16.8 10 18 C6.5 16.8 3.5 14 3.5 10 V4.8 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>
                        DEF
                    </span>
                    <div class="stat-track"><div class="stat-fill" style="width:${Math.min(100, folio.defense)}%;"></div></div>
                    <span class="stat-val">${escapeHtml(folio.defense)}<small>/100</small></span>
                </div>
            </div>
        `;
    };

    const renderTraitPillsHtml = (folio) => `
        <div class="folio-pills" aria-label="Attributs">
            <span class="trait-pill" title="Race : ${escapeHtml(folio.race_name)}">
                ${renderHtmlIcon('races', folio.race_id, 'trait-pill-icon', folio.race_name)}
                <span>${escapeHtml(folio.race_name)}</span>
            </span>
            <span class="trait-pill" title="Arme : ${escapeHtml(folio.weapon_name)}">
                ${renderHtmlIcon('weapons', folio.weapon_id, 'trait-pill-icon', folio.weapon_name)}
                <span>${escapeHtml(folio.weapon_name)}</span>
            </span>
            <span class="trait-pill trait-pill--power" style="--power-hex:${escapeHtml(folio.power_pigment_hex)};" title="Pouvoir : ${escapeHtml(folio.power_name)}">
                ${renderHtmlIcon('powers', folio.power_id, 'trait-pill-icon', folio.power_name)}
                <span>${escapeHtml(folio.power_name)}</span>
            </span>
        </div>
    `;

    const renderFolioCardHtml = (folio, options = {}) => {
        const favFlag = folio.is_favorite ? '<span class="folio-flag folio-flag--fav" title="Favori" aria-label="Favori">★</span>' : '';
        const lockFlag = folio.is_locked ? '<span class="folio-flag folio-flag--lock" title="Verrouillé" aria-label="Verrouillé">⛨</span>' : '';
        const synergies = folio.synergies || [];
        const featuredSyn = synergies.find((s) => Boolean(s.is_unstable)) || synergies[0] || null;
        const extraSynCount = Math.max(0, synergies.length - 1);
        const synChip = featuredSyn
            ? `<span class="syn-chip ${featuredSyn.is_unstable ? 'syn-chip--unstable' : ''}" title="${escapeHtml(featuredSyn.name)}">
                ${featuredSyn.is_unstable ? '⚡' : '✦'} ${escapeHtml(featuredSyn.name)}${extraSynCount > 0 ? ` +${extraSynCount}` : ''}
               </span>`
            : '';

        const versoTags = [
            folio.race_passive ? `<span class="verso-tag">${escapeHtml(folio.race_passive)}</span>` : '',
            folio.weapon_passive ? `<span class="verso-tag">${escapeHtml(folio.weapon_passive)}</span>` : '',
            ...synergies.map((s) => `<span class="verso-tag ${s.is_unstable ? 'verso-tag--unstable' : 'verso-tag--synergy'}">${s.is_unstable ? '⚡' : '✦'} ${escapeHtml(s.name)}</span>`),
        ].join('');

        const stampClass = options.animateStamp ? 'wax-stamp-drop' : '';

        return `
            <article class="folio-card" data-folio-id="${escapeHtml(folio.id)}" data-rarity="${escapeHtml(folio.rarity)}">
                <div class="folio-card-inner">
                    <div class="folio-face folio-face--front" aria-hidden="false">
                        <div class="folio-head">
                            <div class="folio-title-block">
                                <h3 class="folio-name">${escapeHtml(folio.name)}</h3>
                                <div class="folio-flags">${favFlag}${lockFlag}</div>
                            </div>
                            <span class="power-medallion tabular-nums ${stampClass}" title="Power Score">${escapeHtml(folio.power_score)}</span>
                        </div>
                        ${renderPortraitHtml(folio)}
                        ${renderTraitPillsHtml(folio)}
                        <div class="folio-foot">
                            <div class="folio-foot-left">
                                <span class="rarity-seal-pill ${stampClass}" style="--seal-hex:${escapeHtml(folio.seal_hex)};" title="${escapeHtml(folio.rarity_label)}">
                                    <span class="rarity-dot" aria-hidden="true"></span>
                                    <span>${escapeHtml(folio.rarity_label)}</span>
                                </span>
                                ${synChip}
                            </div>
                            <button type="button" class="folio-flip-icon" data-flip-folio aria-label="Voir les statistiques de ${escapeHtml(folio.name)}" title="Stats &amp; Verso">↻</button>
                        </div>
                    </div>
                    <div class="folio-face folio-face--back" aria-hidden="true">
                        <div class="folio-head">
                            <h3 class="folio-name">${escapeHtml(folio.name)}</h3>
                            <span class="power-medallion tabular-nums">${escapeHtml(folio.power_score)}</span>
                        </div>
                        ${renderStatBarsHtml(folio)}
                        <div class="verso-tags">${versoTags}</div>
                        <div class="folio-foot">
                            <span class="recycle-pill tabular-nums" title="Valeur de transmutation">
                                <svg class="hud-icon" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7.5" fill="none" stroke="currentColor" stroke-width="1.9"/><polygon points="10,5.5 13,10 10,14.5 7,10" fill="currentColor"/></svg>
                                +${escapeHtml(folio.recycle_value)}
                            </span>
                            <button type="button" class="folio-flip-icon" data-flip-folio tabindex="-1" aria-label="Retourner au recto de ${escapeHtml(folio.name)}" title="Recto">↺</button>
                        </div>
                    </div>
                </div>
            </article>
        `;
    };

    /* ======================================================================
       HUD & COMPTEURS SYNCHRONISÉS INTER-PAGES
       ====================================================================== */

    const isPityReady = () => Number(store.hud.pity_counter || 0) >= Number(store.hud.pity_threshold || 10);

    const getActivePool = () => {
        if (store.sourceMode === 'connected' && DATA.connected) {
            return [...store.connectedSealedFolios, ...(DATA.connected.folios || [])];
        }
        return [...store.showcaseSealedFolios, ...(DATA.showcaseFolios || [])];
    };

    const getActiveGrimoireCount = () => {
        if (store.sourceMode === 'connected' && DATA.connected) {
            return Number(DATA.connected.folios_count || 0) + store.connectedSealedFolios.length;
        }
        return Number(DATA.showcaseCount || 6) + store.showcaseSealedFolios.length;
    };

    const renderHud = () => {
        const inkText = document.getElementById('hud-ink-text');
        const inkChip = document.getElementById('hud-chip-ink');
        const pityText = document.getElementById('hud-pity-text');
        const pityChip = document.getElementById('hud-chip-pity');
        const coinsEl = document.getElementById('hud-coins');
        const navCount = document.getElementById('nav-grimoire-count');
        const sealBtn = document.getElementById('btn-invoke-seal');

        const curInk = Math.max(0, Number(store.hud.spin_charges || 0));
        const maxInk = Number(store.hud.spin_max || 3);
        const curPity = Math.min(Number(store.hud.pity_threshold || 10), Math.max(0, Number(store.hud.pity_counter || 0)));
        const maxPity = Number(store.hud.pity_threshold || 10);
        const lucky = curPity >= maxPity;

        if (inkText) inkText.textContent = `${curInk}/${maxInk}`;
        if (inkChip) inkChip.title = `Gouttes d'encre (${curInk}/${maxInk})`;
        if (pityText) pityText.textContent = `${curPity}/${maxPity}`;
        if (pityChip) {
            pityChip.classList.toggle('is-lucky', lucky);
            pityChip.title = `Grand Rituel (${curPity}/${maxPity})`;
        }
        if (coinsEl) coinsEl.textContent = Number(store.hud.coins || 0).toLocaleString('fr-FR');
        if (navCount) navCount.textContent = String(getActiveGrimoireCount());

        document.querySelectorAll('[data-connected-count]').forEach((el) => {
            if (DATA.connected) {
                el.textContent = String(Number(DATA.connected.folios_count || 0) + store.connectedSealedFolios.length);
            }
        });
        document.querySelectorAll('[data-showcase-count]').forEach((el) => {
            el.textContent = String(Number(DATA.showcaseCount || 6) + store.showcaseSealedFolios.length);
        });
        document.querySelectorAll('[data-source-toggle]').forEach((btn) => {
            btn.classList.toggle('is-active', btn.getAttribute('data-source-toggle') === store.sourceMode);
        });
        document.querySelectorAll('[data-sandbox-pool]').forEach((btn) => {
            const wantHuman = btn.getAttribute('data-sandbox-pool') === 'human';
            btn.classList.toggle('is-active', wantHuman === Boolean(store.humanOnlyPool));
        });

        if (sealBtn) {
            sealBtn.classList.toggle('btn-wax-seal--gold', lucky);
            sealBtn.innerHTML = lucky
                ? '<span>Grand Rituel</span>'
                : '<span>Invoquer</span>';
            sealBtn.disabled = state.spinning || Boolean(store.pendingFolio);
            sealBtn.title = store.pendingFolio
                ? 'Scellez ou transmutez le folio en attente'
                : (lucky ? 'Grand Rituel garanti (10/10)' : 'Invoquer un héros');
        }

        const wsBal = document.getElementById('workshop-balance');
        if (wsBal) {
            wsBal.textContent = Number(store.hud.coins || 0).toLocaleString('fr-FR');
        }
    };

    /* ======================================================================
       PAGE 1 — VOLVELLE SÉQUENTIELLE (Races -> Armes -> Pouvoirs)
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

    const renderVolvelleSectorIcon = (category, item, x, y, midDeg, size, iconColor) => {
        const icon = resolveIcon(category, item.id);
        const half = (size / 2).toFixed(2);

        if (icon.type === 'image' && icon.url) {
            const bgRadius = (size * 0.62).toFixed(2);
            const clipAttr = [25, 30, 34].includes(size) ? ` clip-path="url(#volvelle-clip-${size})"` : '';
            return `
                <g transform="translate(${x.toFixed(1)} ${y.toFixed(1)}) rotate(${midDeg.toFixed(1)})">
                    <circle cx="0" cy="0" r="${bgRadius}" fill="rgba(245,237,216,0.92)" stroke="#4A3618" stroke-width="0.9"/>
                    <image href="${escapeHtml(icon.url)}" x="-${half}" y="-${half}" width="${size}" height="${size}" preserveAspectRatio="xMidYMid meet"${clipAttr}/>
                </g>
            `;
        }

        const scale = (size / 48).toFixed(4);
        return `
            <g transform="translate(${x.toFixed(1)} ${y.toFixed(1)}) rotate(${midDeg.toFixed(1)}) translate(-${half} -${half}) scale(${scale})" style="color:${iconColor};">
                ${icon.markup || ''}
            </g>
        `;
    };

    const drawRing = (groupEl, category, items, rInner, rOuter, options = {}) => {
        if (!groupEl) {
            return [];
        }
        const cx = 250;
        const cy = 250;
        const total = items.reduce((acc, item) => acc + Math.max(0.1, Number(item.weight || 1)), 0);
        let cursor = 0;
        const geometry = [];
        let svgMarkup = '';

        items.forEach((item, idx) => {
            const span = (Math.max(0.1, Number(item.weight || 1)) / total) * 360;
            const startDeg = cursor;
            const endDeg = cursor + span;
            const midDeg = startDeg + span / 2;
            cursor = endDeg;

            geometry.push({ id: item.id, startDeg, endDeg, midDeg });

            const path = annularSectorPath(cx, cy, rInner, rOuter, startDeg, endDeg);
            const fill = options.fillFor ? options.fillFor(item, idx) : (idx % 2 === 0 ? '#EFE4CC' : '#DFCFAF');
            const iconColor = options.iconFor ? options.iconFor(item, idx) : '#2A1D11';
            const rMid = (rInner + rOuter) / 2;
            const pos = polarToCartesian(cx, cy, rMid, midDeg);
            const iconSize = options.iconSize || 28;

            svgMarkup += `<g><title>${escapeHtml(item.name)}</title>`;
            svgMarkup += `<path d="${path}" fill="${fill}" stroke="#4A3618" stroke-width="1.15"/>`;
            svgMarkup += renderVolvelleSectorIcon(category, item, pos.x, pos.y, midDeg, iconSize, iconColor);
            svgMarkup += '</g>';
        });

        groupEl.innerHTML = svgMarkup;
        return geometry;
    };

    const getEffectivePowerPool = (isLucky) => {
        const basePool = isLucky ? POWERS.filter((p) => p.tier !== 'common') : POWERS;
        const tierTotals = {};
        basePool.forEach((p) => {
            const w = Math.max(0, Number(p.weight || 0));
            tierTotals[p.tier] = (tierTotals[p.tier] || 0) + w;
        });

        const totalWeight = Object.values(tierTotals).reduce((acc, w) => acc + w, 0);
        const magnetLevel = Math.max(0, Number(store.workshop.rare_magnet || 0));
        const bonusPerLevel = Number(CONFIG.workshop?.rare_magnet?.bonus_per_level ?? 3);
        const bonus = magnetLevel * bonusPerLevel;
        const toMove = !tierTotals.mythic || bonus <= 0 ? 0 : (totalWeight * bonus) / 100;

        const scale = {};
        Object.keys(tierTotals).forEach((tier) => {
            scale[tier] = 1;
        });

        let moved = 0;
        ['common', 'rare'].forEach((tier) => {
            if (!tierTotals[tier] || moved >= toMove) {
                return;
            }
            const taken = Math.min(tierTotals[tier], toMove - moved);
            scale[tier] = (tierTotals[tier] - taken) / tierTotals[tier];
            moved += taken;
        });

        if (moved > 0 && tierTotals.mythic) {
            scale.mythic = (tierTotals.mythic + moved) / tierTotals.mythic;
        }

        return basePool.map((p) => ({
            ...p,
            weight: Math.max(0.1, Number(p.weight || 1) * (scale[p.tier] ?? 1)),
        }));
    };

    const drawPowerRing = (isLucky) => {
        state.renderedLuckyRing = Boolean(isLucky);
        state.ringsGeometry.powers = drawRing(
            document.getElementById('ring-powers'),
            'powers',
            getEffectivePowerPool(isLucky),
            184,
            242,
            {
                fillFor: (p) => p.pigment_hex,
                iconFor: () => '#FBF2DC',
                iconSize: 25,
            }
        );
    };

    const drawAllVolvelleRings = (overrideLucky = null) => {
        if (!document.getElementById('volvelle-svg')) {
            return;
        }
        drawPowerRing(overrideLucky === null ? isPityReady() : Boolean(overrideLucky));

        state.ringsGeometry.weapons = drawRing(
            document.getElementById('ring-weapons'),
            'weapons',
            WEAPONS,
            126,
            184,
            {
                fillFor: (_w, i) => (i % 2 === 0 ? '#F3E8D0' : '#E0D0AE'),
                iconFor: () => '#2A1D11',
                iconSize: 34,
            }
        );

        state.ringsGeometry.races = drawRing(
            document.getElementById('ring-races'),
            'races',
            RACES,
            66,
            126,
            {
                fillFor: (r, i) => {
                    if (store.humanOnlyPool) {
                        return r.has_portrait ? '#DFC06E' : '#D5C6A5';
                    }
                    return i % 2 === 0 ? '#E9DBBE' : '#D9C8A2';
                },
                iconFor: (r) => (store.humanOnlyPool && !r.has_portrait ? '#7D6849' : '#2A1D11'),
                iconSize: 30,
            }
        );
    };

    const clearSpinTimers = () => {
        state.spinTimers.forEach((id) => window.clearTimeout(id));
        state.spinTimers = [];
    };

    const scheduleSpin = (fn, delay) => {
        const id = window.setTimeout(fn, delay);
        state.spinTimers.push(id);
        return id;
    };

    const setActiveRingGlow = (ringName) => {
        ['races', 'weapons', 'powers'].forEach((name) => {
            const el = document.getElementById(`ring-glow-${name}`);
            if (el) {
                el.classList.toggle('is-active-ring', name === ringName);
            }
        });
    };

    const resetSequenceSteps = () => {
        ['race', 'weapon', 'power'].forEach((step) => {
            const el = document.getElementById(`step-${step}`);
            if (!el) return;
            el.classList.remove('is-active', 'is-locked');
            el.style.removeProperty('--step-accent');
            const iconSlot = el.querySelector('[data-step-icon]');
            const valSlot = el.querySelector('[data-step-value]');
            if (iconSlot) iconSlot.textContent = '?';
            if (valSlot) valSlot.textContent = '—';
        });
    };

    const setStepActive = (step) => {
        const el = document.getElementById(`step-${step}`);
        if (el) {
            el.classList.remove('is-locked');
            el.classList.add('is-active');
        }
    };

    const lockStep = (step, category, id, label, accentHex = null) => {
        const el = document.getElementById(`step-${step}`);
        if (!el) return;
        el.classList.remove('is-active');
        el.classList.add('is-locked');
        if (accentHex) {
            el.style.setProperty('--step-accent', accentHex);
        }
        const iconSlot = el.querySelector('[data-step-icon]');
        const valSlot = el.querySelector('[data-step-value]');
        if (iconSlot) iconSlot.innerHTML = renderHtmlIcon(category, id, 'seq-svg', label);
        if (valSlot) valSlot.textContent = label;
    };

    const renderEmergencyBar = () => {
        const bar = document.getElementById('emergency-spin-bar');
        const btn = document.getElementById('btn-emergency-spin');
        const label = document.getElementById('emergency-spin-label');
        if (!bar || !btn || !label) {
            return;
        }

        const shouldShow = Number(store.hud.spin_charges || 0) <= 0 && !store.pendingFolio && !state.spinning;
        bar.hidden = !shouldShow;
        if (!shouldShow) {
            return;
        }

        const costs = CONFIG.workshop?.emergency_spin?.costs || [120, 250];
        const purchases = Number(store.emergencyPurchases || 0);
        if (purchases >= costs.length) {
            btn.disabled = true;
            label.textContent = `Jeton épuisé (${purchases}/${costs.length})`;
            return;
        }

        const cost = Number(costs[purchases]);
        btn.disabled = Number(store.hud.coins || 0) < cost;
        label.textContent = `Jeton d'Urgence · ${cost.toLocaleString('fr-FR')} écus`;
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

    const rollSandboxFolio = (isLuckySpin) => {
        const illustratedRaces = RACES.filter((r) => Boolean(r.has_portrait));
        const race = store.humanOnlyPool
            ? weightedPick(illustratedRaces.length > 0 ? illustratedRaces : [RACE_BY_ID.human || RACES[0]])
            : weightedPick(RACES);
        const weapon = weightedPick(WEAPONS);
        const power = weightedPick(getEffectivePowerPool(isLuckySpin));

        const synKey = `${race.id}:${weapon.id}:${power.id}`;
        const synergies = SYNERGIES[synKey] || [];
        const isUnstable = synergies.some((s) => Boolean(s.is_unstable));

        const base = CONFIG.baseStats || {
            hp_min: 55, hp_max: 75,
            strength_min: 45, strength_max: 70,
            speed_min: 40, speed_max: 68,
            defense_min: 42, defense_max: 68,
        };

        const forgeLevel = Math.max(0, Number(store.workshop.hero_forge || 0));
        const forgeHp = forgeLevel * Number(CONFIG.workshop?.hero_forge?.bonus_hp ?? 3);
        const forgeStr = forgeLevel * Number(CONFIG.workshop?.hero_forge?.bonus_str ?? 2);

        let hp = randInt(base.hp_min, base.hp_max) + (race.mods.hp || 0) + (weapon.mods.hp || 0) + forgeHp;
        let strength = randInt(base.strength_min, base.strength_max) + (race.mods.strength || 0) + (weapon.mods.strength || 0) + forgeStr;
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

        const raceAssetUrl = PAPER_DOLL_MAP.races ? (PAPER_DOLL_MAP.races[race.id] || null) : null;
        const equipLayers = PAPER_DOLL_MAP.equipment && PAPER_DOLL_MAP.equipment[weapon.id]
            ? (PAPER_DOLL_MAP.equipment[weapon.id][power.id] || null)
            : null;
        const hasPortrait = Boolean(raceAssetUrl);
        const resolvedLayers = hasPortrait
            ? {
                race: raceAssetUrl,
                weapon: equipLayers ? equipLayers.weapon : null,
                aura: equipLayers ? equipLayers.aura : null,
                is_invisibility: Boolean(equipLayers && equipLayers.is_invisibility),
                is_pale_aura: Boolean(equipLayers && equipLayers.is_pale_aura),
            }
            : { race: null, weapon: null, aura: null, is_invisibility: false, is_pale_aura: false };
        const name = generateName(race.id);
        store.folioCounter = Number(store.folioCounter || 6) + 1;

        return {
            id: `session-${Date.now()}-${store.folioCounter}`,
            folio_number: String(store.folioCounter).padStart(3, '0'),
            name,
            initial: name.charAt(0).toUpperCase(),
            race_id: race.id,
            race_name: race.name,
            race_initial: race.initial,
            race_passive: race.passive,
            weapon_id: weapon.id,
            weapon_name: weapon.name,
            weapon_passive: weapon.passive,
            power_id: power.id,
            power_name: power.name,
            power_tier: power.tier,
            power_tier_label: power.tier_label,
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
            was_lucky_spin: Boolean(isLuckySpin),
            synergies,
            has_portrait: hasPortrait,
            layers: resolvedLayers,
        };
    };

    const showRevealedFolio = (folio, animateStamp = true) => {
        const contentEl = document.getElementById('ritual-content');
        const actionsEl = document.getElementById('ritual-actions');
        const transmuteBtn = document.getElementById('btn-transmute-folio');

        if (contentEl) {
            contentEl.className = 'ritual-reveal-wrap';
            contentEl.innerHTML = renderFolioCardHtml(folio, { animateStamp });
        }
        if (transmuteBtn) {
            transmuteBtn.textContent = `Transmuter (+${folio.recycle_value})`;
        }
        if (actionsEl) {
            actionsEl.hidden = false;
        }
    };

    const renderIdleRitual = () => {
        const contentEl = document.getElementById('ritual-content');
        const actionsEl = document.getElementById('ritual-actions');
        if (actionsEl) actionsEl.hidden = true;
        if (contentEl) {
            contentEl.className = 'ritual-idle';
            contentEl.innerHTML = `
                <svg class="ritual-idle-emblem" viewBox="0 0 120 120" aria-hidden="true">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
                    <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.2"/>
                    <polygon points="60,14 68,52 106,60 68,68 60,106 52,68 14,60 52,52" fill="none" stroke="currentColor" stroke-width="1.3"/>
                </svg>
                <p class="ritual-cue">Pressez le sceau</p>
            `;
        }
    };

    const applyRingSpin = (el, deg, durationMs, easing) => {
        if (!el) return;
        el.style.transitionDuration = prefersReducedMotion ? '1ms' : `${durationMs}ms`;
        el.style.transitionTimingFunction = easing;
        el.style.transform = `rotate(${deg}deg)`;
    };

    const runInvocationRitual = () => {
        const statusEl = document.getElementById('ritual-status');
        const contentEl = document.getElementById('ritual-content');
        const actionsEl = document.getElementById('ritual-actions');

        if (state.spinning) {
            return;
        }

        if (store.pendingFolio) {
            if (actionsEl) {
                actionsEl.classList.remove('is-attention');
                void actionsEl.offsetWidth;
                actionsEl.classList.add('is-attention');
            }
            if (statusEl) {
                statusEl.textContent = 'Scellez ou transmutez le folio en attente.';
            }
            return;
        }

        if (Number(store.hud.spin_charges || 0) <= 0) {
            renderEmergencyBar();
            if (statusEl) {
                statusEl.textContent = 'Encrier vide (0/3).';
            }
            return;
        }

        clearSpinTimers();
        state.spinning = true;

        const isLuckySpin = isPityReady();
        const ringP = document.getElementById('ring-powers');
        const ringW = document.getElementById('ring-weapons');
        const ringR = document.getElementById('ring-races');

        if (state.renderedLuckyRing !== isLuckySpin) {
            drawPowerRing(isLuckySpin);
            if (ringP) {
                ringP.style.transitionDuration = '0ms';
                state.rotations.powers = 0;
                ringP.style.transform = 'rotate(0deg)';
                void ringP.getBoundingClientRect();
            }
        }

        // Décrément immédiat dans le HUD à t = 0
        store.hud.spin_charges = Math.max(0, Number(store.hud.spin_charges || 0) - 1);
        saveStore();
        renderHud();
        renderEmergencyBar();

        if (actionsEl) actionsEl.hidden = true;
        if (statusEl) statusEl.textContent = '';
        resetSequenceSteps();

        const folio = rollSandboxFolio(isLuckySpin);

        const raceGeo = state.ringsGeometry.races.find((g) => g.id === folio.race_id) || state.ringsGeometry.races[0];
        const weaponGeo = state.ringsGeometry.weapons.find((g) => g.id === folio.weapon_id) || state.ringsGeometry.weapons[0];
        const powerGeo = state.ringsGeometry.powers.find((g) => g.id === folio.power_id) || state.ringsGeometry.powers[0];

        const isMythicPower = folio.power_tier === 'mythic';
        const durRace = prefersReducedMotion ? 50 : 1100;
        const pause1 = prefersReducedMotion ? 30 : 300;
        const durWeapon = prefersReducedMotion ? 50 : 1150;
        const pause2 = prefersReducedMotion ? 30 : 350;
        const durPower = prefersReducedMotion ? 50 : (isMythicPower ? 1850 : 1450);
        const settlePad = prefersReducedMotion ? 20 : 60;
        const traceDur = prefersReducedMotion ? 40 : 280;

        if (contentEl) {
            contentEl.className = 'ritual-idle';
            contentEl.innerHTML = `
                <svg class="ritual-idle-emblem" viewBox="0 0 120 120" aria-hidden="true">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
                    <circle cx="60" cy="60" r="34" fill="none" stroke="currentColor" stroke-width="1.2"/>
                </svg>
                <p class="ritual-cue">${isLuckySpin ? 'Grand Rituel' : 'Tirage…'}</p>
            `;
        }

        // Phase 1 : Races
        setActiveRingGlow('races');
        setStepActive('race');
        state.rotations.races = computeNextRotation(state.rotations.races, raceGeo.midDeg, 2, true);
        applyRingSpin(ringR, state.rotations.races, durRace, 'cubic-bezier(0.16, 0.84, 0.22, 1)');

        const tRaceDone = durRace + settlePad;
        const tWeaponStart = tRaceDone + pause1;
        const tWeaponDone = tWeaponStart + durWeapon + settlePad;
        const tPowerStart = tWeaponDone + pause2;
        const tPowerDone = tPowerStart + durPower + settlePad;
        const tReveal = tPowerDone + traceDur;

        scheduleSpin(() => {
            setActiveRingGlow(null);
            lockStep('race', 'races', folio.race_id, folio.race_name);
        }, tRaceDone);

        // Phase 2 : Armes
        scheduleSpin(() => {
            setActiveRingGlow('weapons');
            setStepActive('weapon');
            state.rotations.weapons = computeNextRotation(state.rotations.weapons, weaponGeo.midDeg, 3, false);
            applyRingSpin(ringW, state.rotations.weapons, durWeapon, 'cubic-bezier(0.16, 0.84, 0.22, 1)');
        }, tWeaponStart);

        scheduleSpin(() => {
            setActiveRingGlow(null);
            lockStep('weapon', 'weapons', folio.weapon_id, folio.weapon_name);
        }, tWeaponDone);

        // Phase 3 : Pouvoirs (décélération dramatique sur les 4% Mythiques)
        scheduleSpin(() => {
            setActiveRingGlow('powers');
            setStepActive('power');
            state.rotations.powers = computeNextRotation(state.rotations.powers, powerGeo.midDeg, isMythicPower ? 5 : 4, true);
            const powerEase = isMythicPower
                ? 'cubic-bezier(0.08, 0.90, 0.14, 1)'
                : 'cubic-bezier(0.14, 0.84, 0.20, 1)';
            applyRingSpin(ringP, state.rotations.powers, durPower, powerEase);
        }, tPowerStart);

        scheduleSpin(() => {
            setActiveRingGlow(null);
            lockStep('power', 'powers', folio.power_id, folio.power_name, folio.power_pigment_hex);
            if (contentEl) {
                contentEl.className = 'ritual-idle';
                contentEl.innerHTML = renderPowerSigilSvg(folio.power_id, folio.power_pigment_hex, 'ritual-sigil-trace');
            }
        }, tPowerDone);

        // Phase 4 : Révélation stricte après l'arrêt complet du dernier anneau
        scheduleSpin(() => {
            store.pendingFolio = folio;
            store.hud.pity_counter = isLuckySpin
                ? 0
                : Math.min(Number(store.hud.pity_threshold || 10), Number(store.hud.pity_counter || 0) + 1);
            state.spinning = false;
            saveStore();
            showRevealedFolio(folio, true);
            renderHud();
            renderEmergencyBar();
        }, tReveal);
    };

    const resolvePendingFolio = (action) => {
        if (!store.pendingFolio || state.spinning) {
            return;
        }

        const folio = store.pendingFolio;
        const statusEl = document.getElementById('ritual-status');
        store.pendingFolio = null;

        if (action === 'seal') {
            if (store.sourceMode === 'connected' && DATA.isAuthenticated) {
                store.connectedSealedFolios.unshift(folio);
            } else {
                store.showcaseSealedFolios.unshift(folio);
            }
            if (statusEl) {
                const grimoireUrl = DATA.routes?.grimoire || '/newda/grimoire';
                statusEl.innerHTML = `${escapeHtml(folio.name)} scellé · <a href="${escapeHtml(grimoireUrl)}">Voir le Grimoire →</a>`;
            }
        } else {
            const gained = Number(folio.recycle_value || 0);
            store.hud.coins = Number(store.hud.coins || 0) + gained;
            if (statusEl) {
                statusEl.textContent = `+${gained} écus`;
            }
        }

        saveStore();
        resetSequenceSteps();
        renderIdleRitual();
        if (state.renderedLuckyRing !== isPityReady()) {
            drawPowerRing(isPityReady());
        }
        renderHud();
        renderEmergencyBar();
    };

    const resetRingTransforms = () => {
        ['powers', 'weapons', 'races'].forEach((name) => {
            state.rotations[name] = 0;
            const el = document.getElementById(`ring-${name}`);
            if (el) {
                el.style.transitionDuration = '0ms';
                el.style.transform = 'rotate(0deg)';
                void el.getBoundingClientRect();
            }
        });
    };

    const snapRingsToFolio = (folio) => {
        const targets = [
            ['races', folio.race_id],
            ['weapons', folio.weapon_id],
            ['powers', folio.power_id],
        ];
        targets.forEach(([name, id]) => {
            const el = document.getElementById(`ring-${name}`);
            const geo = (state.ringsGeometry[name] || []).find((g) => g.id === id);
            if (!el || !geo) return;
            const angle = (360 - (geo.midDeg % 360) + 360) % 360;
            state.rotations[name] = angle;
            el.style.transitionDuration = '0ms';
            el.style.transform = `rotate(${angle}deg)`;
            void el.getBoundingClientRect();
        });
    };

    const hydrateInvocationPage = () => {
        if (store.pendingFolio) {
            const f = store.pendingFolio;
            const ringLucky = typeof f.was_lucky_spin === 'boolean'
                ? f.was_lucky_spin
                : (f.power_tier !== 'common' && isPityReady());
            drawAllVolvelleRings(ringLucky);
            snapRingsToFolio(f);
            lockStep('race', 'races', f.race_id, f.race_name);
            lockStep('weapon', 'weapons', f.weapon_id, f.weapon_name);
            lockStep('power', 'powers', f.power_id, f.power_name, f.power_pigment_hex);
            showRevealedFolio(f, false);
        } else {
            drawAllVolvelleRings();
        }
        renderEmergencyBar();
    };

    /* ======================================================================
       PAGE 2 — GRIMOIRE
       ====================================================================== */

    const renderGrimoire = () => {
        const gridEl = document.getElementById('folios-grid');
        const emptyEl = document.getElementById('gallery-empty-state');
        if (!gridEl) {
            return;
        }

        const pool = getActivePool();
        const filtered = state.rarityFilter === 'all'
            ? pool
            : pool.filter((f) => f.rarity === state.rarityFilter);

        const counts = { all: pool.length };
        pool.forEach((f) => {
            counts[f.rarity] = (counts[f.rarity] || 0) + 1;
        });
        document.querySelectorAll('[data-filter-count]').forEach((el) => {
            const key = el.getAttribute('data-filter-count') || 'all';
            el.textContent = String(counts[key] || 0);
        });

        const sourceBadge = document.getElementById('grimoire-source-badge');
        if (sourceBadge) {
            sourceBadge.hidden = !(DATA.isAuthenticated && store.sourceMode === 'showcase');
        }

        const isEmpty = filtered.length === 0;
        gridEl.hidden = isEmpty;
        gridEl.innerHTML = isEmpty ? '' : filtered.map((f) => renderFolioCardHtml(f)).join('');

        if (emptyEl) {
            emptyEl.hidden = !isEmpty;
            if (isEmpty) {
                const isFilteredOut = pool.length > 0 && state.rarityFilter !== 'all';
                const titleEl = document.getElementById('gallery-empty-title');
                const ctaEl = document.getElementById('gallery-empty-cta');
                const resetBtn = document.getElementById('gallery-reset-filter-btn');
                if (titleEl) {
                    titleEl.textContent = isFilteredOut ? 'Aucun folio dans cette rareté' : 'Grimoire vierge';
                }
                if (ctaEl) ctaEl.hidden = isFilteredOut;
                if (resetBtn) resetBtn.hidden = !isFilteredOut;
            }
        }
    };

    /* ======================================================================
       PAGE 3 — ARÈNE
       ====================================================================== */

    const clearDuelTimers = () => {
        state.duelTimers.forEach((id) => window.clearTimeout(id));
        state.duelTimers = [];
        state.duelRunning = false;
        const runBtn = document.getElementById('btn-run-duel');
        if (runBtn) runBtn.disabled = false;
    };

    const getArenaPool = () => {
        const active = getActivePool();
        if (active.length >= 2) {
            return active;
        }
        return state.arenaDemoMode ? (DATA.showcaseFolios || []) : active;
    };

    const getArenaFolio = (selectId, fallbackIdx) => {
        const pool = getArenaPool();
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
            <div class="folio-head" style="margin-bottom:8px;">
                <h3 class="folio-name">${escapeHtml(folio.name)}</h3>
                <span class="power-medallion tabular-nums">${escapeHtml(folio.power_score)}</span>
            </div>
            ${renderPortraitHtml(folio)}
            <div style="margin-top:10px;">
                ${renderTraitPillsHtml(folio)}
            </div>
            <div class="stat-bars tabular-nums" style="margin-top:10px;">
                <div class="stat-bar-row">
                    <span class="stat-bar-label">PV</span>
                    <div class="stat-track"><div class="stat-fill stat-fill--hp" data-duel-bar style="width:${pct}%;"></div></div>
                    <span class="stat-val" data-duel-hp>${hp}<small>/${folio.hp}</small></span>
                </div>
            </div>
        `;
    };

    const renderDuelCards = () => {
        clearDuelTimers();
        const pool = getArenaPool();
        if (pool.length < 2) {
            return;
        }
        const leftEl = document.getElementById('duel-card-left');
        const rightEl = document.getElementById('duel-card-right');
        const chronicleEl = document.getElementById('duel-chronicle');
        const leftFolio = getArenaFolio('duel-left-select', 0);
        const rightFolio = getArenaFolio('duel-right-select', Math.min(4, pool.length - 1));

        [leftEl, rightEl].forEach((el) => el && el.classList.remove('is-winner', 'is-loser', 'is-channeling', 'is-hit'));
        renderSingleDuelCard(leftEl, leftFolio, leftFolio.hp);
        renderSingleDuelCard(rightEl, rightFolio, rightFolio.hp);
        if (chronicleEl) {
            chronicleEl.innerHTML = '';
        }
    };

    const syncArenaView = () => {
        const banner = document.getElementById('arena-insufficient-banner');
        const wrap = document.getElementById('arena-workbench-wrap');
        const demoBar = document.getElementById('arena-demo-bar');
        const leftSel = document.getElementById('duel-left-select');
        const rightSel = document.getElementById('duel-right-select');
        if (!banner || !wrap || !leftSel || !rightSel) {
            return;
        }

        const realPool = getActivePool();
        const usingDemo = realPool.length < 2 && state.arenaDemoMode;
        const pool = usingDemo ? (DATA.showcaseFolios || []) : realPool;
        const canFight = pool.length >= 2;

        banner.hidden = canFight;
        wrap.hidden = !canFight;
        if (demoBar) {
            demoBar.hidden = !usingDemo;
        }
        if (!canFight) {
            clearDuelTimers();
            return;
        }

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

        if (leftSel.value === rightSel.value && pool.length >= 2) {
            const alternate = pool.find((f) => f.id !== leftSel.value);
            if (alternate) {
                rightSel.value = alternate.id;
            }
        }

        state.prevArenaLeft = leftSel.value;
        state.prevArenaRight = rightSel.value;

        renderDuelCards();
    };

    const handleArenaSelectionChange = (changedSide) => {
        const leftSel = document.getElementById('duel-left-select');
        const rightSel = document.getElementById('duel-right-select');
        const pool = getArenaPool();
        if (!leftSel || !rightSel || pool.length < 2) {
            renderDuelCards();
            return;
        }

        if (leftSel.value === rightSel.value) {
            if (changedSide === 'left') {
                const swapCandidate = pool.some((f) => f.id === state.prevArenaLeft && f.id !== leftSel.value)
                    ? state.prevArenaLeft
                    : (pool.find((f) => f.id !== leftSel.value)?.id || rightSel.value);
                rightSel.value = swapCandidate;
            } else {
                const swapCandidate = pool.some((f) => f.id === state.prevArenaRight && f.id !== rightSel.value)
                    ? state.prevArenaRight
                    : (pool.find((f) => f.id !== rightSel.value)?.id || leftSel.value);
                leftSel.value = swapCandidate;
            }
        }

        state.prevArenaLeft = leftSel.value;
        state.prevArenaRight = rightSel.value;
        renderDuelCards();
    };

    const runDuelSimulation = () => {
        if (state.duelRunning) {
            return;
        }
        const pool = getArenaPool();
        if (pool.length < 2) {
            return;
        }
        renderDuelCards();
        state.duelRunning = true;
        const runBtn = document.getElementById('btn-run-duel');
        if (runBtn) runBtn.disabled = true;

        const leftFolio = getArenaFolio('duel-left-select', 0);
        const rightFolio = getArenaFolio('duel-right-select', Math.min(4, pool.length - 1));
        const leftEl = document.getElementById('duel-card-left');
        const rightEl = document.getElementById('duel-card-right');
        const chronicleEl = document.getElementById('duel-chronicle');
        if (chronicleEl) chronicleEl.innerHTML = '';

        const fighters = [
            { folio: leftFolio, hp: leftFolio.hp, el: leftEl },
            { folio: rightFolio, hp: rightFolio.hp, el: rightEl },
        ];

        const firstIdx = fighters[0].folio.speed >= fighters[1].folio.speed ? 0 : 1;
        const turns = [];
        const simHp = [fighters[0].hp, fighters[1].hp];

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
                });
            }
        }

        const winnerIdx = simHp[0] >= simHp[1] ? 0 : 1;
        const stepMs = prefersReducedMotion ? 40 : 540;

        turns.forEach((turn, idx) => {
            const tId = window.setTimeout(() => {
                const atkF = fighters[turn.atkIdx];
                const defF = fighters[turn.defIdx];
                fighters.forEach((f) => f.el && f.el.classList.remove('is-channeling', 'is-hit'));
                if (atkF.el) atkF.el.classList.add('is-channeling');
                if (defF.el) {
                    void defF.el.offsetWidth;
                    defF.el.classList.add('is-hit');
                    const hpSpan = defF.el.querySelector('[data-duel-hp]');
                    const hpBar = defF.el.querySelector('[data-duel-bar]');
                    if (hpSpan) hpSpan.innerHTML = `${turn.remainingHp}<small>/${defF.folio.hp}</small>`;
                    if (hpBar) hpBar.style.width = `${Math.max(0, Math.round((turn.remainingHp / defF.folio.hp) * 100))}%`;
                }
                if (chronicleEl) {
                    const row = document.createElement('div');
                    row.className = 'duel-log-row tabular-nums';
                    row.innerHTML = `
                        <span class="duel-log-round">T${turn.round}</span>
                        <span style="color:${escapeHtml(atkF.folio.power_pigment_hex)};display:inline-flex;">
                            ${renderHtmlIcon('powers', atkF.folio.power_id, 'duel-log-icon', atkF.folio.power_name)}
                        </span>
                        <span>${escapeHtml(atkF.folio.name)}</span>
                        <span class="duel-log-dmg">-${turn.dmg} PV</span>
                        <span class="duel-log-hp">${turn.remainingHp} PV</span>
                    `;
                    chronicleEl.appendChild(row);
                }
            }, idx * stepMs);
            state.duelTimers.push(tId);
        });

        const endTimer = window.setTimeout(() => {
            fighters.forEach((f) => f.el && f.el.classList.remove('is-channeling', 'is-hit'));
            const winner = fighters[winnerIdx];
            const loser = fighters[1 - winnerIdx];
            if (winner.el) winner.el.classList.add('is-winner');
            if (loser.el) loser.el.classList.add('is-loser');

            const mults = CONFIG.workshop?.arena_bonus?.multipliers || [1.25, 1.5, 2.0];
            const bonusLvl = Math.max(0, Number(store.workshop.arena_bonus || 0));
            const multiplier = bonusLvl > 0 ? Number(mults[bonusLvl - 1] || 1) : 1;
            const baseWinCoins = Number(CONFIG.rewards?.win_coins_base ?? 65);
            const rewardCoins = Math.round(baseWinCoins * multiplier);

            store.hud.coins = Number(store.hud.coins || 0) + rewardCoins;
            saveStore();
            renderHud();

            if (chronicleEl) {
                const row = document.createElement('div');
                row.className = 'duel-log-row duel-log-row--verdict tabular-nums';
                row.innerHTML = `
                    <span>✦ ${escapeHtml(winner.folio.name)}</span>
                    <span class="duel-log-dmg" style="color:var(--gold-dark);">+${rewardCoins} écus</span>
                `;
                chronicleEl.appendChild(row);
            }
            state.duelRunning = false;
            const doneRunBtn = document.getElementById('btn-run-duel');
            if (doneRunBtn) doneRunBtn.disabled = false;
        }, turns.length * stepMs + 180);
        state.duelTimers.push(endTimer);
    };

    /* ======================================================================
       PAGE 4 — RANG (5 LIGUES & MODALE D'INSPECTION)
       ====================================================================== */

    const findRivalById = (rivalId) => {
        const standings = DATA.standingsByLeague || {};
        for (const leagueKey of Object.keys(standings)) {
            const match = (standings[leagueKey] || []).find((entry) => entry.id === rivalId);
            if (match) {
                return match;
            }
        }
        return null;
    };

    const renderLeaderboardLeague = (leagueValue) => {
        const podiumEl = document.getElementById('rang-podium');
        const tableEl = document.getElementById('rang-table');
        if (!podiumEl || !tableEl) {
            return;
        }

        state.activeLeague = leagueValue;
        document.querySelectorAll('[data-league-tab]').forEach((tab) => {
            const active = tab.getAttribute('data-league-tab') === leagueValue;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.setAttribute('tabindex', active ? '0' : '-1');
        });
        const panelEl = document.getElementById('rang-tabpanel');
        if (panelEl) {
            panelEl.setAttribute('aria-labelledby', `league-tab-${leagueValue}`);
        }

        const entries = (DATA.standingsByLeague && DATA.standingsByLeague[leagueValue]) || [];
        const top3 = entries.slice(0, 3);
        const podiumOrder = [top3[1], top3[0], top3[2]].filter(Boolean);
        const rest = entries.slice(3);

        podiumEl.innerHTML = podiumOrder.map((entry) => `
            <button
                type="button"
                class="podium-card podium-card--rank-${entry.rank}"
                data-inspect-rival="${escapeHtml(entry.id)}"
                style="--league-color:${escapeHtml(entry.league_color)};"
            >
                <span class="podium-rank tabular-nums">#${entry.rank}</span>
                <strong class="podium-name">${escapeHtml(entry.name)}</strong>
                <span class="podium-elo tabular-nums">${Number(entry.elo).toLocaleString('fr-FR')}</span>
                <span class="podium-hero-chip">
                    ${renderHtmlIcon('races', entry.champion.race_id, 'podium-mini-icon', entry.champion.race_name)}
                    <span>${escapeHtml(entry.champion.name)}</span>
                </span>
            </button>
        `).join('');

        tableEl.innerHTML = rest.map((entry) => `
            <div class="rang-row">
                <span class="rang-pos tabular-nums">#${entry.rank}</span>
                <span class="rang-name">${escapeHtml(entry.name)}</span>
                <span class="rang-hero">
                    ${renderHtmlIcon('races', entry.champion.race_id, 'rang-mini-icon', entry.champion.race_name)}
                    <span>${escapeHtml(entry.champion.name)}</span>
                </span>
                <span class="rang-winrate tabular-nums" title="${entry.wins}V / ${entry.losses}D">${entry.winrate}%</span>
                <span class="rang-elo tabular-nums">${Number(entry.elo).toLocaleString('fr-FR')}</span>
                <button type="button" class="rang-inspect-btn" data-inspect-rival="${escapeHtml(entry.id)}" aria-label="Inspecter ${escapeHtml(entry.name)}" title="Inspecter">
                    <svg class="hud-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M2 12 Q12 4 22 12 Q12 20 2 12 Z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3.2" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        `).join('');
    };

    const openRivalInspector = (rivalId, triggerEl = null) => {
        const modal = document.getElementById('rang-inspector-modal');
        const titleEl = document.getElementById('inspector-title');
        const metaEl = document.getElementById('inspector-meta');
        const bodyEl = document.getElementById('inspector-body');
        const rival = findRivalById(rivalId);
        if (!modal || !rival) {
            return;
        }

        state.lastInspectorTrigger = triggerEl || document.activeElement;
        if (titleEl) titleEl.textContent = rival.name;
        if (metaEl) metaEl.textContent = `${rival.league_label} · ${Number(rival.elo).toLocaleString('fr-FR')} Élo · ${rival.wins}V / ${rival.losses}D`;
        if (bodyEl) {
            bodyEl.innerHTML = `
                ${renderPortraitHtml(rival.champion)}
                <div style="text-align:center;">
                    <strong class="folio-name">${escapeHtml(rival.champion.name)}</strong>
                </div>
                ${renderTraitPillsHtml(rival.champion)}
                ${renderStatBarsHtml(rival.champion)}
            `;
        }
        modal.hidden = false;
        document.getElementById('btn-inspector-close')?.focus();
    };

    const closeRivalInspector = (restoreFocus = false) => {
        const modal = document.getElementById('rang-inspector-modal');
        if (!modal || modal.hidden) {
            return;
        }
        modal.hidden = true;
        const trigger = state.lastInspectorTrigger;
        state.lastInspectorTrigger = null;
        if (restoreFocus && trigger && typeof trigger.focus === 'function' && document.contains(trigger)) {
            trigger.focus();
        }
    };

    /* ======================================================================
       PAGE 5 — ATELIER (3 CARTES SYMÉTRIQUES INTERACTIVES)
       ====================================================================== */

    const renderWorkshop = (justBoughtKey = null) => {
        const grid = document.getElementById('workshop-cards-grid');
        if (!grid || !DATA.workshopCards) {
            return;
        }

        const coins = Number(store.hud.coins || 0);

        DATA.workshopCards.forEach((card) => {
            const el = grid.querySelector(`[data-workshop-card="${card.key}"]`);
            if (!el) return;

            const lvl = Math.min(card.max_level, Math.max(0, Number(store.workshop[card.key] || 0)));
            const isMaxed = lvl >= card.max_level;
            const nextCost = isMaxed ? null : Number(card.costs[lvl] || 0);
            const canAfford = !isMaxed && coins >= nextCost;

            el.classList.toggle('is-maxed', isMaxed);
            if (justBoughtKey === card.key) {
                el.classList.remove('is-just-bought');
                void el.offsetWidth;
                el.classList.add('is-just-bought');
            }

            const lvlEl = el.querySelector('[data-ws-level]');
            if (lvlEl) lvlEl.textContent = `${lvl}/${card.max_level}`;

            const segsEl = el.querySelector('[data-ws-segments]');
            if (segsEl) {
                let segs = '';
                for (let i = 1; i <= card.max_level; i++) {
                    segs += `<span class="workshop-seg ${i <= lvl ? 'is-filled' : ''}"></span>`;
                }
                segsEl.innerHTML = segs;
            }

            const bonusEl = el.querySelector('[data-ws-bonus]');
            if (bonusEl) {
                if (card.key === 'rare_magnet') {
                    const cur = lvl * card.bonus_per_level;
                    const nxt = (lvl + 1) * card.bonus_per_level;
                    bonusEl.innerHTML = `<span class="ws-current">+${cur}% Mythique</span>${isMaxed ? '' : `<span class="ws-arrow">→</span><span class="ws-next">+${nxt}%</span>`}`;
                } else if (card.key === 'hero_forge') {
                    const curHp = lvl * card.bonus_hp;
                    const curStr = lvl * card.bonus_str;
                    const nxtHp = (lvl + 1) * card.bonus_hp;
                    const nxtStr = (lvl + 1) * card.bonus_str;
                    bonusEl.innerHTML = `<span class="ws-current">+${curHp} PV · +${curStr} FOR</span>${isMaxed ? '' : `<span class="ws-arrow">→</span><span class="ws-next">+${nxtHp} · +${nxtStr}</span>`}`;
                } else {
                    const curMult = lvl > 0 ? Number(card.multipliers[lvl - 1]) : 1;
                    const nxtMult = !isMaxed ? Number(card.multipliers[lvl]) : null;
                    const fmt = (n) => n.toFixed(2).replace('.', ',');
                    bonusEl.innerHTML = `<span class="ws-current">×${fmt(curMult)} Arène</span>${nxtMult !== null ? `<span class="ws-arrow">→</span><span class="ws-next">×${fmt(nxtMult)}</span>` : ''}`;
                }
            }

            const btn = el.querySelector('[data-workshop-buy]');
            if (btn) {
                btn.disabled = isMaxed || !canAfford;
                btn.innerHTML = isMaxed
                    ? '<span>Max</span>'
                    : `<span>Améliorer · ${nextCost.toLocaleString('fr-FR')}</span>`;
            }
        });
    };

    const purchaseWorkshopUpgrade = (key) => {
        const card = (DATA.workshopCards || []).find((c) => c.key === key);
        if (!card) return;

        const lvl = Math.max(0, Number(store.workshop[key] || 0));
        if (lvl >= card.max_level) return;

        const cost = Number(card.costs[lvl] || 0);
        if (Number(store.hud.coins || 0) < cost) return;

        store.hud.coins = Number(store.hud.coins || 0) - cost;
        store.workshop[key] = lvl + 1;
        saveStore();
        renderHud();
        renderWorkshop(key);
    };

    /* ======================================================================
       ÉCOUTEURS D'ÉVÉNEMENTS & TIROIR ⚙ BAC À SABLE
       ====================================================================== */

    const setSandboxOpen = (open, restoreFocus = false) => {
        const drawer = document.getElementById('sandbox-drawer');
        const trigger = document.getElementById('btn-sandbox-toggle');
        if (!drawer || !trigger) return;
        const wasOpen = !drawer.hidden;
        drawer.hidden = !open;
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (!open && wasOpen && restoreFocus) {
            trigger.focus();
        }
    };

    const bindEvents = () => {
        // Tiroir ⚙ Bac à sable
        document.getElementById('btn-sandbox-toggle')?.addEventListener('click', () => {
            const drawer = document.getElementById('sandbox-drawer');
            setSandboxOpen(drawer ? drawer.hidden : false);
        });
        document.getElementById('btn-sandbox-close')?.addEventListener('click', () => setSandboxOpen(false, true));

        document.addEventListener('click', (e) => {
            const dock = document.querySelector('.sandbox-dock');
            const drawer = document.getElementById('sandbox-drawer');
            if (dock && drawer && !drawer.hidden && !dock.contains(e.target)) {
                setSandboxOpen(false);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                setSandboxOpen(false, true);
                closeRivalInspector(true);
            }
        });

        document.querySelectorAll('[data-sandbox-pool]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (state.spinning) return;
                store.humanOnlyPool = btn.getAttribute('data-sandbox-pool') === 'human';
                saveStore();
                renderHud();
                drawAllVolvelleRings();
            });
        });

        document.getElementById('btn-charge-pity')?.addEventListener('click', () => {
            if (state.spinning) return;
            store.hud.pity_counter = Number(store.hud.pity_threshold || 10);
            saveStore();
            renderHud();
            const statusEl = document.getElementById('ritual-status');
            if (statusEl) statusEl.textContent = '';
            if (!store.pendingFolio) {
                drawPowerRing(true);
            }
        });

        document.getElementById('btn-refill-ink')?.addEventListener('click', () => {
            store.hud.spin_charges = Number(store.hud.spin_max || 3);
            saveStore();
            renderHud();
            renderEmergencyBar();
            const statusEl = document.getElementById('ritual-status');
            if (statusEl) statusEl.textContent = '';
        });

        document.getElementById('btn-add-coins')?.addEventListener('click', () => {
            store.hud.coins = Number(store.hud.coins || 0) + 500;
            saveStore();
            renderHud();
            renderEmergencyBar();
            renderWorkshop();
        });

        document.querySelectorAll('[data-source-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!DATA.isAuthenticated) return;
                store.sourceMode = btn.getAttribute('data-source-toggle') === 'showcase' ? 'showcase' : 'connected';
                state.arenaDemoMode = false;
                saveStore();
                renderHud();
                renderGrimoire();
                syncArenaView();
            });
        });

        document.getElementById('btn-sandbox-reset')?.addEventListener('click', () => {
            clearSpinTimers();
            clearDuelTimers();
            state.spinning = false;
            state.arenaDemoMode = false;
            state.rarityFilter = 'all';
            document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-rarity-filter') === 'all');
            });
            clearStore();
            setActiveRingGlow(null);
            resetRingTransforms();
            resetSequenceSteps();
            renderIdleRitual();
            drawAllVolvelleRings();
            renderHud();
            renderEmergencyBar();
            renderGrimoire();
            syncArenaView();
            renderWorkshop();
            setSandboxOpen(false);
        });

        // Tirage / Volvelle
        document.getElementById('btn-invoke-seal')?.addEventListener('click', runInvocationRitual);
        document.getElementById('btn-seal-folio')?.addEventListener('click', () => resolvePendingFolio('seal'));
        document.getElementById('btn-transmute-folio')?.addEventListener('click', () => resolvePendingFolio('transmute'));

        document.getElementById('btn-emergency-spin')?.addEventListener('click', () => {
            const costs = CONFIG.workshop?.emergency_spin?.costs || [120, 250];
            const idx = Number(store.emergencyPurchases || 0);
            if (idx >= costs.length) return;
            const cost = Number(costs[idx]);
            if (Number(store.hud.coins || 0) < cost) return;

            store.hud.coins -= cost;
            store.hud.spin_charges = Math.min(Number(store.hud.spin_max || 3), Number(store.hud.spin_charges || 0) + 1);
            store.emergencyPurchases = idx + 1;
            saveStore();
            renderHud();
            renderEmergencyBar();
            const statusEl = document.getElementById('ritual-status');
            if (statusEl) statusEl.textContent = '';
        });

        // Flip 3D (délégation globale : Grimoire + Reveal Tirage)
        document.addEventListener('click', (e) => {
            const flipBtn = e.target.closest('[data-flip-folio]');
            if (!flipBtn) return;
            const card = flipBtn.closest('.folio-card');
            if (card) {
                const flipped = card.classList.toggle('is-flipped');
                const front = card.querySelector('.folio-face--front');
                const back = card.querySelector('.folio-face--back');
                if (front) front.setAttribute('aria-hidden', flipped ? 'true' : 'false');
                if (back) back.setAttribute('aria-hidden', flipped ? 'false' : 'true');
                const frontBtn = front?.querySelector('[data-flip-folio]');
                const backBtn = back?.querySelector('[data-flip-folio]');
                if (frontBtn) frontBtn.setAttribute('tabindex', flipped ? '-1' : '0');
                if (backBtn) backBtn.setAttribute('tabindex', flipped ? '0' : '-1');
                const nextBtn = flipped ? backBtn : frontBtn;
                if (nextBtn) nextBtn.focus();
            }
        });

        // Grimoire filtres
        document.querySelectorAll('[data-rarity-filter]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.rarityFilter = btn.getAttribute('data-rarity-filter') || 'all';
                document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                    b.classList.toggle('is-active', b === btn);
                });
                renderGrimoire();
            });
        });

        document.getElementById('gallery-reset-filter-btn')?.addEventListener('click', () => {
            state.rarityFilter = 'all';
            document.querySelectorAll('[data-rarity-filter]').forEach((b) => {
                b.classList.toggle('is-active', b.getAttribute('data-rarity-filter') === 'all');
            });
            renderGrimoire();
        });

        // Arène
        document.getElementById('btn-arena-demo')?.addEventListener('click', () => {
            state.arenaDemoMode = true;
            syncArenaView();
        });
        document.getElementById('btn-arena-exit-demo')?.addEventListener('click', () => {
            state.arenaDemoMode = false;
            syncArenaView();
        });
        document.getElementById('duel-left-select')?.addEventListener('change', () => handleArenaSelectionChange('left'));
        document.getElementById('duel-right-select')?.addEventListener('change', () => handleArenaSelectionChange('right'));
        document.getElementById('btn-run-duel')?.addEventListener('click', runDuelSimulation);
        document.getElementById('btn-reset-duel')?.addEventListener('click', renderDuelCards);

        // Rang
        const leagueTabs = Array.from(document.querySelectorAll('[data-league-tab]'));
        leagueTabs.forEach((tab, idx) => {
            tab.addEventListener('click', () => {
                const league = tab.getAttribute('data-league-tab');
                if (league) renderLeaderboardLeague(league);
            });
            tab.addEventListener('keydown', (e) => {
                let targetIdx = null;
                if (e.key === 'ArrowRight') targetIdx = (idx + 1) % leagueTabs.length;
                else if (e.key === 'ArrowLeft') targetIdx = (idx - 1 + leagueTabs.length) % leagueTabs.length;
                else if (e.key === 'Home') targetIdx = 0;
                else if (e.key === 'End') targetIdx = leagueTabs.length - 1;
                if (targetIdx === null) return;
                e.preventDefault();
                const nextTab = leagueTabs[targetIdx];
                const league = nextTab?.getAttribute('data-league-tab');
                if (league) {
                    renderLeaderboardLeague(league);
                    nextTab.focus();
                }
            });
        });

        document.addEventListener('click', (e) => {
            const inspectBtn = e.target.closest('[data-inspect-rival]');
            if (inspectBtn) {
                openRivalInspector(inspectBtn.getAttribute('data-inspect-rival'), inspectBtn);
            }
        });

        document.getElementById('btn-inspector-close')?.addEventListener('click', () => closeRivalInspector(true));
        document.getElementById('rang-inspector-modal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeRivalInspector(true);
            }
        });

        // Atelier
        document.getElementById('workshop-cards-grid')?.addEventListener('click', (e) => {
            const buyBtn = e.target.closest('[data-workshop-buy]');
            if (!buyBtn || buyBtn.disabled) return;
            purchaseWorkshopUpgrade(buyBtn.getAttribute('data-workshop-buy'));
        });
    };

    /* ======================================================================
       INITIALISATION PAR PAGE
       ====================================================================== */

    renderHud();
    if (PAGE === 'invocation') {
        hydrateInvocationPage();
    } else if (PAGE === 'grimoire') {
        renderGrimoire();
    } else if (PAGE === 'arena') {
        syncArenaView();
    } else if (PAGE === 'workshop') {
        renderWorkshop();
    }
    bindEvents();
})();
