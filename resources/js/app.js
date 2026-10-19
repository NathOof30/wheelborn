// Livewire 3 gère et démarre automatiquement Alpine.js.
// Ne pas appeler Alpine.start() ici pour éviter les instances en double.
import battleAnimation from './battle-animation';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('battleAnimation', battleAnimation);
});
