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

    // --- TICKET D'URGENCE (achat ponctuel d'une charge de spin) ---

    /**
     * État du Ticket d'Urgence aujourd'hui : achats faits, achats restants et prix du prochain (null si épuisé).
     * Le compteur repart à zéro chaque jour.
     */
    public function emergencySpinStatus(User $user): array
    {
        $costs = GameConfig::workshop()['emergency_spin']['costs'];
        $purchases = $this->emergencyPurchasesToday($this->upgradesOf($user));

        return [
            'purchases_today' => $purchases,
            'remaining'       => max(0, count($costs) - $purchases),
            'next_cost'       => $costs[$purchases] ?? null,
        ];
    }

    /**
     * Achète une charge de spin quand le joueur n'en a plus (120 🪙, puis 250 🪙, 2 fois par jour au maximum).
     * Même protection que les améliorations : joueur et atelier verrouillés pendant la transaction.
     *
     * @return array{cost: int, coins: int, spin_charges: int}
     * @throws DomainException charges encore disponibles, limite du jour atteinte ou or insuffisant
     */
    public function buyEmergencySpin(User $user): array
    {
        $costs = GameConfig::workshop()['emergency_spin']['costs'];

        $result = DB::transaction(function () use ($user, $costs) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            // Une charge a pu se recharger depuis le dernier affichage : on ne vend pas ce que le joueur a déjà
            app(EnergyManagerService::class)->regenSpins($lockedUser);
            if ($lockedUser->spin_charges > 0) {
                throw new DomainException("Tu as encore des charges de tirage : le Ticket d'Urgence sert quand la réserve est vide.");
            }

            $upgrades = WorkshopUpgrade::where('user_id', $lockedUser->id)->lockForUpdate()->first()
                ?? WorkshopUpgrade::create(['user_id' => $lockedUser->id]);

            $purchases = $this->emergencyPurchasesToday($upgrades);
            $cost = $costs[$purchases] ?? null;

            if ($cost === null) {
                throw new DomainException("Limite atteinte : " . count($costs) . " Tickets d'Urgence par jour maximum.");
            }

            if ($lockedUser->coins < $cost) {
                throw new DomainException("Pas assez de pièces : {$cost} 🪙 requises, tu en as {$lockedUser->coins}.");
            }

            $lockedUser->update([
                'coins'        => $lockedUser->coins - $cost,
                'spin_charges' => $lockedUser->spin_charges + 1,
            ]);
            $upgrades->update([
                'emergency_purchases_today' => $purchases + 1,
                'last_emergency_date'       => today(),
            ]);

            return [
                'cost'         => $cost,
                'coins'        => $lockedUser->coins,
                'spin_charges' => $lockedUser->spin_charges,
            ];
        });

        // Le modèle de l'appelant reflète le nouveau solde et la charge achetée
        $user->forceFill(['coins' => $result['coins'], 'spin_charges' => $result['spin_charges']])
            ->syncOriginalAttributes(['coins', 'spin_charges']);

        return $result;
    }

    /** Achats du jour : le compteur enregistré ne vaut que s'il date d'aujourd'hui. */
    private function emergencyPurchasesToday(WorkshopUpgrade $upgrades): int
    {
        return $upgrades->last_emergency_date?->isToday() ? $upgrades->emergency_purchases_today : 0;
    }

    // --- EFFETS ACTUELS (lus par la génération et l'arène) ---

    /** Aimant à Pouvoirs Rares : points de pourcentage ajoutés aux chances de pouvoirs mythiques (+3 par niveau). */
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
