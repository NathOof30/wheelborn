<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;
use Illuminate\Support\Facades\DB;

class RecycleService
{
    /**
     * Recycle un personnage et crédite les pièces à l'utilisateur.
     * Transaction sécurisée.
     */
    public function recycle(User $user, string $characterId): int
    {
        return DB::transaction(function () use ($user, $characterId) {
            $character = Character::where('id', $characterId)
                ->where('user_id', $user->id)
                ->whereNull('recycled_at')
                ->lockForUpdate()
                ->firstOrFail();

            $coins = $character->recycle_value;

            $character->update([
                'recycled_at' => now(),
                'is_active_fighter' => false,
            ]);

            $user->increment('coins', $coins);

            // Mettre à jour les stats du joueur si elles existent
            if ($user->playerStats) {
                $user->playerStats->increment('total_recycled');
                $user->playerStats->increment('coins_earned_total', $coins);
            }

            return $coins;
        });
    }
}
