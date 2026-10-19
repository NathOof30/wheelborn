<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Character;
use App\Services\RecycleService;
use App\Data\RaceData;
use App\Data\WeaponData;
use App\Enums\Rarity;
use Illuminate\Support\Facades\DB;
use DomainException;

class CollectionGrid extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 12;

    // --- Filtres & Tri Principaux (Ticket T1.2) ---
    public string $search = '';
    public string $rarity = '';
    public string $race = '';
    public string $weapon = '';
    public bool $onlySynergies = false;
    public bool $onlyLocked = false;
    public string $sortBy = 'power_desc';

    // --- Modale de Recyclage en Masse Avancé (Ticket T1.4) ---
    public bool $showBatchModal = false;
    public array $batchRarities = ['common'];
    public string $batchMaxPower = '';
    public string $batchRace = '';
    public string $batchWeapon = '';
    public bool $batchExcludeSynergies = true;

    // --- Modale d'Inspection Détaillée (Ticket T1.5) ---
    public ?string $inspectedCharacterId = null;

    public ?string $notification = null;
    public ?string $notificationType = null;

    // Réinitialisation de la pagination lors du changement de filtre principal
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRarity(): void { $this->resetPage(); }
    public function updatedRace(): void { $this->resetPage(); }
    public function updatedWeapon(): void { $this->resetPage(); }
    public function updatedOnlySynergies(): void { $this->resetPage(); }
    public function updatedOnlyLocked(): void { $this->resetPage(); }
    public function updatedSortBy(): void { $this->resetPage(); }

    /**
     * Réinitialise tous les filtres de recherche et de tri de la grille principale.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->rarity = '';
        $this->race = '';
        $this->weapon = '';
        $this->onlySynergies = false;
        $this->onlyLocked = false;
        $this->sortBy = 'power_desc';
        $this->resetPage();
    }

    /**
     * Vérifie si au moins un filtre de recherche est actif sur la grille.
     */
    public function hasActiveFilters(): bool
    {
        return !empty(trim($this->search))
            || !empty($this->rarity)
            || !empty($this->race)
            || !empty($this->weapon)
            || $this->onlySynergies
            || $this->onlyLocked
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
     * Bascule le filtre des héros verrouillés.
     */
    public function toggleOnlyLocked(): void
    {
        $this->onlyLocked = !$this->onlyLocked;
        $this->resetPage();
    }

    /**
     * Verrouille / Déverrouille une carte contre le recyclage accidentel (T1.3).
     * Transaction atomique avec lockForUpdate pour éviter tout conflit concurrent.
     */
    public function toggleLock(string $characterId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        DB::transaction(function () use ($user, $characterId) {
            $character = Character::where('id', $characterId)
                ->where('user_id', $user->id)
                ->whereNull('recycled_at')
                ->lockForUpdate()
                ->first();

            if (!$character) {
                $this->notify("Combattant introuvable.", 'error');
                return;
            }

            $character->update([
                'is_locked' => !$character->is_locked,
            ]);

            if ($character->is_locked) {
                $this->notify("🔒 {$character->name} est désormais verrouillé et protégé du recyclage.", 'info');
            } else {
                $this->notify("🔓 {$character->name} a été déverrouillé.", 'info');
            }
        });
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

            if ($this->inspectedCharacterId === $characterId) {
                $this->inspectedCharacterId = null;
            }

            $this->notify("+{$coins} 🪙 ajoutées à votre solde suite au recyclage !", 'success');
            $this->dispatch('stats-updated',
                coins: $user->coins,
                spinCharges: $user->spin_charges,
                elo: $user->elo
            );
        } catch (DomainException $e) {
            $this->notify($e->getMessage(), 'error');
        } catch (\Exception $e) {
            $this->notify("Impossible de recycler ce personnage : {$e->getMessage()}", 'error');
        }
    }

    // --- MODALE D'INSPECTION DÉTAILLÉE DE COMBATTANT (T1.5) ---

    public function inspectCharacter(string $characterId): void
    {
        $this->inspectedCharacterId = $characterId;
    }

    public function closeInspector(): void
    {
        $this->inspectedCharacterId = null;
    }

    // --- MODALE DE RECYCLAGE EN MASSE AVANCÉ (T1.4) ---

    public function openBatchModal(): void
    {
        $this->showBatchModal = true;
    }

    public function closeBatchModal(): void
    {
        $this->showBatchModal = false;
    }

    public function resetBatchFilters(): void
    {
        $this->batchRarities = ['common'];
        $this->batchMaxPower = '';
        $this->batchRace = '';
        $this->batchWeapon = '';
        $this->batchExcludeSynergies = true;
    }

    public function toggleBatchRarity(string $rarity): void
    {
        if (in_array($rarity, $this->batchRarities, true)) {
            $this->batchRarities = array_values(array_diff($this->batchRarities, [$rarity]));
        } else {
            $this->batchRarities[] = $rarity;
        }
    }

    public function getBatchCriteria(): array
    {
        return [
            'rarities'          => $this->batchRarities,
            'max_power'         => (!empty($this->batchMaxPower) && is_numeric($this->batchMaxPower)) ? (int) $this->batchMaxPower : null,
            'race_id'           => !empty($this->batchRace) ? $this->batchRace : null,
            'weapon_id'         => !empty($this->batchWeapon) ? $this->batchWeapon : null,
            'exclude_synergies' => $this->batchExcludeSynergies,
        ];
    }

    public function confirmBatchRecycle(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $criteria = $this->getBatchCriteria();

        try {
            $result = app(RecycleService::class)->recycleBatch($user, $criteria);
            $user->refresh();

            if ($result['count'] === 0) {
                $this->notify("Aucun combattant éligible à recycler avec ces critères (les cartes verrouillées sont protégées).", 'info');
            } else {
                $this->notify("♻️ Recyclage de masse réussi : {$result['count']} combattants recyclés pour +{$result['coins']} 🪙 !", 'success');
                $this->dispatch('stats-updated',
                    coins: $user->coins,
                    spinCharges: $user->spin_charges,
                    elo: $user->elo
                );
            }

            $this->showBatchModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            $this->notify("Erreur lors du recyclage en masse : {$e->getMessage()}", 'error');
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
                'lockedCount'   => 0,
                'rarityCounts'  => [],
                'maxPowerScore' => 0,
                'races'         => RaceData::all(),
                'weapons'       => WeaponData::all(),
                'rarities'      => Rarity::cases(),
                'batchPreview'  => ['count' => 0, 'coins' => 0, 'characters' => collect()],
            ]);
        }

        $baseQuery = Character::where('user_id', $user->id)
            ->where('pending_reveal', false)
            ->whereNull('recycled_at');

        // Statistiques globales de collection pour les badges du HUD
        $totalCount = (clone $baseQuery)->count();
        $lockedCount = (clone $baseQuery)->where('is_locked', true)->count();
        $maxPowerScore = (clone $baseQuery)->max('power_score') ?? 0;
        
        $rarityCounts = [
            'legendary' => (clone $baseQuery)->where('rarity', 'legendary')->count(),
            'epic'      => (clone $baseQuery)->where('rarity', 'epic')->count(),
            'rare'      => (clone $baseQuery)->where('rarity', 'rare')->count(),
            'common'    => (clone $baseQuery)->where('rarity', 'common')->count(),
        ];

        // Construction de la requête filtrée de la grille
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

        if ($this->onlyLocked) {
            $filteredQuery->where('is_locked', true);
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

        // Aperçu du recyclage en masse en temps réel selon les critères de la modale
        $batchPreview = app(RecycleService::class)->getBatchRecyclePreview($user, $this->getBatchCriteria());

        // Héros inspecté dans la modale grand format (T1.5)
        $inspectedCharacter = $this->inspectedCharacterId
            ? Character::where('id', $this->inspectedCharacterId)
                ->where('user_id', $user->id)
                ->whereNull('recycled_at')
                ->first()
            : null;

        return view('livewire.collection-grid', [
            'characters'         => $characters,
            'totalCount'         => $totalCount,
            'filteredCount'      => $filteredCount,
            'lockedCount'        => $lockedCount,
            'rarityCounts'       => $rarityCounts,
            'maxPowerScore'      => $maxPowerScore,
            'races'              => RaceData::all(),
            'weapons'            => WeaponData::all(),
            'rarities'           => Rarity::cases(),
            'batchPreview'       => $batchPreview,
            'inspectedCharacter' => $inspectedCharacter,
        ]);
    }
}
