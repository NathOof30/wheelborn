<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-extrabold text-xl tracking-wide flex items-center gap-3">
                <span class="text-2xl">🎡</span>
                <span class="heading-majestic">Tirage de Combattants</span>
            </h2>
            <span class="text-[10px] text-slate-600 font-medium tracking-wider uppercase hidden sm:inline-block">
                Génération Procédurale
            </span>
        </div>
    </x-slot>

    <div class="section-breathe">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:character-wheel />
        </div>
    </div>
</x-app-layout>
