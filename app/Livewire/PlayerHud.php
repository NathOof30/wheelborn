<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\GameConfig;
use App\Services\EnergyManagerService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PlayerHud extends Component
{
    #[Locked]
    public int $coins = 250;

    #[Locked]
    public int $elo = 1000;

    #[Locked]
    public int $spinCharges = 3;

    #[Locked]
    public int $arenaTickets = 5;

    #[Locked]
    public int $maxTickets = 5;

    #[Locked]
    public bool $isAdmin = false;

    #[Locked]
    public string $leagueIcon = '🥉';

    #[Locked]
    public string $leagueLabel = 'Argent';

    public function mount(): void
    {
        $this->refreshStats();
    }

    #[On('stats-updated')]
    public function refreshStats($coins = null, $spinCharges = null, $elo = null, $arenaTickets = null): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        if (! $user->isAdmin()) {
            $energy = app(EnergyManagerService::class);
            $energy->regenSpins($user);
            $energy->regenArenaTickets($user);
        }

        $user->refresh();
        $league = $user->getLeague();

        $this->isAdmin = $user->isAdmin();
        $this->coins = $coins !== null ? (int) $coins : (int) $user->coins;
        $this->elo = $elo !== null ? (int) $elo : (int) $user->elo;
        $this->spinCharges = $spinCharges !== null ? (int) $spinCharges : (int) $user->spin_charges;
        $this->arenaTickets = $arenaTickets !== null ? (int) $arenaTickets : (int) $user->arena_tickets;
        $this->maxTickets = (int) GameConfig::arenaEnergy()['max'];
        $this->leagueIcon = $league->icon();
        $this->leagueLabel = $league->label();
    }

    public function render()
    {
        return view('livewire.player-hud');
    }
}
