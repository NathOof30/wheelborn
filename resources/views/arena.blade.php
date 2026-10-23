<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-extrabold text-xl tracking-wide flex items-center gap-3">
                <span class="text-2xl">⚔️</span>
                <span class="bg-gradient-to-r from-rose-300 to-rose-400 bg-clip-text text-transparent">Arène de Duel</span>
            </h2>
            <span class="text-[10px] text-slate-600 font-medium tracking-wider uppercase hidden sm:inline-block">
                Combats
            </span>
        </div>
    </x-slot>

    <div class="section-breathe">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <livewire:battle-arena />

            <div class="divider-glow max-w-xs mx-auto"></div>

            <livewire:battle-history />
        </div>
    </div>
</x-app-layout>
