<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-wax shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Arène de Duel</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Lice des Sceaux</span>
    </x-slot>

    <div class="space-y-8">
        <livewire:battle-arena />

        <hr class="codex-divider max-w-md mx-auto">

        <livewire:battle-history />
    </div>
</x-app-layout>
