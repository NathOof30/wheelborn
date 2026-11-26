import battleAnimation from './battle-animation';
import { codexVolvelle, initCodexUi } from './codex-ui';

initCodexUi();

document.addEventListener('alpine:init', () => {
    window.Alpine.data('battleAnimation', battleAnimation);
    window.Alpine.data('codexVolvelle', codexVolvelle);
});
