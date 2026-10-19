<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class PlayerHud extends Component
{
    public int $coins = 250;
    public int $elo = 1000;
    public int $spinCharges = 3;
    public bool $isAdmin = false;
    public string $leagueIcon = '🥉';

    public function mount(): void
    {
        $this->refreshStats();
    }

    #[On('stats-updated')]
    public function refreshStats(?int $coins = null, ?int $spinCharges = null, ?int $elo = null): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $user->refresh();

        $this->isAdmin = $user->isAdmin();
        $this->coins = $coins ?? $user->coins;
        $this->elo = $elo ?? $user->elo;
        $this->spinCharges = $spinCharges ?? $user->spin_charges;
        $this->leagueIcon = $user->getLeague()->icon();
    }

    public function render()
    {
        return view('livewire.player-hud');
    }
}
