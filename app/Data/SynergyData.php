<?php

namespace App\Data;

class SynergyData
{
    public static function all(): array
    {
        return [
            // 1. Démon + Feu
            [
                'id' => 'demon_fire',
                'name' => 'Affinité Démoniaque',
                'icon' => '🔥😈',
                'description' => '+18% Dégâts globaux et la Brûlure inflige 50% de dégâts supplémentaires.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'demon' && $p === 'fire',
                'effects' => ['damage_multiplier' => 1.18, 'burn_damage_boost' => 1.5],
            ],
            // 2. Elfe + Arc
            [
                'id' => 'elf_bow',
                'name' => 'Archer Naturel',
                'icon' => '🏹🧝',
                'description' => '+16 Vitesse et +20% de chances de coup critique.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'elf' && $w === 'bow',
                'effects' => ['speed_bonus' => 16, 'crit_chance_bonus' => 0.20],
            ],
            // 3. Robot + Foudre
            [
                'id' => 'robot_lightning',
                'name' => 'Supraconducteur',
                'icon' => '⚡🤖',
                'description' => '+15 Défense et immunité aux débuffs de foudre. Chaque coup électrique recharge 6 PV.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'robot' && $p === 'lightning',
                'effects' => ['defense_bonus' => 15, 'lightning_heal' => 6],
            ],
            // 4. Vampire + Régénération
            [
                'id' => 'vampire_regen',
                'name' => 'Sangsue Immortelle',
                'icon' => '💖🧛',
                'description' => 'Régénération passive doublée (+12 PV/tour) et vol de vie accru à 25%.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'vampire' && $p === 'regeneration',
                'effects' => ['regen_bonus' => 12, 'lifesteal_bonus' => 0.13],
            ],
            // 5. Golem + Marteau
            [
                'id' => 'golem_hammer',
                'name' => 'Impact Tellurique',
                'icon' => '🔨🗿',
                'description' => '+25 Force d\'écrasement et 40% de chance d\'étourdir l\'adversaire.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'golem' && $w === 'hammer',
                'effects' => ['strength_bonus' => 25, 'stun_chance_bonus' => 0.15],
            ],
            // 6. Grimoire + Magie élémentaire
            [
                'id' => 'grimoire_elemental',
                'name' => 'Incantation Arcanique',
                'icon' => '📖✨',
                'description' => '+25% sur tous les effets magiques et dégâts élémentaires.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $w === 'grimoire' && in_array($p, ['fire', 'ice', 'lightning', 'poison']),
                'effects' => ['spell_damage_multiplier' => 1.25, 'dot_duration_bonus' => 1],
            ],
            // 7. Humain + Épée
            [
                'id' => 'human_sword',
                'name' => 'Maître d\'Armes',
                'icon' => '⚔️👤',
                'description' => '+12 Force, +12 Défense et parade réduisant de 20% les dégâts reçus.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'human' && $w === 'sword',
                'effects' => ['strength_bonus' => 12, 'defense_bonus' => 12, 'damage_reduction' => 0.10],
            ],
            // 8. Alien + Téléportation
            [
                'id' => 'alien_teleport',
                'name' => 'Distorsion Spatiale',
                'icon' => '🌀👽',
                'description' => '+30% esquive globale. Réplique immédiatement avec une contre-attaque en cas d\'esquive.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $r === 'alien' && $p === 'teleportation',
                'effects' => ['dodge_bonus' => 0.30, 'counter_on_dodge' => true],
            ],
            // 9. Dague + Invisibilité
            [
                'id' => 'dagger_invis',
                'name' => 'Assassin Fantôme',
                'icon' => '🗡️👁️‍🗨️',
                'description' => 'Premier coup critique garanti à x2.2 de dégâts. Vitesse accrue de +12.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $w === 'dagger' && $p === 'invisibility',
                'effects' => ['speed_bonus' => 12, 'first_crit_multiplier' => 2.2],
            ],
            // 10. Bâton + Régénération
            [
                'id' => 'staff_regen',
                'name' => 'Harmonie Sylvestre',
                'icon' => '🪄💖',
                'description' => 'Confère un bouclier de 25 PV en début de combat et +8 PV soignés par tour.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $w === 'staff' && $p === 'regeneration',
                'effects' => ['shield_hp' => 25, 'regen_bonus' => 8],
            ],
            // 11. Pistolet + Foudre
            [
                'id' => 'gun_lightning',
                'name' => 'Tir Électro-Magnétique',
                'icon' => '🔫⚡',
                'description' => '40% de chance de critique électrique infligeant des secousses paralysantes.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $w === 'gun' && $p === 'lightning',
                'effects' => ['crit_chance_bonus' => 0.25, 'crit_multiplier' => 2.0],
            ],
            // 12. Lance + Gravité
            [
                'id' => 'spear_gravity',
                'name' => 'Pointe Gravitationnelle',
                'icon' => '🔱🌌',
                'description' => 'La lance cloue la cible au sol. L\'adversaire ne peut jamais esquiver et subit +15% dégâts.',
                'is_unstable' => false,
                'check' => fn ($r, $w, $p) => $w === 'spear' && $p === 'gravity',
                'effects' => ['pierce_dodge' => true, 'damage_multiplier' => 1.15],
            ],

            // COMBINAISONS INSTABLES
            // 13. Démon + Glace
            [
                'id' => 'demon_ice',
                'name' => 'Combustion Glacée',
                'icon' => '❄️😈💥',
                'description' => 'INSTABLE : Choc thermique imprévisible ! Dégâts oscillant entre 50% et 220%, inflige Brûlure ET Gel.',
                'is_unstable' => true,
                'check' => fn ($r, $w, $p) => $r === 'demon' && $p === 'ice',
                'effects' => ['unstable_volatility' => true, 'applies_both_burn_and_freeze' => true],
            ],
            // 14. Robot + Poison
            [
                'id' => 'robot_poison',
                'name' => 'Infection Artificielle',
                'icon' => '🧪🤖☣️',
                'description' => 'INSTABLE : Liquide de refroidissement toxique. Réduit la défense ennemie de 30%, mais subit 4 dégâts/tour.',
                'is_unstable' => true,
                'check' => fn ($r, $w, $p) => $r === 'robot' && $p === 'poison',
                'effects' => ['acid_poison' => true, 'self_corrosion_damage' => 4],
            ],
            // 15. Golem + Téléportation
            [
                'id' => 'golem_teleport',
                'name' => 'Masse Déplacée',
                'icon' => '🌀🗿☄️',
                'description' => 'INSTABLE : Colosse de 10 tonnes téléporté ! 20% esquive, puis s\'écrase avec 30 dégâts bonus massifs.',
                'is_unstable' => true,
                'check' => fn ($r, $w, $p) => $r === 'golem' && $p === 'teleportation',
                'effects' => ['heavy_crush_dodge_bonus' => 0.20, 'crush_bonus_damage' => 30],
            ],
            // 16. Mutant + Gravité
            [
                'id' => 'mutant_gravity',
                'name' => 'Singularité Vivante',
                'icon' => '🌌☣️🌀',
                'description' => 'INSTABLE : Anomalie gravitationnelle. Stats modifiées aléatoirement à chaque tour (-10 à +25 force/vitesse).',
                'is_unstable' => true,
                'check' => fn ($r, $w, $p) => $r === 'mutant' && $p === 'gravity',
                'effects' => ['chaos_shifting' => true],
            ],
        ];
    }

    public static function detect(string $raceId, string $weaponId, string $powerId): array
    {
        $active = [];

        foreach (self::all() as $synergy) {
            if (($synergy['check'])($raceId, $weaponId, $powerId)) {
                $copy = $synergy;
                unset($copy['check']);
                $active[] = $copy;
            }
        }

        return $active;
    }
}
