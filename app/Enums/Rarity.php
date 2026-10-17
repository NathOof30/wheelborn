<?php

namespace App\Enums;

enum Rarity: string
{
    case Common    = 'common';
    case Rare      = 'rare';
    case Epic      = 'epic';
    case Legendary = 'legendary';

    public function label(): string
    {
        return match($this) {
            self::Common    => 'Commun',
            self::Rare      => 'Rare',
            self::Epic      => 'Épique',
            self::Legendary => 'Légendaire',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Common    => '#94a3b8',
            self::Rare      => '#38bdf8',
            self::Epic      => '#c084fc',
            self::Legendary => '#fbbf24',
        };
    }

    public function recycleMultiplier(): float
    {
        return match($this) {
            self::Common    => 1.00,
            self::Rare      => 1.15,
            self::Epic      => 1.30,
            self::Legendary => 1.50,
        };
    }

    public function stars(): int
    {
        return match($this) {
            self::Common    => 1,
            self::Rare      => 2,
            self::Epic      => 3,
            self::Legendary => 5,
        };
    }
}
