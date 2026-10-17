<?php

namespace App\Data;

class PowerData
{
    public static function all(): array
    {
        return [
            [
                'id' => 'fire',
                'name' => 'Feu',
                'icon' => '🔥',
                'tier' => 'common',
                'tier_label' => 'Commun',
                'description' => 'Brûle la cible, infligeant des dégâts continus sur plusieurs tours.',
                'type' => 'elemental',
                'effect' => [
                    'type' => 'burn',
                    'damage_per_turn' => 8,
                    'duration' => 3,
                    'label' => 'Brûlure',
                ],
                'weight' => 30,
            ],
            [
                'id' => 'poison',
                'name' => 'Poison',
                'icon' => '🧪',
                'tier' => 'common',
                'tier_label' => 'Commun',
                'description' => 'Toxine mortelle qui s\'intensifie à chaque tour de combat.',
                'type' => 'elemental',
                'effect' => [
                    'type' => 'poison',
                    'base_damage' => 5,
                    'ramping' => 5,
                    'duration' => 3,
                    'label' => 'Envenimement',
                ],
                'weight' => 30,
            ],
            [
                'id' => 'ice',
                'name' => 'Glace',
                'icon' => '❄️',
                'tier' => 'rare',
                'tier_label' => 'Rare',
                'description' => 'Gèle les articulations adverses, réduisant drastiquement leur vitesse.',
                'type' => 'elemental',
                'effect' => [
                    'type' => 'slow',
                    'speed_reduction_percent' => 35,
                    'freeze_chance' => 0.15,
                    'label' => 'Ralentissement glacial',
                ],
                'weight' => 10,
            ],
            [
                'id' => 'lightning',
                'name' => 'Foudre',
                'icon' => '⚡',
                'tier' => 'rare',
                'tier_label' => 'Rare',
                'description' => 'Énergie électrique pure conférant une haute chance de coup critique.',
                'type' => 'elemental',
                'effect' => [
                    'type' => 'crit_boost',
                    'crit_chance' => 0.35,
                    'crit_multiplier' => 1.8,
                    'label' => 'Choc foudroyant',
                ],
                'weight' => 10,
            ],
            [
                'id' => 'regeneration',
                'name' => 'Régénération',
                'icon' => '💖',
                'tier' => 'rare',
                'tier_label' => 'Rare',
                'description' => 'Restaure des points de vie de manière continue tout au long du duel.',
                'type' => 'vital',
                'effect' => [
                    'type' => 'heal',
                    'heal_per_turn' => 12,
                    'label' => 'Régénération cellulaire',
                ],
                'weight' => 8,
            ],
            [
                'id' => 'gravity',
                'name' => 'Gravité',
                'icon' => '🌌',
                'tier' => 'mythic',
                'tier_label' => 'Mythique',
                'description' => 'Manipule la masse : écrase la vitesse ennemie et affaiblit ses coups.',
                'type' => 'cosmic',
                'effect' => [
                    'type' => 'gravity',
                    'speed_reduction_percent' => 45,
                    'damage_reduction_percent' => 15,
                    'label' => 'Pression gravitationnelle',
                ],
                'weight' => 4,
            ],
            [
                'id' => 'teleportation',
                'name' => 'Téléportation',
                'icon' => '🌀',
                'tier' => 'mythic',
                'tier_label' => 'Mythique',
                'description' => 'Disparaît dans une faille spatiale pour éviter les attaques reçues.',
                'type' => 'spatial',
                'effect' => [
                    'type' => 'dodge',
                    'dodge_chance' => 0.28,
                    'label' => 'Saut dimensionnel',
                ],
                'weight' => 4,
            ],
            [
                'id' => 'invisibility',
                'name' => 'Invisibilité',
                'icon' => '👁️‍🗨️',
                'tier' => 'mythic',
                'tier_label' => 'Mythique',
                'description' => 'Se dissimule aux yeux de l\'adversaire, garantissant l\'esquive du 1er coup et un retour puissant.',
                'type' => 'stealth',
                'effect' => [
                    'type' => 'stealth',
                    'first_turn_dodge' => true,
                    'dodge_chance' => 0.20,
                    'ambush_crit_multiplier' => 1.6,
                    'label' => 'Camouflage optique',
                ],
                'weight' => 4,
            ],
        ];
    }

    public static function find(string $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }
}
