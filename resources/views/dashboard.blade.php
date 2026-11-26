<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.9"/>
                <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/>
                <path d="M12 3 V9 M12 15 V21 M3 12 H9 M15 12 H21" stroke="currentColor" stroke-width="1.7"/>
            </svg>
            <span>Tirage de Combattants</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Volvelle Arcanique</span>
    </x-slot>

    <livewire:character-wheel />
</x-app-layout>
