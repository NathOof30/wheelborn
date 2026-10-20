<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-xl text-rose-400 tracking-wide flex items-center gap-2">
                <span>⚔️</span>
                <span>Arène de Duel</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium bg-slate-800/60 px-3 py-1 rounded-full border border-slate-700/50 hidden sm:inline-block">
                Combats contre les Bots
            </span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:battle-arena />
        </div>
    </div>
</x-app-layout>
