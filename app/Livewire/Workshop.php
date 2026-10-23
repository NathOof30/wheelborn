<?php

namespace App\Livewire;

use App\Data\GameConfig;
use App\Services\WorkshopService;
use DomainException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * T4.4 : Atelier, achat des améliorations permanentes avec l'or du recyclage et des victoires.
 * Toute la logique (coûts, solde, niveaux) est dans WorkshopService : le composant ne fait qu'afficher et relayer l'achat.
 */
class Workshop extends Component
{
    public int $coins = 0;
    public bool $isAdmin = false;

    // Amélioration qui vient d'être achetée : sa carte joue l'animation de déblocage
    #[Locked]
    public ?string $justUnlocked = null;

    public ?string $notification = null;
    public ?string $notificationType = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->coins = (int) $user->coins;
        $this->isAdmin = $user->isAdmin();
    }

    /** Cartes d'amélioration : niveau, coût du suivant, fonds suffisants ou non, effet actuel et effet au niveau suivant. */
    #[Computed]
    public function upgrades(): array
    {
        $overview = app(WorkshopService::class)->overview(auth()->user());

        foreach ($overview as $key => $upgrade) {
            $overview[$key]['missing'] = $upgrade['next_cost'] !== null ? max(0, $upgrade['next_cost'] - $this->coins) : 0;
            $overview[$key]['current_effect'] = $upgrade['level'] > 0 ? $this->effectAt($key, $upgrade['level']) : 'Aucun effet pour l\'instant';
            $overview[$key]['next_effect'] = $upgrade['is_maxed'] ? null : $this->effectAt($key, $upgrade['level'] + 1);
        }

        return $overview;
    }

    public function purchase(string $upgrade): void
    {
        $user = auth()->user();

        try {
            $result = app(WorkshopService::class)->purchase($user, $upgrade);
        } catch (DomainException $e) {
            $this->justUnlocked = null;
            $this->notify($e->getMessage(), 'error');
            return;
        }

        $definition = GameConfig::workshop()[$upgrade];
        $this->coins = $result['coins'];
        $this->justUnlocked = $upgrade;
        unset($this->upgrades);

        $this->notify("{$definition['icon']} {$definition['name']} débloqué au niveau {$result['level']} ! (-{$result['cost']} 🪙)", 'success');

        // Met à jour la barre HUD sans rechargement de page
        $this->dispatch('stats-updated', coins: $user->coins, elo: $user->elo, spinCharges: $user->spin_charges);
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

    /** Effet cumulé d'une amélioration à un niveau donné, en texte (valeurs lues dans GameConfig). */
    private function effectAt(string $upgrade, int $level): string
    {
        $definition = GameConfig::workshop()[$upgrade];

        return match ($upgrade) {
            'rare_magnet' => '+' . ($level * $definition['bonus_per_level']) . ' % de chances de pouvoir mythique',
            'hero_forge'  => '+' . ($level * $definition['bonus_hp']) . ' PV et +' . ($level * $definition['bonus_str']) . ' Force à chaque tirage',
            'arena_bonus' => 'Or des victoires d\'arène ×' . str_replace('.', ',', (string) $definition['multipliers'][$level - 1]),
        };
    }

    public function render()
    {
        return view('livewire.workshop');
    }
}
