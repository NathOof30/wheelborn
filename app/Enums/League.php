<?php

namespace App\Enums;

enum League: string
{
    case Bronze   = 'bronze';
    case Silver   = 'silver';
    case Gold     = 'gold';
    case Platinum = 'platinum';
    case Master   = 'master';

    public static function fromElo(?int $elo = null): self
    {
        $elo = $elo ?? 1000;
        return match(true) {
            $elo >= 1800 => self::Master,
            $elo >= 1500 => self::Platinum,
            $elo >= 1250 => self::Gold,
            $elo >= 1000 => self::Silver,
            default      => self::Bronze,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Bronze   => 'Bronze',
            self::Silver   => 'Argent',
            self::Gold     => 'Or',
            self::Platinum => 'Platine',
            self::Master   => 'Maître',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Bronze   => '🥉',
            self::Silver   => '🥈',
            self::Gold     => '🥇',
            self::Platinum => '💎',
            self::Master   => '👑',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Bronze   => '#cd7f32',
            self::Silver   => '#94a3b8',
            self::Gold     => '#fbbf24',
            self::Platinum => '#38bdf8',
            self::Master   => '#c084fc',
        };
    }

    public function minElo(): int
    {
        return match($this) {
            self::Bronze   => 0,
            self::Silver   => 1000,
            self::Gold     => 1250,
            self::Platinum => 1500,
            self::Master   => 1800,
        };
    }
}
