const STEP_DELAY_MS = 450;
const REDUCED_MOTION_DELAY_MS = 90;

const ENTRY_STYLES = {
    crit: 'codex-log-entry codex-log-crit',
    dodge: 'codex-log-entry codex-log-dodge',
    counter: 'codex-log-entry codex-log-counter',
    initiative: 'codex-log-entry codex-log-initiative',
    end: 'codex-log-entry codex-log-end',
    burn: 'codex-log-entry codex-log-burn',
    burn_applied: 'codex-log-entry codex-log-burn',
    poison: 'codex-log-entry codex-log-poison',
    poison_applied: 'codex-log-entry codex-log-poison',
    freeze: 'codex-log-entry codex-log-freeze',
    stun: 'codex-log-entry codex-log-stun',
    skip: 'codex-log-entry codex-log-skip',
    regen: 'codex-log-entry codex-log-regen',
    lifesteal: 'codex-log-entry codex-log-lifesteal',
    shield: 'codex-log-entry codex-log-shield',
    absorb: 'codex-log-entry codex-log-absorb',
};

export default () => ({
    log: [],
    step: 0,
    done: false,
    timer: null,
    pendingStats: null,

    init() {
        this.$watch('step', () => this.$nextTick(() => {
            if (this.$refs.log) {
                this.$refs.log.scrollTop = this.$refs.log.scrollHeight;
            }
        }));
    },

    destroy() {
        clearInterval(this.timer);
    },

    play({ log, stats, replay = false }) {
        this.reset();
        this.log = Array.isArray(log) ? log : [];
        this.pendingStats = stats;

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (replay) {
            this.$root.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
        }

        const delay = reducedMotion ? REDUCED_MOTION_DELAY_MS : STEP_DELAY_MS;
        this.timer = setInterval(() => (this.step < this.log.length ? this.step++ : this.finish()), delay);
    },

    finish() {
        clearInterval(this.timer);
        this.step = this.log.length;
        this.done = true;
        this.revealStats();
    },

    reset() {
        clearInterval(this.timer);
        this.revealStats();
        this.log = [];
        this.step = 0;
        this.done = false;
    },

    revealStats() {
        if (this.pendingStats) {
            this.$dispatch('stats-updated', this.pendingStats);
            this.pendingStats = null;
        }
    },

    get entries() {
        return this.log.slice(0, this.step);
    },

    hp(side, maxHp) {
        return this.step ? this.log[this.step - 1].hp[side] : maxHp;
    },

    pct(side, maxHp) {
        if (!maxHp) {
            return 0;
        }

        return Math.max(0, Math.min(100, (this.hp(side, maxHp) / maxHp) * 100));
    },

    style(entry) {
        return ENTRY_STYLES[entry.type] ?? 'codex-log-entry codex-log-attack';
    },
});
