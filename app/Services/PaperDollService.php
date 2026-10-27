<?php

namespace App\Services;

class PaperDollService
{
    /**
     * Alias entre les identifiants en base de données et les noms de fichiers possibles.
     */
    protected static array $raceAliases = [
        'human' => ['human', 'humain'],
        'elf' => ['elf', 'elfe'],
        'demon' => ['demon'],
        'robot' => ['robot', 'cyborg'],
        'mutant' => ['mutant'],
        'golem' => ['golem'],
        'vampire' => ['vampire'],
        'alien' => ['alien'],
        'orc' => ['orc'],
        'undead' => ['undead', 'mort_vivant'],
    ];

    protected static array $weaponAliases = [
        'sword' => ['sword', 'epee'],
        'staff' => ['staff', 'baton'],
        'bow' => ['bow', 'arc'],
        'hammer' => ['hammer', 'marteau'],
        'spear' => ['spear', 'lance'],
        'dagger' => ['dagger', 'dague'],
        'gun' => ['gun', 'pistolet'],
        'grimoire' => ['grimoire', 'book'],
        'axe' => ['axe', 'hache'],
        'shield' => ['shield', 'bouclier'],
    ];

    protected static array $powerAliases = [
        'fire' => ['fire', 'feu'],
        'poison' => ['poison'],
        'ice' => ['ice', 'glace'],
        'lightning' => ['lightning', 'foudre'],
        'regeneration' => ['regeneration', 'regen'],
        'gravity' => ['gravity', 'gravite'],
        'teleportation' => ['teleportation', 'teleport'],
        'invisibility' => ['invisibility', 'invisibilite'],
        'water' => ['water', 'eau'],
        'earth' => ['earth', 'terre'],
        'wind' => ['wind', 'vent'],
        'dark' => ['dark', 'shadow', 'ombre'],
        'light' => ['light', 'lumiere'],
    ];

    /**
     * Résout le chemin relatif de l'asset pour la Race (Calque 1).
     */
    public static function getRaceAsset(?string $raceId): ?string
    {
        if (!$raceId)
            return null;
        $candidates = self::getNormalizedCandidates(strtolower($raceId), self::$raceAliases);
        return self::findFirstAsset('characters/races', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour l'Arme (Calque 3).
     */
    public static function getWeaponAsset(?string $weaponId): ?string
    {
        if (!$weaponId)
            return null;
        $candidates = self::getNormalizedCandidates(strtolower($weaponId), self::$weaponAliases);
        return self::findFirstAsset('characters/weapons', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour le Pouvoir / VFX Élémentaire (Calque 2).
     */
    public static function getPowerAsset(?string $powerId): ?string
    {
        if (!$powerId)
            return null;
        $candidates = self::getNormalizedCandidates(strtolower($powerId), self::$powerAliases);
        return self::findFirstAsset('characters/elements', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour le Fond / Aura de Rareté (Calque 0).
     */
    public static function getBackgroundAsset(?string $rarity): ?string
    {
        if (!$rarity)
            return null;
        $key = strtolower($rarity);
        return self::findFirstAsset('characters/backgrounds', [$key]);
    }

    /**
     * Résout le chemin relatif de l'asset pour l'Aura Élémentaire de l'Arme (Calque 4, z-index: 40).
     * Les auras sont organisées par arme et élément, ex:
     * public/images/characters/weapons/sword_auras/sword_fire.webp
     */
    public static function getAuraAsset(?string $weaponId, ?string $powerId): ?string
    {
        if (!$weaponId || !$powerId)
            return null;

        $weaponCandidates = self::getNormalizedCandidates(strtolower($weaponId), self::$weaponAliases);
        $powerCandidates = self::getNormalizedCandidates(strtolower($powerId), self::$powerAliases);

        $extensions = ['webp', 'png', 'svg', 'jpg'];

        foreach ($weaponCandidates as $w) {
            foreach ($powerCandidates as $p) {
                $dirs = [
                    "characters/weapons/{$w}_auras",
                ];

                $fileNames = [
                    "{$w}_{$p}",
                    "{$p}",
                ];

                foreach ($dirs as $dir) {
                    foreach ($fileNames as $fileName) {
                        foreach ($extensions as $ext) {
                            $relative = "images/{$dir}/{$fileName}.{$ext}";
                            if (file_exists(public_path($relative))) {
                                return $relative;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Résout de manière bidirectionnelle les alias (anglais <-> français <-> id).
     * Place toujours le nom canonique anglais en premier.
     */
    protected static function getNormalizedCandidates(string $id, array $aliasMap): array
    {
        $id = strtolower($id);
        $candidates = [];

        foreach ($aliasMap as $canonical => $aliases) {
            if ($id === $canonical || in_array($id, $aliases, true)) {
                $candidates[] = $canonical;
                foreach ($aliases as $alias) {
                    $candidates[] = $alias;
                }
                break;
            }
        }

        $candidates[] = $id;

        return array_values(array_unique($candidates));
    }

    /**
     * Détermine si un identifiant de pouvoir correspond à l'invisibilité (id canonique ou alias).
     */
    public static function isInvisibilityPower(?string $powerId): bool
    {
        if (!$powerId) {
            return false;
        }

        $candidates = self::getNormalizedCandidates('invisibility', self::$powerAliases);
        return in_array(strtolower($powerId), $candidates, true);
    }

    /**
     * Détermine si une aura correspond à l'aura d'invisibilité (via son chemin ou l'id du pouvoir).
     */
    public static function isInvisibilityAura(?string $powerId, ?string $auraPath = null): bool
    {
        if ($auraPath !== null) {
            $normalized = strtolower(str_replace('\\', '/', $auraPath));
            if (str_contains($normalized, 'invisibility') || str_contains($normalized, 'invisibilite')) {
                return true;
            }
        }

        return self::isInvisibilityPower($powerId);
    }

    /**
     * Vérifie si le personnage possède au moins un calque visuel disponible (ex: Race, Arme, Pouvoir ou Aura).
     */
    public static function hasVisualLayers(?string $raceId, ?string $weaponId = null, ?string $powerId = null): bool
    {
        return self::getRaceAsset($raceId) !== null
            || self::getWeaponAsset($weaponId) !== null
            || self::getPowerAsset($powerId) !== null
            || self::getAuraAsset($weaponId, $powerId) !== null;
    }

    /**
     * Retourne l'ensemble des calques résolus pour un personnage.
     * Pour toutes les armes, si l'aura d'invisibilité est active, le calque de base de l'arme
     * n'est pas affiché car l'aura reprend l'outline sans les couleurs.
     */
    public static function resolveLayers(?string $raceId, ?string $weaponId, ?string $powerId, ?string $rarity): array
    {
        $aura = self::getAuraAsset($weaponId, $powerId);
        $weapon = self::getWeaponAsset($weaponId);

        // Cas particulier de l'Invisibilité : L'aura d'invisibilité reprend l'outline
        // de l'arme sans ses couleurs. Pour toutes les armes, lorsque cette aura est active,
        // on n'affiche pas l'arme de base pour ne pas écraser l'effet translucide.
        if ($aura && self::isInvisibilityAura($powerId, $aura)) {
            $weapon = null;
        }

        return [
            'background' => self::getBackgroundAsset($rarity),
            'race' => self::getRaceAsset($raceId),
            'power' => self::getPowerAsset($powerId),
            'weapon' => $weapon,
            'aura' => $aura,
        ];
    }

    /**
     * Recherche le premier fichier correspondant sur le disque dans public/images/...
     */
    protected static function findFirstAsset(string $subDir, array $names): ?string
    {
        $extensions = ['webp', 'png', 'svg', 'jpg'];
        foreach ($names as $name) {
            foreach ($extensions as $ext) {
                $relative = "images/{$subDir}/{$name}.{$ext}";
                if (file_exists(public_path($relative))) {
                    return $relative;
                }
            }
        }
        return null;
    }
}
