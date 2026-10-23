<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;
use App\Events\CharactersRecycled;
use Illuminate\Support\Facades\DB;
use DomainException;

class RecycleService
{
    /**
     * Recycle un combattant unique et crédite les pièces au joueur.
     * Transaction sécurisée avec blocage strict si la carte est verrouillée.
     *
     * @throws DomainException si la carte est verrouillée
     */
    public function recycle(User $user, string $characterId): int
    {
        $coins = DB::transaction(function () use ($user, $characterId) {
            $lockedUser = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $character = Character::where('id', $characterId)
                ->where('user_id', $lockedUser->id)
                ->whereNull('recycled_at')
                ->lockForUpdate()
                ->firstOrFail();

            if ($character->is_locked) {
                throw new DomainException("Ce combattant est verrouillé ! Déverrouillez-le avant de pouvoir le recycler.");
            }

            if ($character->is_active_fighter) {
                throw new DomainException("Ce combattant est actuellement déployé dans l'arène et ne peut pas être recyclé.");
            }

            $coins = $character->recycle_value;

            $character->update([
                'recycled_at'       => now(),
                'is_active_fighter' => false,
                'pending_reveal'    => false,
            ]);

            $lockedUser->increment('coins', $coins);

            // Mettre à jour les stats du joueur si elles existent
            if ($lockedUser->playerStats) {
                $lockedUser->playerStats->increment('total_recycled');
                $lockedUser->playerStats->increment('coins_earned_total', $coins);
            }

            return $coins;
        });

        // Succès liés au recyclage (« Maître Alchimiste »)
        CharactersRecycled::dispatch($user, 1);

        return $coins;
    }

    /**
     * Normalise les critères passés en paramètre pour assurer la rétrocompatibilité
     * avec les appels simples $recycleService->recycleBatch($user, ['common']).
     */
    protected function normalizeCriteria(array $criteria): array
    {
        if (isset($criteria[0]) && is_string($criteria[0])) {
            return ['rarities' => $criteria];
        }

        return $criteria;
    }

    /**
     * Construit la requête des combattants éligibles au recyclage de masse selon les critères avancés.
     */
    protected function buildBatchQuery(User $user, array $criteria)
    {
        $query = Character::where('user_id', $user->id)
            ->where('pending_reveal', false)
            ->where('is_locked', false)
            ->where('is_active_fighter', false)
            ->whereNull('recycled_at');

        // 1. Filtre par raretés sélectionnées
        $rarities = $criteria['rarities'] ?? [];
        if (!empty($rarities)) {
            $query->whereIn('rarity', $rarities);
        }

        // 2. Filtre par score de puissance maximum (<= max_power)
        if (isset($criteria['max_power']) && $criteria['max_power'] !== null && $criteria['max_power'] !== '') {
            $query->where('power_score', '<=', (int) $criteria['max_power']);
        }

        // 3. Filtre par score de puissance minimum (>= min_power)
        if (isset($criteria['min_power']) && $criteria['min_power'] !== null && $criteria['min_power'] !== '') {
            $query->where('power_score', '>=', (int) $criteria['min_power']);
        }

        // 4. Filtre par race
        if (!empty($criteria['race_id'])) {
            $query->where('race_id', $criteria['race_id']);
        }

        // 5. Filtre par arme
        if (!empty($criteria['weapon_id'])) {
            $query->where('weapon_id', $criteria['weapon_id']);
        }

        // 6. Exclusion des cartes avec synergies actives
        if (!empty($criteria['exclude_synergies'])) {
            $query->where(function ($q) {
                $q->whereNull('synergies_data')
                  ->orWhere('synergies_data', '[]')
                  ->orWhere('synergies_data', '');
            });
        }

        return $query;
    }

    /**
     * Calcule le récapitulatif prévisionnel d'un recyclage en masse (sans modifier la BDD).
     * Retourne le nombre, le total de pièces et la liste des héros concernés pour l'aperçu visuel.
     */
    public function getBatchRecyclePreview(User $user, array $criteria = ['rarities' => ['common']]): array
    {
        $criteria = $this->normalizeCriteria($criteria);

        if (empty($criteria['rarities']) && empty($criteria['race_id']) && empty($criteria['weapon_id']) && empty($criteria['max_power'])) {
            return [
                'count'      => 0,
                'coins'      => 0,
                'characters' => collect(),
            ];
        }

        $characters = $this->buildBatchQuery($user, $criteria)
            ->orderByDesc('power_score')
            ->get();

        return [
            'count'      => $characters->count(),
            'coins'      => (int) $characters->sum('recycle_value'),
            'characters' => $characters,
        ];
    }

    /**
     * Recycle en masse tous les combattants non verrouillés selon des critères multicritères avancés.
     * Transaction atomique unique avec lock.
     *
     * @return array{count: int, coins: int}
     */
    public function recycleBatch(User $user, array $criteria = ['rarities' => ['common']]): array
    {
        $criteria = $this->normalizeCriteria($criteria);

        if (empty($criteria['rarities']) && empty($criteria['race_id']) && empty($criteria['weapon_id']) && empty($criteria['max_power'])) {
            return ['count' => 0, 'coins' => 0];
        }

        $result = DB::transaction(function () use ($user, $criteria) {
            $lockedUser = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $characters = $this->buildBatchQuery($lockedUser, $criteria)
                ->lockForUpdate()
                ->get();

            if ($characters->isEmpty()) {
                return ['count' => 0, 'coins' => 0];
            }

            $count = $characters->count();
            $totalCoins = (int) $characters->sum('recycle_value');

            Character::whereIn('id', $characters->pluck('id'))
                ->update([
                    'recycled_at'       => now(),
                    'is_active_fighter' => false,
                    'pending_reveal'    => false,
                ]);

            $lockedUser->increment('coins', $totalCoins);

            if ($lockedUser->playerStats) {
                $lockedUser->playerStats->increment('total_recycled', $count);
                $lockedUser->playerStats->increment('coins_earned_total', $totalCoins);
            }

            return [
                'count' => $count,
                'coins' => $totalCoins,
            ];
        });

        if ($result['count'] > 0) {
            CharactersRecycled::dispatch($user, $result['count']);
        }

        return $result;
    }
}
