<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\GameConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Consomme 1 ticket d'arène sur un utilisateur déjà verrouillé dans une transaction active.
     */
    public function consumeArenaTicketLocked(User $lockedUser): bool
    {
        return $this->consumeLocked($lockedUser, $this->arenaPool());
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
        ['stock' => $stock, 'timer' => $timer] = $pool;

        $lockedUser = DB::transaction(function () use ($user, $pool): User {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->regenLocked($locked, $pool);

            return $locked;
        });

        $this->syncPoolState($user, $lockedUser, $stock, $timer);
    }

    private function regenLocked(User $lockedUser, array $pool): void
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max, 'interval' => $interval] = $pool;

        if ($lockedUser->isAdmin()) {
            if ($lockedUser->{$stock} < self::ADMIN_UNLIMITED) {
                $lockedUser->update([$stock => self::ADMIN_UNLIMITED]);
            }

            return;
        }

        if ($lockedUser->{$stock} >= $max) {
            if ($lockedUser->{$timer} !== null) {
                $lockedUser->update([$timer => null]);
            }

            return;
        }

        if (! $lockedUser->{$timer}) {
            $lockedUser->update([$timer => now()]);

            return;
        }

        $lastRegen = Carbon::parse($lockedUser->{$timer});
        $elapsed = max(0, (int) (now()->timestamp - $lastRegen->timestamp));

        if ($elapsed >= $interval) {
            $recharged = intdiv($elapsed, $interval);
            $newStock = min($max, (int) $lockedUser->{$stock} + $recharged);

            $lockedUser->update([
                $stock => $newStock,
                $timer => $newStock >= $max ? null : $lastRegen->copy()->addSeconds($recharged * $interval),
            ]);
        }
    }

    private function consume(User $user, array $pool): bool
    {
        ['stock' => $stock, 'timer' => $timer] = $pool;

        if ($user->isAdmin()) {
            return true;
        }

        [$consumed, $lockedUser] = DB::transaction(function () use ($user, $pool): array {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            return [$this->consumeLocked($locked, $pool), $locked];
        });

        $this->syncPoolState($user, $lockedUser, $stock, $timer);

        return $consumed;
    }

    private function consumeLocked(User $lockedUser, array $pool): bool
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max] = $pool;

        if ($lockedUser->isAdmin()) {
            return true;
        }

        $this->regenLocked($lockedUser, $pool);

        if ((int) $lockedUser->{$stock} <= 0) {
            return false;
        }

        $wasFull = (int) $lockedUser->{$stock} >= $max;
        $newStock = (int) $lockedUser->{$stock} - 1;
        $updates = [$stock => $newStock];

        if ($wasFull || ! $lockedUser->{$timer}) {
            $updates[$timer] = now();
        }

        $lockedUser->update($updates);

        return true;
    }

    private function syncPoolState(User $target, User $source, string $stock, string $timer): void
    {
        $target->{$stock} = $source->{$stock};
        $target->{$timer} = $source->{$timer};
        $target->syncOriginalAttribute($stock);
        $target->syncOriginalAttribute($timer);
    }

    private function secondsUntilNext(User $user, array $pool): int
    {
        ['stock' => $stock, 'timer' => $timer, 'max' => $max, 'interval' => $interval] = $pool;

        if ($user->isAdmin() || $user->{$stock} >= $max) {
            return 0;
        }

        if (! $user->{$timer}) {
            return $interval;
        }

        $elapsed = max(0, now()->timestamp - Carbon::parse($user->{$timer})->timestamp);

        return max(0, $interval - ($elapsed % $interval));
    }
}
