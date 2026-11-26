<x-app-layout>
    <x-slot name="header">
        <h2 class="codex-chapter-title">
            <svg class="w-5 h-5 text-brass-dark shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.9"/>
                <path d="M4.5 20 C5.5 15.8 8.4 14 12 14 C15.6 14 18.5 15.8 19.5 20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
            <span>{{ __('Fiche de Destinée & Profil') }}</span>
        </h2>
        <span class="codex-chapter-sub hidden sm:inline">Identité · Vitrine · Palmarès</span>
    </x-slot>

    <livewire:user-profile />
</x-app-layout>
