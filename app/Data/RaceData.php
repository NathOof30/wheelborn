<?php

namespace App\Data;

class RaceData
{
    public static function all(): array
    {
        return [
            [
                'id' => 'human',
                'name' => 'Humain',
                'icon' => '👤',
                'weight' => 20,
                'mods' => ['hp' => 5, 'strength' => 5, 'speed' => 5, 'defense' => 5],
                'passive' => 'Adaptabilité',
                'passive_desc' => 'Bonus universel équilibré.',
            ],
            [
                'id' => 'elf',
                'name' => 'Elfe',
                'icon' => '🧝',
                'weight' => 15,
                'mods' => ['hp' => -2, 'strength' => 2, 'speed' => 18, 'defense' => -10],
                'passive' => 'Grâce Sylvestre',
                'passive_desc' => 'Initiative et vélocité extrêmes.',
            ],
            [
                'id' => 'demon',
                'name' => 'Démon',
                'icon' => '😈',
                'weight' => 12,
                'mods' => ['hp' => 4, 'strength' => 20, 'speed' => 6, 'defense' => -12],
                'passive' => 'Fureur Infernale',
                'passive_desc' => 'Frappe massive, défense vulnérable.',
            ],
            [
                'id' => 'robot',
                'name' => 'Robot',
                'icon' => '🤖',
                'weight' => 12,
                'mods' => ['hp' => 12, 'strength' => 6, 'speed' => -14, 'defense' => 22],
                'passive' => 'Châssis Titane',
                'passive_desc' => 'Blindage lourd, pénalité de vitesse.',
            ],
            [
                'id' => 'mutant',
                'name' => 'Mutant',
                'icon' => '☣️',
                'weight' => 10,
                'mods' => ['hp' => 0, 'strength' => 0, 'speed' => 0, 'defense' => 0],
                'passive' => 'Génome Chaotique',
                'passive_desc' => 'Tirages imprévisibles par stat (variance [-12, +20]).',
                'is_mutant' => true,
            ],
            [
                'id' => 'golem',
                'name' => 'Golem',
                'icon' => '🗿',
                'weight' => 10,
                'mods' => ['hp' => 25, 'strength' => 12, 'speed' => -22, 'defense' => 25],
                'passive' => 'Écorce Rocheuse',
                'passive_desc' => 'Sac de PV et armure colossale.',
            ],
            [
                'id' => 'vampire',
                'name' => 'Vampire',
                'icon' => '🧛',
                'weight' => 11,
                'mods' => ['hp' => -4, 'strength' => 14, 'speed' => 10, 'defense' => -6],
                'passive' => 'Soif de Sang',
                'passive_desc' => 'Vole 12% des dégâts infligés en PV.',
            ],
            [
                'id' => 'alien',
                'name' => 'Alien',
                'icon' => '👽',
                'weight' => 10,
                'mods' => ['hp' => 6, 'strength' => 8, 'speed' => 8, 'defense' => 6],
                'passive' => 'Biologie Stellaire',
                'passive_desc' => '+8% d\'esquive innée et synergies cosmiques.',
            ],
        ];
    }

    public static function find(string $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }
}
