<?php

namespace App\Livewire;

use App\Data\GameConfig;
use App\Models\Character;
use App\Services\BattleEngineService;
use App\Services\EnergyManagerService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BattleArena extends Component
{
    public ?string $fighterId = null;

    // Verrouillés : le client ne doit pas pouvoir affaiblir le bot ou falsifier le résultat
    #[Locked]
    public ?array $bot = null;

    #[Locked]
    public ?array $result = null;

    // Tickets d'arène (affichage uniquement, la vérification se fait côté serveur au lancement du duel)
    public int $arenaTickets = 0;
    public int $maxTickets = 0;
    public int $nextTicketSeconds = 0;
    public bool $isAdmin = false;
    public ?string $error = null;

    public function mount(): void
    {
        $this->refreshTickets();
        $this->fighterId = $this->fighters->first()?->id;

        if ($this->fighterId) {
            $this->newOpponent();
        }
    }

    #[Computed]
    public function fighters()
    {
        return app(BattleEngineService::class)->availableFighters(auth()->user());
    }

    #[Computed]
    public function fighter(): ?Character
    {
        return $this->fighters->firstWhere('id', $this->fighterId);
    }

    #[Computed]
    public function cards(): array
    {
        $engine = app(BattleEngineService::class);

        return array_filter([
            'challenger' => $this->fighter ? ['label' => 'Ton combattant'] + $engine->describeFighter($this->fighter) : null,
            'opponent'   => $this->bot ? ['label' => '🤖 Bot d\'arène'] + $engine->describeFighter($this->bot) : null,
        ]);
    }

    /** Temps avant le prochain ticket, au format "mm:ss". */
    #[Computed]
    public function nextTicketIn(): string
    {
        return gmdate('i:s', $this->nextTicketSeconds);
    }

    /** Recharge les tickets (rattrapage hors-ligne compris) et met à jour le compteur affiché. */
    public function refreshTickets(): void
    {
        $user = auth()->user();
        $energy = app(EnergyManagerService::class);

        $energy->regenArenaTickets($user);

        $this->isAdmin = $user->isAdmin();
        $this->arenaTickets = $user->arena_tickets;
        $this->maxTickets = GameConfig::arenaEnergy()['max'];
        $this->nextTicketSeconds = $energy->getNextArenaRegenSeconds($user);
    }

    public function selectFighter(string $characterId): void
    {
        if (!$this->fighters->contains('id', $characterId)) {
            return;
        }

        $this->fighterId = $characterId;
        $this->resetBattle();
    }

    public function newOpponent(): void
    {
        $this->bot = app(BattleEngineService::class)->generateBotOpponent(auth()->user());
        $this->resetBattle();
    }

    public function fight(): void
    {
        if (!$this->fighter || !$this->bot) {
            return;
        }

        $user = auth()->user();

        // Ticket et combat dans la même transaction : si le combat échoue, le ticket est rendu
        $outcome = DB::transaction(fn () => app(EnergyManagerService::class)->consumeArenaTicket($user)
            ? app(BattleEngineService::class)->fightBot($user, $this->fighter, $this->bot)
            : null);

        $this->refreshTickets();

        if ($outcome === null) {
            $this->error = "Plus de ticket d'arène ! Un nouveau ticket arrive dans {$this->nextTicketIn}.";
            return;
        }

        // Le journal part directement à l'animation Alpine, inutile de le garder dans l'état du composant
        $this->result = collect($outcome)->except('log')->all();
        unset($this->fighters);

        $this->dispatch('battle-started', log: $outcome['log'], maxHp: $outcome['max_hp']);
        $this->dispatch('stats-updated', coins: $user->coins, spinCharges: $user->spin_charges, elo: $user->elo);
    }

    private function resetBattle(): void
    {
        $this->result = null;
        $this->error = null;
        $this->dispatch('battle-reset');
    }

    public function render()
    {
        return view('livewire.battle-arena');
    }
}
