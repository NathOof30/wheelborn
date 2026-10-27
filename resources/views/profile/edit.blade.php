<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-extrabold text-xl tracking-wide flex items-center gap-3">
                <span class="text-2xl">👤</span>
                <span class="heading-majestic">{{ __('Fiche de Destinée & Profil') }}</span>
            </h2>
            <div class="text-xs text-slate-500 font-mono tracking-widest uppercase">Identité • Vitrine • Palmarès</div>
        </div>
    </x-slot>

    <div class="section-breathe">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <livewire:user-profile />
        </div>
    </div>
</x-app-layout>
