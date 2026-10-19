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
    public function refreshStats($coins = null, $spinCharges = null, $elo = null): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $user->refresh();

        $this->isAdmin = $user->isAdmin();
        $this->coins = $coins !== null ? (int) $coins : (int) $user->coins;
        $this->elo = $elo !== null ? (int) $elo : (int) $user->elo;
        $this->spinCharges = $spinCharges !== null ? (int) $spinCharges : (int) $user->spin_charges;
        $this->leagueIcon = $user->getLeague()->icon();
    }

    public function render()
    {
        return view('livewire.player-hud');
    }
}
