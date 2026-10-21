<div class="space-y-6 pb-24">
    {{-- Onglets de ligue --}}
    <div class="flex flex-wrap justify-center gap-2">
        @foreach ($this->tabs as $tab)
            <button
                wire:key="tab-{{ $tab['value'] }}"
                wire:click="setLeague('{{ $tab['value'] }}')"
                class="px-4 py-2 rounded-xl border text-sm font-bold transition {{ $league === $tab['value'] ? 'bg-slate-800 shadow-lg' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200' }}"
                @if ($league === $tab['value']) style="color: {{ $tab['color'] }}; border-color: {{ $tab['color'] }}" @endif
            >
                {{ $tab['icon'] }} {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    @if (empty($this->players))
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-10 text-center">
            <p class="text-4xl">🏟️</p>
            <p class="text-slate-400 text-sm mt-2">Aucun joueur classé dans cette ligue pour l'instant.</p>
        </div>
    @else
        {{-- Podium du Top 3 --}}
        <div class="flex items-end justify-center gap-3 sm:gap-6 pt-6">
            @foreach ($this->podium as $player)
                <div wire:key="podium-{{ $player['id'] }}" class="flex flex-col items-center w-28 sm:w-36">
                    <div class="text-4xl mb-1">{{ $player['medal'] }}</div>
                    <div class="font-extrabold text-white text-sm truncate max-w-full">{{ $player['name'] }}</div>
                    <div class="text-xs mb-2" style="color: {{ $player['league']['color'] }}">
                        {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                    </div>
                    <div
                        class="w-full rounded-t-2xl border-2 bg-slate-950/80 flex flex-col items-center justify-start pt-3"
                        style="height: {{ $player['height'] }}px; border-color: {{ $player['color'] }}; box-shadow: 0 0 25px {{ $player['color'] }}66, inset 0 0 20px {{ $player['color'] }}22"
                    >
                        <span class="text-3xl font-black" style="color: {{ $player['color'] }}; text-shadow: 0 0 12px {{ $player['color'] }}">
                            #{{ $player['rank'] }}
                        </span>
                        <span class="font-mono font-bold text-white text-sm">{{ $player['elo'] }} Élo</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Suite du classement --}}
        @if (!empty($this->others))
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden">
                @foreach ($this->others as $player)
                    <div
                        wire:key="row-{{ $player['id'] }}"
                        class="flex items-center gap-4 px-5 py-3 border-b border-slate-800/70 last:border-0 {{ $player['id'] === auth()->id() ? 'bg-indigo-500/10' : '' }}"
                    >
                        <span class="w-10 font-mono font-black text-slate-400">#{{ $player['rank'] }}</span>
                        <span class="flex-1 font-bold text-white truncate">{{ $player['name'] }}</span>
                        <span class="hidden sm:inline text-xs text-slate-500">{{ $player['battles'] }} combat(s)</span>
                        <span class="text-xs font-bold w-24 text-right" style="color: {{ $player['league']['color'] }}">
                            {{ $player['league']['icon'] }} {{ $player['league']['label'] }}
                        </span>
                        <span class="w-20 text-right font-mono font-bold text-amber-300">{{ $player['elo'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <p class="text-center text-[11px] text-slate-600">Classement mis à jour toutes les minutes.</p>

    {{-- Ligne épinglée du joueur connecté --}}
    @if ($this->me)
        <div class="fixed bottom-4 inset-x-0 px-4 z-40">
            <div
                class="max-w-3xl mx-auto flex items-center gap-4 px-5 py-3 rounded-2xl border-2 bg-slate-950/95 backdrop-blur-md shadow-2xl"
                style="border-color: {{ $this->me['league']['color'] }}"
            >
                <span class="text-xs uppercase tracking-widest text-slate-500">Toi</span>
                <span class="font-mono font-black text-white">#{{ $this->me['rank'] }}</span>
                <span class="flex-1 font-bold text-white truncate">{{ $this->me['name'] }}</span>
                <span class="text-xs font-bold" style="color: {{ $this->me['league']['color'] }}">
                    {{ $this->me['league']['icon'] }} {{ $this->me['league']['label'] }}
                </span>
                <span class="font-mono font-bold text-amber-300">{{ $this->me['elo'] }} Élo</span>
            </div>
        </div>
    @endif
</div>
