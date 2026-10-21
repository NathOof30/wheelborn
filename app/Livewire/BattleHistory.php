<?php

namespace App\Livewire;

use App\Services\BattleHistoryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BattleHistory extends Component
{
    #[Computed]
    public function battles(): array
    {
        return app(BattleHistoryService::class)->recent(auth()->user());
    }

    /**
     * Rafraîchit la liste quand le résultat d'un duel est dévoilé (fin de l'animation),
     * pour ne pas afficher la victoire ou la défaite avant la fin du combat.
     */
    #[On('stats-updated')]
    public function refresh(): void
    {
        unset($this->battles);
    }

    public function render()
    {
        return view('livewire.battle-history');
    }
}
