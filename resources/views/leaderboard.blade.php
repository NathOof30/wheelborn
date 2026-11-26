<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 17 L6 8 L10 12 L12 6 L14 12 L18 8 L20 17 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                <line x1="5" y1="20" x2="19" y2="20" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
            <span>Classement de l'Arène</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Élo FIDE · 5 Ligues</span>
    </x-slot>

    <livewire:leaderboard />
</x-app-layout>
