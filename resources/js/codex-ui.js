const flippedKeys = new Set();

const tooltipState = {
    anchor: null,
    pinned: false,
};

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const getTooltipEl = () => document.getElementById('codex-tooltip-portal');

export const hideCodexTooltip = () => {
    const tip = getTooltipEl();
    if (tooltipState.anchor) {
        tooltipState.anchor.classList.remove('is-tooltip-open');
    }
    tooltipState.anchor = null;
    tooltipState.pinned = false;
    if (!tip) return;
    tip.classList.remove('is-visible', 'is-below', 'is-unstable', 'is-lucky', 'is-power');
    tip.hidden = true;
};

const positionTooltip = (anchor, tip) => {
    const rect = anchor.getBoundingClientRect();
    const tipRect = tip.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const pad = 12;
    const gap = 10;

    let top = rect.top - tipRect.height - gap;
    let placeBelow = false;
    if (top < pad) {
        top = rect.bottom + gap;
        placeBelow = true;
    }
    if (placeBelow && top + tipRect.height > vh - pad) {
        top = Math.max(pad, vh - tipRect.height - pad);
    }

    const anchorCenter = rect.left + rect.width / 2;
    let left = anchorCenter - tipRect.width / 2;
    left = Math.max(pad, Math.min(vw - tipRect.width - pad, left));

    const arrowOffset = Math.max(18, Math.min(tipRect.width - 18, anchorCenter - left));
    tip.classList.toggle('is-below', placeBelow);
    tip.style.left = `${Math.round(left)}px`;
    tip.style.top = `${Math.round(top)}px`;
    tip.style.setProperty('--arrow-left', `${Math.round(arrowOffset)}px`);
};

const showCodexTooltip = (anchor, pinned = false) => {
    const tip = getTooltipEl();
    if (!tip || !anchor) return;

    const title = anchor.getAttribute('data-tooltip-title') || '';
    const desc = anchor.getAttribute('data-tooltip-desc') || '';
    const eyebrow = anchor.getAttribute('data-tooltip-eyebrow') || '';
    const tone = anchor.getAttribute('data-tooltip-tone') || 'default';
    const hex = anchor.getAttribute('data-tooltip-hex') || '';
    if (!title && !desc) return;

    if (tooltipState.anchor && tooltipState.anchor !== anchor) {
        tooltipState.anchor.classList.remove('is-tooltip-open');
    }
    tooltipState.anchor = anchor;
    tooltipState.pinned = pinned;
    anchor.classList.add('is-tooltip-open');

    tip.classList.remove('is-visible', 'is-below', 'is-unstable', 'is-lucky', 'is-power');
    if (tone === 'unstable') tip.classList.add('is-unstable');
    else if (tone === 'lucky') tip.classList.add('is-lucky');
    else if (tone === 'power') tip.classList.add('is-power');

    if (hex) {
        tip.style.setProperty('--tooltip-accent', hex);
    } else {
        tip.style.removeProperty('--tooltip-accent');
    }

    tip.innerHTML = `
        ${eyebrow ? `<span class="codex-tooltip-eyebrow">${escapeHtml(eyebrow)}</span>` : ''}
        <strong class="codex-tooltip-title">${escapeHtml(title)}</strong>
        ${desc ? `<div class="codex-tooltip-divider"></div><p class="codex-tooltip-desc">${escapeHtml(desc)}</p>` : ''}
        <span class="codex-tooltip-arrow" aria-hidden="true"></span>
    `;
    tip.hidden = false;
    positionTooltip(anchor, tip);
    void tip.offsetWidth;
    tip.classList.add('is-visible');
};

const applyFlipAttributes = (card, flipped) => {
    card.classList.toggle('is-flipped', flipped);
    const front = card.querySelector('.folio-face--front');
    const back = card.querySelector('.folio-face--back');

    if (front) {
        front.setAttribute('aria-hidden', flipped ? 'true' : 'false');
        front.inert = flipped;
        front.querySelectorAll('button, a, [tabindex]').forEach((el) => {
            el.setAttribute('tabindex', flipped ? '-1' : '0');
        });
    }
    if (back) {
        back.setAttribute('aria-hidden', flipped ? 'false' : 'true');
        back.inert = !flipped;
        back.querySelectorAll('button, a, [tabindex]').forEach((el) => {
            el.setAttribute('tabindex', flipped ? '0' : '-1');
        });
    }
};

const cardKeyOf = (card) => card.getAttribute('data-folio-key') || card.getAttribute('data-folio-id') || '';

const toggleFolioFlip = (card) => {
    if (!card) return;
    hideCodexTooltip();
    const flipped = !card.classList.contains('is-flipped');
    const key = cardKeyOf(card);
    if (key) {
        if (flipped) flippedKeys.add(key);
        else flippedKeys.delete(key);
    }
    applyFlipAttributes(card, flipped);
};

export const restoreFlippedCards = () => {
    if (flippedKeys.size === 0) return;
    document.querySelectorAll('.folio-card').forEach((card) => {
        const key = cardKeyOf(card);
        if (key && flippedKeys.has(key) && !card.classList.contains('is-flipped')) {
            applyFlipAttributes(card, true);
        }
    });
};

let initialized = false;

export const initCodexUi = () => {
    if (initialized) return;
    initialized = true;

    document.addEventListener('click', (e) => {
        if (tooltipState.pinned && tooltipState.anchor && !tooltipState.anchor.contains(e.target)) {
            hideCodexTooltip();
        }

        const tooltipTapTarget = e.target.closest?.('[data-no-flip][data-tooltip-title]');
        if (tooltipTapTarget) {
            e.stopPropagation();
            const hiddenFace = tooltipTapTarget.closest('.folio-face[aria-hidden="true"]');
            if (hiddenFace) return;
            if (tooltipState.anchor === tooltipTapTarget && tooltipState.pinned) {
                hideCodexTooltip();
            } else {
                showCodexTooltip(tooltipTapTarget, true);
            }
            return;
        }

        const card = e.target.closest?.('.folio-card');
        if (!card) return;
        if (e.target.closest('button, a, input, select, textarea, label, [data-no-flip]')) {
            return;
        }
        toggleFolioFlip(card);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideCodexTooltip();
            return;
        }
        if ((e.key === 'Enter' || e.key === ' ') && e.target instanceof Element && e.target.classList.contains('folio-card')) {
            e.preventDefault();
            toggleFolioFlip(e.target);
        }
    });

    document.addEventListener('mouseover', (e) => {
        const target = e.target.closest?.('[data-tooltip-title]');
        if (!target || target === tooltipState.anchor) return;
        if (target.closest('.folio-face[aria-hidden="true"]')) return;
        showCodexTooltip(target, false);
    });

    document.addEventListener('mouseout', (e) => {
        const target = e.target.closest?.('[data-tooltip-title]');
        if (!target || target !== tooltipState.anchor || tooltipState.pinned) return;
        const related = e.relatedTarget;
        if (related && target.contains(related)) return;
        hideCodexTooltip();
    });

    document.addEventListener('focusin', (e) => {
        const target = e.target.closest?.('[data-tooltip-title]');
        if (!target) return;
        if (target.closest('.folio-face[aria-hidden="true"]')) return;
        showCodexTooltip(target, false);
    });

    document.addEventListener('focusout', (e) => {
        const target = e.target.closest?.('[data-tooltip-title]');
        if (!target || target !== tooltipState.anchor) return;
        hideCodexTooltip();
    });

    window.addEventListener('scroll', hideCodexTooltip, { passive: true });
    window.addEventListener('resize', hideCodexTooltip, { passive: true });

    const hookLivewire = () => {
        if (!window.Livewire || typeof window.Livewire.hook !== 'function') return;

        window.Livewire.hook('morph.updating', ({ el, toEl }) => {
            if (el instanceof Element && toEl instanceof Element && el.classList.contains('folio-card')) {
                const key = cardKeyOf(el);
                if (el.classList.contains('is-flipped') || (key && flippedKeys.has(key))) {
                    applyFlipAttributes(toEl, true);
                }
            }
        });

        window.Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                queueMicrotask(() => {
                    restoreFlippedCards();
                    if (tooltipState.anchor && !document.contains(tooltipState.anchor)) {
                        hideCodexTooltip();
                    }
                });
            });
        });
    };

    if (window.Livewire) {
        hookLivewire();
    } else {
        document.addEventListener('livewire:init', hookLivewire, { once: true });
    }
};

/* ==========================================================================
   VOLVELLE ARCANIQUE ALPINE (Races -> pause -> Armes -> pause -> Pouvoirs)
   ========================================================================== */

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

export function codexVolvelle(config = {}) {
    return {
        wheels: config.wheels || {},
        icons: config.icons || { races: {}, weapons: {}, powers: {} },
        pigments: config.pigments || {},
        rotations: {
            race: Number(config.initialRotations?.race ?? 0),
            weapon: Number(config.initialRotations?.weapon ?? 0),
            power: Number(config.initialRotations?.power ?? 0),
        },
        activeRing: null,
        stepState: {
            race: config.initialSteps?.race || null,
            weapon: config.initialSteps?.weapon || null,
            power: config.initialSteps?.power || null,
        },
        animating: false,
        timers: [],
        boundHandlers: null,
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,

        init() {
            this.drawAllRings();
            this.applyInstantRotations();

            const onSpin = (ev) => {
                const detail = Array.isArray(ev.detail) ? (ev.detail[0] || {}) : (ev.detail || {});
                this.runSequence(detail);
            };

            const onReset = () => {
                this.clearTimers();
                this.activeRing = null;
                this.animating = false;
                this.stepState = { race: null, weapon: null, power: null };
            };

            const onSyncSteps = (ev) => {
                const detail = Array.isArray(ev.detail) ? (ev.detail[0] || {}) : (ev.detail || {});
                if (detail.steps) {
                    this.stepState = detail.steps;
                }
                if (detail.rotations) {
                    this.rotations = {
                        race: Number(detail.rotations.race ?? this.rotations.race),
                        weapon: Number(detail.rotations.weapon ?? this.rotations.weapon),
                        power: Number(detail.rotations.power ?? this.rotations.power),
                    };
                    this.applyInstantRotations();
                }
            };

            this.boundHandlers = { onSpin, onReset, onSyncSteps };
            window.addEventListener('codex-volvelle-spin', onSpin);
            window.addEventListener('codex-volvelle-reset', onReset);
            window.addEventListener('codex-volvelle-sync-steps', onSyncSteps);
        },

        destroy() {
            this.clearTimers();
            if (this.boundHandlers) {
                window.removeEventListener('codex-volvelle-spin', this.boundHandlers.onSpin);
                window.removeEventListener('codex-volvelle-reset', this.boundHandlers.onReset);
                window.removeEventListener('codex-volvelle-sync-steps', this.boundHandlers.onSyncSteps);
                this.boundHandlers = null;
            }
        },

        clearTimers() {
            this.timers.forEach((id) => window.clearTimeout(id));
            this.timers = [];
        },

        schedule(fn, delay) {
            const id = window.setTimeout(fn, delay);
            this.timers.push(id);
            return id;
        },

        resolveIcon(category, id) {
            const cat = this.icons[category] || {};
            return cat[id] || { type: 'svg', viewBox: '0 0 48 48', markup: '' };
        },

        renderSectorIcon(category, id, x, y, midDeg, size, iconColor) {
            const icon = this.resolveIcon(category, id);
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
        },

        drawRing(el, category, segments, rInner, rOuter, options = {}) {
            if (!el || !Array.isArray(segments) || segments.length === 0) return;
            const cx = 250;
            const cy = 250;
            let svgMarkup = '';

            segments.forEach((seg, idx) => {
                const startDeg = Number(seg.start ?? 0);
                const endDeg = Number(seg.end ?? 0);
                const midDeg = startDeg + (endDeg - startDeg) / 2;
                const path = annularSectorPath(cx, cy, rInner, rOuter, startDeg, endDeg);
                const fill = options.fillFor ? options.fillFor(seg, idx) : (idx % 2 === 0 ? '#EFE4CC' : '#DFCFAF');
                const iconColor = options.iconFor ? options.iconFor(seg, idx) : '#2A1D11';
                const rMid = (rInner + rOuter) / 2;
                const pos = polarToCartesian(cx, cy, rMid, midDeg);
                const iconSize = options.iconSize || 28;

                svgMarkup += `<g><title>${escapeHtml(seg.name || seg.id)}</title>`;
                svgMarkup += `<path d="${path}" fill="${fill}" stroke="#4A3618" stroke-width="1.15"/>`;
                svgMarkup += this.renderSectorIcon(category, seg.id, pos.x, pos.y, midDeg, iconSize, iconColor);
                svgMarkup += '</g>';
            });

            el.innerHTML = svgMarkup;
        },

        drawAllRings() {
            this.drawRing(this.$refs.ringPowers, 'powers', this.wheels.power?.segments || [], 184, 242, {
                fillFor: (seg) => this.pigments[seg.id]?.pigment_hex || '#6B4A2A',
                iconFor: () => '#FBF2DC',
                iconSize: 25,
            });
            this.drawRing(this.$refs.ringWeapons, 'weapons', this.wheels.weapon?.segments || [], 126, 184, {
                fillFor: (_seg, i) => (i % 2 === 0 ? '#F3E8D0' : '#E0D0AE'),
                iconFor: () => '#2A1D11',
                iconSize: 34,
            });
            this.drawRing(this.$refs.ringRaces, 'races', this.wheels.race?.segments || [], 66, 126, {
                fillFor: (_seg, i) => (i % 2 === 0 ? '#E9DBBE' : '#D9C8A2'),
                iconFor: () => '#2A1D11',
                iconSize: 30,
            });
        },

        applyInstantRotations() {
            const pairs = [
                [this.$refs.ringRaces, this.rotations.race],
                [this.$refs.ringWeapons, this.rotations.weapon],
                [this.$refs.ringPowers, this.rotations.power],
            ];
            pairs.forEach(([el, deg]) => {
                if (!el) return;
                el.style.transitionDuration = '0ms';
                el.style.transform = `rotate(${deg}deg)`;
            });
        },

        applySpin(el, deg, durationMs, easing) {
            if (!el) return;
            el.style.transitionDuration = this.prefersReducedMotion ? '1ms' : `${durationMs}ms`;
            el.style.transitionTimingFunction = easing;
            el.style.transform = `rotate(${deg}deg)`;
        },

        runSequence(detail) {
            this.clearTimers();
            if (detail.wheels) {
                this.wheels = detail.wheels;
                this.drawAllRings();
            }

            const targets = detail.rotations || {};
            const reveal = detail.reveal || {};
            const isMythic = reveal.power_tier === 'mythic';

            const durRace = this.prefersReducedMotion ? 40 : 1050;
            const pause1 = this.prefersReducedMotion ? 20 : 280;
            const durWeapon = this.prefersReducedMotion ? 40 : 1100;
            const pause2 = this.prefersReducedMotion ? 20 : 320;
            const durPower = this.prefersReducedMotion ? 40 : (isMythic ? 1750 : 1400);
            const settlePad = this.prefersReducedMotion ? 15 : 60;
            const revealPad = this.prefersReducedMotion ? 30 : 240;

            this.animating = true;
            this.stepState = { race: null, weapon: null, power: null };

            const targetRace = Number(targets.race ?? this.rotations.race);
            const targetWeapon = Number(targets.weapon ?? this.rotations.weapon);
            const targetPower = Number(targets.power ?? this.rotations.power);

            this.activeRing = 'race';
            this.rotations.race = targetRace;
            this.applySpin(this.$refs.ringRaces, targetRace, durRace, 'cubic-bezier(0.16, 0.84, 0.22, 1)');

            const tRaceDone = durRace + settlePad;
            const tWeaponStart = tRaceDone + pause1;
            const tWeaponDone = tWeaponStart + durWeapon + settlePad;
            const tPowerStart = tWeaponDone + pause2;
            const tPowerDone = tPowerStart + durPower + settlePad;
            const tFinish = tPowerDone + revealPad;

            this.schedule(() => {
                this.activeRing = null;
                this.stepState.race = reveal.race || null;
            }, tRaceDone);

            this.schedule(() => {
                this.activeRing = 'weapon';
                this.rotations.weapon = targetWeapon;
                this.applySpin(this.$refs.ringWeapons, targetWeapon, durWeapon, 'cubic-bezier(0.16, 0.84, 0.22, 1)');
            }, tWeaponStart);

            this.schedule(() => {
                this.activeRing = null;
                this.stepState.weapon = reveal.weapon || null;
            }, tWeaponDone);

            this.schedule(() => {
                this.activeRing = 'power';
                this.rotations.power = targetPower;
                const powerEase = isMythic
                    ? 'cubic-bezier(0.08, 0.90, 0.14, 1)'
                    : 'cubic-bezier(0.14, 0.84, 0.20, 1)';
                this.applySpin(this.$refs.ringPowers, targetPower, durPower, powerEase);
            }, tPowerStart);

            this.schedule(() => {
                this.activeRing = null;
                this.stepState.power = reveal.power || null;
            }, tPowerDone);

            this.schedule(() => {
                this.animating = false;
                if (this.$wire && typeof this.$wire.finishSpin === 'function') {
                    this.$wire.finishSpin();
                }
            }, tFinish);
        },

        stepIconHtml(category, id, label) {
            if (!id) return '?';
            const icon = this.resolveIcon(category, id);
            if (icon.type === 'image' && icon.url) {
                return `<img class="seq-svg" src="${escapeHtml(icon.url)}" alt="${escapeHtml(label || id)}" loading="lazy">`;
            }
            return `<svg class="seq-svg" viewBox="${escapeHtml(icon.viewBox || '0 0 48 48')}" aria-hidden="true">${icon.markup || ''}</svg>`;
        },
    };
}
