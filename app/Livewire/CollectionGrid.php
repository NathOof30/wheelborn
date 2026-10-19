<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Character;
use App\Services\RecycleService;
use App\Data\RaceData;
use App\Data\WeaponData;
use App\Enums\Rarity;

class CollectionGrid extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 12;

    // --- Filtres & Tri (Ticket T1.2) ---
    public string $search = '';
    public string $rarity = '';
    public string $race = '';
    public string $weapon = '';
    public bool $onlySynergies = false;
    public string $sortBy = 'power_desc';

    public ?string $notification = null;
    public ?string $notificationType = null;

    // Réinitialisation de la pagination lors du changement de filtre
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRarity(): void { $this->resetPage(); }
    public function updatedRace(): void { $this->resetPage(); }
    public function updatedWeapon(): void { $this->resetPage(); }
    public function updatedOnlySynergies(): void { $this->resetPage(); }
    public function updatedSortBy(): void { $this->resetPage(); }

    /**
     * Réinitialise tous les filtres de recherche et de tri.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->rarity = '';
        $this->race = '';
        $this->weapon = '';
        $this->onlySynergies = false;
        $this->sortBy = 'power_desc';
        $this->resetPage();
    }

    /**
     * Vérifie si au moins un filtre de recherche est actif.
     */
    public function hasActiveFilters(): bool
    {
        return !empty(trim($this->search))
            || !empty($this->rarity)
            || !empty($this->race)
            || !empty($this->weapon)
            || $this->onlySynergies
            || $this->sortBy !== 'power_desc';
    }

    /**
     * Définit rapidement la rareté sélectionnée via les pilules.
     */
    public function setRarity(string $rarity): void
    {
        $this->rarity = ($this->rarity === $rarity) ? '' : $rarity;
        $this->resetPage();
    }

    /**
     * Bascule le filtre de synergies actives.
     */
    public function toggleOnlySynergies(): void
    {
        $this->onlySynergies = !$this->onlySynergies;
        $this->resetPage();
    }

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
                'characters'    => collect(),
                'totalCount'    => 0,
                'filteredCount' => 0,
                'rarityCounts'  => [],
                'maxPowerScore' => 0,
                'races'         => RaceData::all(),
                'weapons'       => WeaponData::all(),
                'rarities'      => Rarity::cases(),
            ]);
        }

        $baseQuery = Character::where('user_id', $user->id)
            ->where('pending_reveal', false)
            ->whereNull('recycled_at');

        // Statistiques globales de collection (non impactées par les filtres pour les badges du HUD)
        $totalCount = (clone $baseQuery)->count();
        $maxPowerScore = (clone $baseQuery)->max('power_score') ?? 0;
        
        $rarityCounts = [
            'legendary' => (clone $baseQuery)->where('rarity', 'legendary')->count(),
            'epic'      => (clone $baseQuery)->where('rarity', 'epic')->count(),
            'rare'      => (clone $baseQuery)->where('rarity', 'rare')->count(),
            'common'    => (clone $baseQuery)->where('rarity', 'common')->count(),
        ];

        // Construction de la requête filtrée
        $filteredQuery = (clone $baseQuery);

        if (!empty(trim($this->search))) {
            $filteredQuery->where('name', 'like', '%' . trim($this->search) . '%');
        }

        if (!empty($this->rarity)) {
            $filteredQuery->where('rarity', $this->rarity);
        }

        if (!empty($this->race)) {
            $filteredQuery->where('race_id', $this->race);
        }

        if (!empty($this->weapon)) {
            $filteredQuery->where('weapon_id', $this->weapon);
        }

        if ($this->onlySynergies) {
            $filteredQuery->where(function ($q) {
                $q->whereNotNull('synergies_data')
                  ->where('synergies_data', '!=', '[]')
                  ->where('synergies_data', '!=', '');
            });
        }

        $filteredCount = (clone $filteredQuery)->count();

        // Application du tri dynamique
        switch ($this->sortBy) {
            case 'power_asc':
                $filteredQuery->orderBy('power_score', 'asc')->orderBy('created_at', 'desc');
                break;
            case 'rarity':
                $filteredQuery->orderByRaw("CASE rarity 
                    WHEN 'legendary' THEN 4 
                    WHEN 'epic' THEN 3 
                    WHEN 'rare' THEN 2 
                    ELSE 1 END DESC")
                    ->orderByDesc('power_score');
                break;
            case 'date_desc':
                $filteredQuery->orderByDesc('created_at');
                break;
            case 'date_asc':
                $filteredQuery->orderBy('created_at', 'asc');
                break;
            case 'wins_desc':
                $filteredQuery->orderByDesc('wins')->orderByDesc('power_score');
                break;
            case 'power_desc':
            default:
                $filteredQuery->orderByDesc('power_score')->orderByDesc('created_at');
                break;
        }

        $characters = $filteredQuery->paginate($this->perPage);

        return view('livewire.collection-grid', [
            'characters'    => $characters,
            'totalCount'    => $totalCount,
            'filteredCount' => $filteredCount,
            'rarityCounts'  => $rarityCounts,
            'maxPowerScore' => $maxPowerScore,
            'races'         => RaceData::all(),
            'weapons'       => WeaponData::all(),
            'rarities'      => Rarity::cases(),
        ]);
    }
}
