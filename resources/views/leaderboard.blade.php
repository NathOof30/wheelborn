<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-xl text-amber-400 tracking-wide flex items-center gap-2">
                <span>🏆</span>
                <span>Classement de l'Arène</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium bg-slate-800/60 px-3 py-1 rounded-full border border-slate-700/50 hidden sm:inline-block">
                Élo FIDE · 5 Ligues
            </span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:leaderboard />
        </div>
    </div>
</x-app-layout>
