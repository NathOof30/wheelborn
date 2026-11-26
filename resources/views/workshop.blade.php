<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 10 H17 L20 13 H15 L17 19 H7 L9 13 H4 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                <path d="M9 6 H15 V10 H9 Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
            </svg>
            <span>Atelier du Destin</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Enluminures Permanentes</span>
    </x-slot>

    <livewire:workshop />
</x-app-layout>
