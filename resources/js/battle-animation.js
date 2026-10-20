// Animation d'un combat de l'arène (composant Livewire BattleArena).
// Le combat est déjà résolu côté serveur : on se contente de dérouler son journal à l'écran.

const STEP_DELAY_MS = 450;

const ENTRY_STYLES = {
    crit: 'text-amber-300 font-bold',
    dodge: 'text-cyan-300',
    counter: 'text-cyan-300',
    initiative: 'text-slate-500 text-xs pt-2',
    end: 'text-white font-extrabold pt-2',
    burn: 'text-orange-400',
    burn_applied: 'text-orange-400',
    poison: 'text-lime-400',
    poison_applied: 'text-lime-400',
    freeze: 'text-sky-300',
    stun: 'text-yellow-300',
    skip: 'text-slate-400 italic',
    regen: 'text-emerald-400',
    lifesteal: 'text-emerald-400',
    shield: 'text-indigo-300',
    absorb: 'text-indigo-300',
};

export default () => ({
    log: [],
    step: 0,
    done: false,
    timer: null,

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

    play({ log }) {
        this.reset();
        this.log = log;
        this.timer = setInterval(() => (this.step < this.log.length ? this.step++ : this.finish()), STEP_DELAY_MS);
    },

    finish() {
        clearInterval(this.timer);
        this.step = this.log.length;
        this.done = true;
    },

    reset() {
        clearInterval(this.timer);
        this.log = [];
        this.step = 0;
        this.done = false;
    },

    get entries() {
        return this.log.slice(0, this.step);
    },

    hp(side, maxHp) {
        return this.step ? this.log[this.step - 1].hp[side] : maxHp;
    },

    pct(side, maxHp) {
        return Math.max(0, (this.hp(side, maxHp) / maxHp) * 100);
    },

    style(entry) {
        return ENTRY_STYLES[entry.type] ?? 'text-slate-200';
    },
});
