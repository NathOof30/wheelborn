<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 5.5 C7 4.5 10 5 12 6.5 C14 5 17 4.5 20 5.5 V18.5 C17 17.5 14 18 12 19.5 C10 18 7 17.5 4 18.5 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                <line x1="12" y1="6.5" x2="12" y2="19.5" stroke="currentColor" stroke-width="1.7"/>
            </svg>
            <span>Collection de Héros</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Folios &amp; Caserne</span>
    </x-slot>

    <livewire:collection-grid />
</x-app-layout>
