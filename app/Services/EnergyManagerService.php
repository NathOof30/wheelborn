<?php

namespace App\Services;

use App\Models\User;
use App\Data\GameConfig;
use Carbon\Carbon;

/**
 * Gère les deux réserves d'énergie du joueur, qui fonctionnent de la même façon :
 * - les charges de spin (tirages de combattants) ;
 * - les tickets d'arène (duels).
 *
 * Chaque réserve a un maximum et se recharge d'une unité à intervalle régulier.
 * Le rattrapage hors-ligne est calculé à partir du dernier horodatage de recharge.
 */
class EnergyManagerService
{
    private const ADMIN_UNLIMITED = 999;

    // --- Charges de spin ---

    /**
     * Régénère les charges de spin du joueur en fonction du temps écoulé.
     */
    public function regenSpins(User $user): void
    {
        $this->regen($user, $this->spinPool());
    }

    /**
     * Consomme 1 charge de spin.
     */
    public function consumeSpin(User $user): bool
    {
        return $this->consume($user, $this->spinPool());
    }

    /**
     * Calcule le temps restant en secondes avant la prochaine charge de spin.
     */
    public function getNextSpinRegenSeconds(User $user): int
    {
        return $this->secondsUntilNext($user, $this->spinPool());
    }

    // --- Tickets d'arène ---

    /**
     * Régénère les tickets d'arène du joueur (1 toutes les 5 minutes, 5 maximum).
     */
    public function regenArenaTickets(User $user): void
    {
        $this->regen($user, $this->arenaPool());
    }

    /**
     * Consomme 1 ticket d'arène au lancement d'un duel.
     * Retourne false si le joueur n'a plus de ticket.
     */
    public function consumeArenaTicket(User $user): bool
    {
        return $this->consume($user, $this->arenaPool());
    }

    /**
     * Calcule le temps restant en secondes avant le prochain ticket d'arène.
     */
    public function getNextArenaRegenSeconds(User $user): int
    {
        return $this->secondsUntilNext($user, $this->arenaPool());
    }

    // --- Logique commune aux deux réserves ---

    /**
     * Décrit une réserve : colonne du stock, colonne d'horodatage, maximum et intervalle de recharge.
     */
    private function spinPool(): array
    {
        $config = GameConfig::spinEnergy();

        return ['stock' => 'spin_charges', 'timer' => 'last_spin_regen_at', 'max' => $config['max'], 'interval' => (int) $config['regen_interval_seconds']];
    }

    private function arenaPool(): array
    {
        $config = GameConfig::arenaEnergy();

        return ['stock' => 'arena_tickets', 'timer' => 'last_arena_regen_at', 'max' => $config['max'], 'interval' => (int) $config['regen_interval_seconds']];
    }

    private function regen(User $user, array $pool): void
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max, 'interval' => $interval] = $pool;

        // Admin : réserve illimitée
        if ($user->isAdmin()) {
            if ($user->{$stock} < self::ADMIN_UNLIMITED) {
                $user->update([$stock => self::ADMIN_UNLIMITED]);
            }
            return;
        }

        // Réserve pleine : plus besoin de chrono
        if ($user->{$stock} >= $max) {
            if ($user->{$timer} !== null) {
                $user->update([$timer => null]);
            }
            return;
        }

        // Réserve entamée sans chrono : on le démarre maintenant
        if (!$user->{$timer}) {
            $user->update([$timer => now()]);
            return;
        }

        // Rattrapage hors-ligne : autant d'unités que d'intervalles complets écoulés
        $lastRegen = Carbon::parse($user->{$timer});
        $elapsed = max(0, now()->timestamp - $lastRegen->timestamp);

        if ($elapsed >= $interval) {
            $recharged = intdiv($elapsed, $interval);
            $newStock = min($max, $user->{$stock} + $recharged);

            $user->update([
                $stock => $newStock,
                // On garde le reste de l'intervalle en cours pour ne pas perdre de temps de recharge
                $timer => $newStock >= $max ? null : $lastRegen->addSeconds($recharged * $interval),
            ]);
        }
    }

    private function consume(User $user, array $pool): bool
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max] = $pool;

        if ($user->isAdmin()) {
            return true; // Admin : aucune décrémentation
        }

        $this->regen($user, $pool);

        // Déduction atomique : la condition "stock > 0" est vérifiée par la base dans la même requête,
        // donc deux requêtes simultanées ne peuvent pas consommer la même unité.
        $consumed = User::whereKey($user->id)->where($stock, '>', 0)->decrement($stock);
        if (!$consumed) {
            return false;
        }

        $wasFull = $user->{$stock} >= $max;
        $user->{$stock}--;
        $user->syncOriginalAttribute($stock);

        // Si on passe sous le maximum ou qu'aucun chrono n'est posé, on démarre la recharge
        if ($wasFull || !$user->{$timer}) {
            $user->update([$timer => now()]);
        }

        return true;
    }

    private function secondsUntilNext(User $user, array $pool): int
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max, 'interval' => $interval] = $pool;

        if ($user->isAdmin() || $user->{$stock} >= $max) {
            return 0;
        }

        if (!$user->{$timer}) {
            return $interval;
        }

        $elapsed = max(0, now()->timestamp - Carbon::parse($user->{$timer})->timestamp);

        return max(0, $interval - ($elapsed % $interval));
    }
}
