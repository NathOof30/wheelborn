<?php

namespace App\Data;

class NameGenerator
{
    private const PREFIXES = [
        'Var', 'Zor', 'Lyr', 'Kor', 'Nym', 'Xer', 'Val', 'Ryn',
        'Thal', 'Drak', 'Zeph', 'Mal', 'Ka', 'Syr', 'Mor', 'Ael',
        'Tor', 'Brak', 'Gry', 'Vex', 'Sol', 'Nyx', 'Ign', 'Cryo',
        'Fen', 'Kael', 'Rha', 'Vol', 'Jax', 'Orn', 'Dax', 'Zen',
    ];

    private const SUFFIXES = [
        'k', 'an', 'a', 'r', 'on', 'o', 'is', 'en',
        'or', 'ath', 'ys', 'mir', 'th', 'us', 'ix', 'el',
        'um', 'ar', 'ox', 'ia', 'ax', 'or', 'in', 'ash',
    ];

    private const ROBOT_PREFIXES = ['MK', 'Unit', 'X', 'Cyber', 'Volt', 'Core', 'Nexus', 'Zero'];

    public static function generate(string $raceId = 'human'): string
    {
        if ($raceId === 'robot' && mt_rand(1, 100) > 40) {
            $pre = self::ROBOT_PREFIXES[array_rand(self::ROBOT_PREFIXES)];
            $num = mt_rand(1, 99);
            return "{$pre}-{$num}";
        }

        $prefix = self::PREFIXES[array_rand(self::PREFIXES)];
        $suffix = self::SUFFIXES[array_rand(self::SUFFIXES)];

        return ucfirst(strtolower($prefix . $suffix));
    }
}
