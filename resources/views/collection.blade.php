<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-xl text-indigo-400 tracking-wide flex items-center gap-2">
                <span>🛡️</span>
                <span>Caserne & Collection de Héros</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium bg-slate-800/60 px-3 py-1 rounded-full border border-slate-700/50 hidden sm:inline-block">
                Inventaire Réactif Livewire 3
            </span>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:collection-grid />
        </div>
    </div>
</x-app-layout>
