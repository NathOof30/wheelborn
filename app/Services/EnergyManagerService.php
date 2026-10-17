<?php

namespace App\Services;

use App\Models\User;
use App\Data\GameConfig;
use Carbon\Carbon;

class EnergyManagerService
{
    /**
     * Régénère les charges de spin du joueur en fonction du temps écoulé.
     */
    public function regenSpins(User $user): void
    {
        if ($user->isAdmin()) {
            if ($user->spin_charges < 999) {
                $user->update(['spin_charges' => 999]);
            }
            return;
        }

        $config = GameConfig::spinEnergy();

        if ($user->spin_charges >= $config['max']) {
            return;
        }

        $lastRegen = $user->last_spin_regen_at ?? now();
        $elapsed = now()->diffInSeconds($lastRegen);
        $regenInterval = $config['regen_interval_seconds']; // 240s

        if ($elapsed >= $regenInterval) {
            $recharged = intdiv($elapsed, $regenInterval);
            $newCharges = min($config['max'], $user->spin_charges + $recharged);

            $user->update([
                'spin_charges' => $newCharges,
                'last_spin_regen_at' => $newCharges >= $config['max']
                    ? null
                    : Carbon::parse($lastRegen)->addSeconds($recharged * $regenInterval),
            ]);
        }
    }

    /**
     * Consomme 1 charge de spin.
     */
    public function consumeSpin(User $user): bool
    {
        if ($user->isAdmin()) {
            return true; // Admin : tirages illimités, aucune décrémentation
        }

        $this->regenSpins($user);

        if ($user->spin_charges <= 0) {
            return false;
        }

        $wasMax = $user->spin_charges >= GameConfig::spinEnergy()['max'];

        $user->decrement('spin_charges');

        if ($wasMax) {
            $user->update(['last_spin_regen_at' => now()]);
        }

        return true;
    }

    /**
     * Calcule le temps restant en secondes avant la prochaine charge de spin.
     */
    public function getNextSpinRegenSeconds(User $user): int
    {
        if ($user->isAdmin()) {
            return 0;
        }

        $config = GameConfig::spinEnergy();
        if ($user->spin_charges >= $config['max']) {
            return 0;
        }

        $lastRegen = $user->last_spin_regen_at ?? now();
        $elapsed = now()->diffInSeconds($lastRegen);
        $interval = $config['regen_interval_seconds'];

        return max(0, $interval - ($elapsed % $interval));
    }
}
