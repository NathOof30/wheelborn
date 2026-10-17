<?php

namespace App\Data;

class WeaponData
{
    public static function all(): array
    {
        return [
            [
                'id' => 'sword',
                'name' => 'Épée',
                'icon' => '⚔️',
                'type' => 'melee',
                'description' => 'Épée forgée équilibrant tranchant et parade.',
                'mods' => ['hp' => 0, 'strength' => 12, 'speed' => 4, 'defense' => 6],
                'passive' => 'Tranchant droit',
                'passive_desc' => '+12 Force et +6 Défense.',
                'weight' => 15,
            ],
            [
                'id' => 'bow',
                'name' => 'Arc',
                'icon' => '🏹',
                'type' => 'ranged',
                'description' => 'Tir rapide à longue distance.',
                'mods' => ['hp' => 0, 'strength' => 6, 'speed' => 16, 'defense' => -2],
                'passive' => 'Tir d\'alerte',
                'passive_desc' => '+16 Vitesse. Permet souvent d\'attaquer en premier.',
                'weight' => 15,
            ],
            [
                'id' => 'hammer',
                'name' => 'Marteau',
                'icon' => '🔨',
                'type' => 'heavy',
                'description' => 'Masse gigantesque infligeant de violents traumatismes.',
                'mods' => ['hp' => 5, 'strength' => 24, 'speed' => -12, 'defense' => 8],
                'passive' => 'Impact lourd',
                'passive_desc' => 'Dégâts très élevés, 25% de chance d\'étourdir la cible.',
                'stun_chance' => 0.25,
                'weight' => 12,
            ],
            [
                'id' => 'spear',
                'name' => 'Lance',
                'icon' => '🔱',
                'type' => 'reach',
                'description' => 'Allonge imposante permettant de frapper avant l\'ennemi.',
                'mods' => ['hp' => 2, 'strength' => 10, 'speed' => 10, 'defense' => 4],
                'passive' => 'Estoc fulgurant',
                'passive_desc' => '+25% dégâts lors du tout premier coup porté.',
                'first_strike_bonus' => 0.25,
                'weight' => 14,
            ],
            [
                'id' => 'dagger',
                'name' => 'Dague',
                'icon' => '🗡️',
                'type' => 'assassin',
                'description' => 'Lame furtive redoutable contre les cibles plus lentes.',
                'mods' => ['hp' => -2, 'strength' => 8, 'speed' => 20, 'defense' => -4],
                'passive' => 'Opportunisme',
                'passive_desc' => '+30% dégâts si la vitesse dépasse celle de l\'adversaire.',
                'speed_advantage_bonus' => 0.30,
                'weight' => 12,
            ],
            [
                'id' => 'staff',
                'name' => 'Bâton',
                'icon' => '🪄',
                'type' => 'magic',
                'description' => 'Bâton d\'arcaniste canalisant l\'énergie vitale et magique.',
                'mods' => ['hp' => 8, 'strength' => 6, 'speed' => 6, 'defense' => 6],
                'passive' => 'Résonance arcanique',
                'passive_desc' => 'Renforce les soins et les effets de contrôle des pouvoirs.',
                'magic_empower' => 1.25,
                'weight' => 12,
            ],
            [
                'id' => 'gun',
                'name' => 'Pistolet',
                'icon' => '🔫',
                'type' => 'ranged',
                'description' => 'Arme balistique rapide avec tirs perforants.',
                'mods' => ['hp' => 0, 'strength' => 14, 'speed' => 12, 'defense' => -4],
                'passive' => 'Tir de précision',
                'passive_desc' => '25% de chance de coup critique (dégâts x1.75).',
                'crit_chance' => 0.25,
                'crit_multiplier' => 1.75,
                'weight' => 11,
            ],
            [
                'id' => 'grimoire',
                'name' => 'Grimoire',
                'icon' => '📖',
                'type' => 'spellbook',
                'description' => 'Tome ancien renfermant des formules élémentaires dévastatrices.',
                'mods' => ['hp' => 0, 'strength' => 16, 'speed' => 2, 'defense' => 2],
                'passive' => 'Magie primordiale',
                'passive_desc' => '+35% sur l\'effet des pouvoirs élémentaires (Feu, Glace, Poison, Foudre).',
                'elemental_boost' => 0.35,
                'weight' => 11,
            ],
        ];
    }

    public static function find(string $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }
}
