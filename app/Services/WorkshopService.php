<?php

namespace App\Services;

use App\Data\GameConfig;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Atelier : achat d'améliorations permanentes avec l'or du recyclage et des victoires.
 * Les coûts et effets sont définis dans GameConfig::workshop(). Ce service donne aussi l'effet actuel
 * de chaque amélioration, pour que la génération et l'arène n'aient pas à relire les niveaux eux-mêmes.
 */
class WorkshopService
{
    /** Atelier du joueur (créé à la volée, tous niveaux à 0). */
    public function upgradesOf(User $user): WorkshopUpgrade
    {
        return WorkshopUpgrade::firstOrCreate(['user_id' => $user->id]);
    }

    /** Définition d'une amélioration permanente (Aimant, Forge, Coffre). */
    public function definition(string $upgrade): array
    {
        if (!array_key_exists($upgrade, WorkshopUpgrade::LEVEL_COLUMNS)) {
            throw new DomainException("Amélioration inconnue : {$upgrade}.");
        }

        return GameConfig::workshop()[$upgrade];
    }

    /** Coût du prochain niveau, ou null si l'amélioration est déjà au maximum. */
    public function nextCost(string $upgrade, int $currentLevel): ?int
    {
        $definition = $this->definition($upgrade);

        return $currentLevel < $definition['max_level'] ? $definition['costs'][$currentLevel] : null;
    }

    /** État de chaque amélioration pour l'affichage : niveau, maximum, coût du suivant et achat possible ou non. */
    public function overview(User $user): array
    {
        $upgrades = $this->upgradesOf($user);

        $overview = [];
        foreach (array_keys(WorkshopUpgrade::LEVEL_COLUMNS) as $key) {
            $definition = $this->definition($key);
            $level = $upgrades->levelOf($key);
            $nextCost = $this->nextCost($key, $level);

            $overview[$key] = [
                'key'        => $key,
                'name'       => $definition['name'],
                'icon'       => $definition['icon'],
                'level'      => $level,
                'max_level'  => $definition['max_level'],
                'next_cost'  => $nextCost,
                'is_maxed'   => $nextCost === null,
                'can_afford' => $nextCost !== null && $user->coins >= $nextCost,
            ];
        }

        return $overview;
    }

    /**
     * Achète le niveau suivant d'une amélioration.
     * Le joueur est verrouillé (lockForUpdate) pendant la transaction : deux achats simultanés
     * ne peuvent ni dépenser le même or ni sauter un niveau.
     *
     * @return array{upgrade: string, level: int, cost: int, coins: int}
     * @throws DomainException amélioration inconnue, déjà au maximum ou or insuffisant
     */
    public function purchase(User $user, string $upgrade): array
    {
        $this->definition($upgrade); // Rejette une clé inconnue avant d'ouvrir la transaction

        $result = DB::transaction(function () use ($user, $upgrade) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $upgrades = WorkshopUpgrade::where('user_id', $lockedUser->id)->lockForUpdate()->first()
                ?? WorkshopUpgrade::create(['user_id' => $lockedUser->id]);

            $level = $upgrades->levelOf($upgrade);
            $cost = $this->nextCost($upgrade, $level);

            if ($cost === null) {
                throw new DomainException('Cette amélioration est déjà au niveau maximum.');
            }

            if ($lockedUser->coins < $cost) {
                throw new DomainException("Pas assez de pièces : {$cost} 🪙 requises, tu en as {$lockedUser->coins}.");
            }

            $lockedUser->decrement('coins', $cost);
            $upgrades->increment(WorkshopUpgrade::LEVEL_COLUMNS[$upgrade]);

            return [
                'upgrade' => $upgrade,
                'level'   => $level + 1,
                'cost'    => $cost,
                'coins'   => $lockedUser->coins,
            ];
        });

        // Le modèle de l'appelant reflète le nouveau solde (HUD, vérifications suivantes)
        $user->coins = $result['coins'];
        $user->syncOriginalAttribute('coins');

        return $result;
    }

    // --- EFFETS ACTUELS (lus par la génération et l'arène) ---

    /** Aimant à Pouvoirs Rares : points de pourcentage ajoutés aux chances de pouvoirs rares/mythiques (+3 par niveau). */
    public function rareMagnetBonus(User $user): int
    {
        return $this->upgradesOf($user)->levelOf('rare_magnet') * $this->definition('rare_magnet')['bonus_per_level'];
    }

    /** Forge des Héros : stats ajoutées à chaque futur tirage (+3 PV et +2 Force par niveau). */
    public function heroForgeBonus(User $user): array
    {
        $level = $this->upgradesOf($user)->levelOf('hero_forge');
        $definition = $this->definition('hero_forge');

        return [
            'hp'       => $level * $definition['bonus_hp'],
            'strength' => $level * $definition['bonus_str'],
        ];
    }

    /** Coffre de Guerre : multiplicateur de l'or gagné par victoire d'arène (×1 sans amélioration). */
    public function arenaCoinMultiplier(User $user): float
    {
        $level = $this->upgradesOf($user)->levelOf('arena_bonus');

        return $level > 0 ? (float) $this->definition('arena_bonus')['multipliers'][$level - 1] : 1.0;
    }
}
