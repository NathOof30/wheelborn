<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Character;
use App\Services\RecycleService;

class CollectionGrid extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 12;
    public ?string $notification = null;
    public ?string $notificationType = null;

    /**
     * Recycle un combattant directement depuis la grille d'inventaire.
     */
    public function recycleCharacter(string $characterId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        try {
            $coins = app(RecycleService::class)->recycle($user, $characterId);
            $user->refresh();
            $this->notify("+{$coins} 🪙 ajoutées à votre solde suite au recyclage !", 'success');
            $this->dispatch('stats-updated',
                coins: $user->coins,
                spinCharges: $user->spin_charges,
                elo: $user->elo
            );
        } catch (\Exception $e) {
            $this->notify("Impossible de recycler ce personnage : {$e->getMessage()}", 'error');
        }
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
        $user = auth()->user();

        if (!$user) {
            return view('livewire.collection-grid', [
                'characters' => collect(),
                'totalCount' => 0,
                'rarityCounts' => [],
                'maxPowerScore' => 0,
            ]);
        }

        $baseQuery = Character::where('user_id', $user->id)
            ->where('pending_reveal', false)
            ->whereNull('recycled_at');

        // Statistiques globales de collection pour les badges du HUD
        $totalCount = (clone $baseQuery)->count();
        $maxPowerScore = (clone $baseQuery)->max('power_score') ?? 0;
        
        $rarityCounts = [
            'legendary' => (clone $baseQuery)->where('rarity', 'legendary')->count(),
            'epic'      => (clone $baseQuery)->where('rarity', 'epic')->count(),
            'rare'      => (clone $baseQuery)->where('rarity', 'rare')->count(),
            'common'    => (clone $baseQuery)->where('rarity', 'common')->count(),
        ];

        // Pagination des héros (tri par puissance décroissante par défaut)
        $characters = $baseQuery
            ->orderByDesc('power_score')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.collection-grid', [
            'characters'    => $characters,
            'totalCount'    => $totalCount,
            'rarityCounts'  => $rarityCounts,
            'maxPowerScore' => $maxPowerScore,
        ]);
    }
}
