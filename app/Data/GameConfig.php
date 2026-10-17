<?php

namespace App\Data;

class GameConfig
{
    public const GAME_TITLE = 'WHEELBORN';
    public const VERSION = '1.0.0';

    public static function spinEnergy(): array
    {
        return [
            'max' => 3,
            'regen_interval_seconds' => 240, // 4 minutes
            'starter_charges' => 3,
        ];
    }

    public static function arenaEnergy(): array
    {
        return [
            'max' => 5,
            'regen_interval_seconds' => 300, // 5 minutes
            'starter_tickets' => 5,
        ];
    }

    public static function pityThreshold(): int
    {
        return 10;
    }

    public static function baseStats(): array
    {
        return [
            'hp_min'       => 55,
            'hp_max'       => 75,
            'strength_min' => 45,
            'strength_max' => 70,
            'speed_min'    => 40,
            'speed_max'    => 68,
            'defense_min'  => 42,
            'defense_max'  => 68,
        ];
    }

    public static function powerWeights(): array
    {
        return [
            'strength'       => 0.32,
            'hp'             => 0.24,
            'defense'        => 0.24,
            'speed'          => 0.20,
            'synergy_bonus'  => 3,
            'unstable_bonus' => 2,
        ];
    }

    public static function rarityWeights(bool $isLuckySpin = false): array
    {
        if ($isLuckySpin) {
            return [
                'common'    => 0,
                'rare'      => 60,
                'epic'      => 30,
                'legendary' => 10,
            ];
        }

        return [
            'common'    => 72,
            'rare'      => 21,
            'epic'      => 6,
            'legendary' => 1,
        ];
    }

    public static function rewards(): array
    {
        return [
            'win_coins_base'        => 65,
            'loss_coins_base'       => 20,
            'win_xp'                => 60,
            'loss_xp'               => 20,
            'generate_xp'           => 10,
            'lucky_spin_xp'         => 100,
            'synergy_discover_xp'   => 80,
            'legendary_discover_xp' => 150,
        ];
    }

    public static function workshop(): array
    {
        return [
            'rare_magnet' => [
                'name' => 'Aimant à Pouvoirs',
                'icon' => '🧲',
                'max_level' => 5,
                'costs' => [200, 450, 750, 1100, 1600],
                'bonus_per_level' => 3,
            ],
            'hero_forge' => [
                'name' => 'Forge des Héros',
                'icon' => '⚡',
                'max_level' => 5,
                'costs' => [150, 350, 650, 950, 1400],
                'bonus_hp' => 3,
                'bonus_str' => 2,
            ],
            'arena_bonus' => [
                'name' => 'Prime d\'Arène',
                'icon' => '🛡️',
                'max_level' => 3,
                'costs' => [300, 600, 1000],
                'multipliers' => [1.25, 1.50, 2.00],
            ],
            'emergency_spin' => [
                'name' => 'Jeton d\'Urgence',
                'icon' => '🎟️',
                'base_cost' => 120,
                'cost_multiplier' => 1.6,
            ],
        ];
    }
}
