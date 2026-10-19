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
            if ($user->last_spin_regen_at !== null) {
                $user->update(['last_spin_regen_at' => null]);
            }
            return;
        }

        // Si le joueur est en déficit de charges mais n'a pas de timestamp de début,
        // on initialise le compteur de recharge dès maintenant.
        if (!$user->last_spin_regen_at) {
            $user->update(['last_spin_regen_at' => now()]);
            return;
        }

        $lastRegen = Carbon::parse($user->last_spin_regen_at);
        $elapsed = max(0, now()->timestamp - $lastRegen->timestamp);
        $regenInterval = (int) $config['regen_interval_seconds']; // 240 secondes (4 min)

        if ($elapsed >= $regenInterval) {
            $recharged = intdiv($elapsed, $regenInterval);
            $newCharges = min($config['max'], $user->spin_charges + $recharged);

            $user->update([
                'spin_charges' => $newCharges,
                'last_spin_regen_at' => $newCharges >= $config['max']
                    ? null
                    : $lastRegen->addSeconds($recharged * $regenInterval),
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

        // Si on passe sous la capacité maximale ou qu'aucun timestamp n'est posé, on démarre le chrono
        if ($wasMax || !$user->last_spin_regen_at) {
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

        if (!$user->last_spin_regen_at) {
            return (int) $config['regen_interval_seconds'];
        }

        $lastRegen = Carbon::parse($user->last_spin_regen_at);
        $elapsed = max(0, now()->timestamp - $lastRegen->timestamp);
        $interval = (int) $config['regen_interval_seconds'];

        return max(0, $interval - ($elapsed % $interval));
    }
}
