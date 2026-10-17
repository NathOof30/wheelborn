<?php

namespace App\Enums;

enum PowerTier: string
{
    case Common = 'common';
    case Rare   = 'rare';
    case Mythic = 'mythic';

    public function label(): string
    {
        return match($this) {
            self::Common => 'Commun',
            self::Rare   => 'Rare',
            self::Mythic => 'Mythique',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Common => '#94a3b8',
            self::Rare   => '#38bdf8',
            self::Mythic => '#f43f5e',
        };
    }
}
