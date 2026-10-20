<?php

namespace App\Enums;

enum League: string
{
    case Bronze   = 'bronze';
    case Silver   = 'silver';
    case Gold     = 'gold';
    case Platinum = 'platinum';
    case Master   = 'master';

    /**
     * Ligue correspondant à un score d'Élo.
     * On parcourt les ligues de la plus haute à la plus basse : la première dont le palier est atteint l'emporte.
     */
    public static function fromElo(?int $elo = null): self
    {
        $elo = $elo ?? 1000;

        foreach (array_reverse(self::cases()) as $league) {
            if ($elo >= $league->minElo()) {
                return $league;
            }
        }

        return self::Bronze;
    }

    /** Vrai si cette ligue est au-dessus de l'autre (sert à détecter une promotion). */
    public function isHigherThan(self $other): bool
    {
        return $this->minElo() > $other->minElo();
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

    /**
     * Palier d'entrée de la ligue (seul endroit où les paliers sont définis).
     * Bronze 0-999, Argent 1000-1399, Or 1400-1799, Platine 1800-2199, Maître 2200+.
     */
    public function minElo(): int
    {
        return match($this) {
            self::Bronze   => 0,
            self::Silver   => 1000,
            self::Gold     => 1400,
            self::Platinum => 1800,
            self::Master   => 2200,
        };
    }
}
