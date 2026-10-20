<?php

namespace App\Livewire;

use App\Models\Character;
use App\Services\BattleEngineService;
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

    public function mount(): void
    {
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
        $outcome = app(BattleEngineService::class)->fightBot($user, $this->fighter, $this->bot);

        // Le journal part directement à l'animation Alpine, inutile de le garder dans l'état du composant
        $this->result = collect($outcome)->except('log')->all();
        unset($this->fighters);

        $this->dispatch('battle-started', log: $outcome['log'], maxHp: $outcome['max_hp']);
        $this->dispatch('stats-updated', coins: $user->coins, spinCharges: $user->spin_charges, elo: $user->elo);
    }

    private function resetBattle(): void
    {
        $this->result = null;
        $this->dispatch('battle-reset');
    }

    public function render()
    {
        return view('livewire.battle-arena');
    }
}
