<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Character;
use App\Services\CharacterGeneratorService;
use App\Services\EnergyManagerService;
use App\Services\RecycleService;

class CharacterWheel extends Component
{
    public int $spinCharges = 3;
    public int $maxCharges = 3;
    public int $pityCounter = 0;
    public int $pityMax = 10;
    public int $coins = 250;
    public int $elo = 1000;
    public int $nextRegenSeconds = 0;
    public bool $isAdmin = false;
    public bool $isSpinning = false;
    public ?array $revealedCharacter = null;
    public ?string $notification = null;
    public ?string $notificationType = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $this->isAdmin = $user->isAdmin();

        if ($this->isAdmin) {
            // Pour l'admin : argent et charges illimités
            if ($user->coins < 999999 || $user->spin_charges < 999) {
                $user->update([
                    'coins' => max(999999, $user->coins),
                    'spin_charges' => 999,
                    'arena_tickets' => 999,
                ]);
            }
        } else {
            app(EnergyManagerService::class)->regenSpins($user);
        }

        $user->refresh();

        $this->spinCharges = $user->spin_charges;
        $this->maxCharges = $this->isAdmin ? 999 : 3;
        $this->pityCounter = $user->pity_counter;
        $this->coins = $user->coins;
        $this->elo = $user->elo;
        $this->nextRegenSeconds = app(EnergyManagerService::class)->getNextSpinRegenSeconds($user);
    }

    public function spin(): void
    {
        $user = auth()->user();
        $energy = app(EnergyManagerService::class);

        if (!$this->isAdmin && !$energy->consumeSpin($user)) {
            $this->notify('Aucune charge de tirage disponible ! Attends la recharge.', 'error');
            return;
        }

        $isLucky = $user->pity_counter >= $this->pityMax;
        $character = app(CharacterGeneratorService::class)->generate($user, $isLucky);

        if ($isLucky) {
            $user->update(['pity_counter' => 0]);
            $this->notify('🌟 SUPER SPIN DÉCLENCHÉ ! Tirage exceptionnel garanti !', 'success');
        } else {
            $user->increment('pity_counter');
        }

        $this->revealedCharacter = $character->toArray();
        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    public function keepCharacter(): void
    {
        if ($this->revealedCharacter) {
            Character::where('id', $this->revealedCharacter['id'])
                ->update(['pending_reveal' => false]);
            $this->notify("{$this->revealedCharacter['name']} a rejoint votre collection !", 'success');
        }

        $this->revealedCharacter = null;
        $this->dispatchStatsUpdated();
    }

    public function recycleCharacter(): void
    {
        if (!$this->revealedCharacter) {
            return;
        }

        $coins = app(RecycleService::class)->recycle(
            auth()->user(),
            $this->revealedCharacter['id']
        );

        $this->notify("+{$coins} 🪙 récupérées par recyclage !", 'success');
        $this->revealedCharacter = null;
        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    // --- OUTILS ADMIN POUR LES TESTS ---

    public function adminAddCoins(int $amount = 50000): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->increment('coins', $amount);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify("+{$amount} 🪙 ajoutées pour les tests !", 'success');
    }

    public function adminTriggerSuperSpin(): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->update(['pity_counter' => 10]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('✨ Jauge de chance maximisée à 10/10 ! Le prochain spin est un Super Spin !', 'success');
    }

    public function adminResetPity(): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->update(['pity_counter' => 0]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('Jauge de chance réinitialisée à 0.', 'info');
    }

    public function dispatchStatsUpdated(): void
    {
        $this->dispatch('stats-updated',
            coins: $this->coins,
            spinCharges: $this->spinCharges,
            elo: $this->elo
        );
    }

    public function notify(string $message, string $type = 'info'): void
    {
        $this->notification = $message;
        $this->notificationType = $type;
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
        $this->notificationType = null;
    }

    public function render()
    {
        return view('livewire.character-wheel');
    }
}
