<?php

namespace App\Services;

class PaperDollService
{
    /**
     * Alias entre les identifiants en base de données et les noms de fichiers possibles.
     */
    protected static array $raceAliases = [
        'human'       => ['human', 'humain'],
        'elf'         => ['elf', 'elfe'],
        'demon'       => ['demon'],
        'robot'       => ['robot', 'cyborg'],
        'mutant'      => ['mutant'],
        'golem'       => ['golem'],
        'vampire'     => ['vampire'],
        'alien'       => ['alien'],
        // Fallbacks au cas où
        'orc'         => ['orc'],
        'undead'      => ['undead', 'mort_vivant'],
    ];

    protected static array $weaponAliases = [
        'sword'    => ['epee', 'sword'],
        'staff'    => ['baton', 'staff'],
        'bow'      => ['arc', 'bow'],
        'hammer'   => ['marteau', 'hammer'],
        'spear'    => ['lance', 'spear'],
        'dagger'   => ['dague', 'dagger'],
        'gun'      => ['pistolet', 'gun'],
        'grimoire' => ['grimoire', 'book'],
    ];

    protected static array $powerAliases = [
        'fire'          => ['feu', 'fire'],
        'poison'        => ['poison'],
        'ice'           => ['glace', 'ice'],
        'lightning'     => ['foudre', 'lightning'],
        'regeneration'  => ['regeneration', 'regen'],
        'gravity'       => ['gravite', 'gravity'],
        'teleportation' => ['teleportation', 'teleport'],
        'invisibility'  => ['invisibilite', 'invisibility'],
        // Fallbacks rétrocompatibles
        'water'         => ['eau', 'water'],
        'earth'         => ['terre', 'earth'],
        'wind'          => ['vent', 'wind'],
        'dark'          => ['ombre', 'dark', 'shadow'],
        'light'         => ['lumiere', 'light'],
    ];

    /**
     * Résout le chemin relatif de l'asset pour la Race (Calque 1).
     */
    public static function getRaceAsset(?string $raceId): ?string
    {
        if (!$raceId) return null;
        $key = strtolower($raceId);
        $candidates = self::$raceAliases[$key] ?? [$key];
        return self::findFirstAsset('characters/races', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour l'Arme (Calque 3).
     */
    public static function getWeaponAsset(?string $weaponId): ?string
    {
        if (!$weaponId) return null;
        $key = strtolower($weaponId);
        $candidates = self::$weaponAliases[$key] ?? [$key];
        return self::findFirstAsset('characters/weapons', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour le Pouvoir / VFX Élémentaire (Calque 2).
     */
    public static function getPowerAsset(?string $powerId): ?string
    {
        if (!$powerId) return null;
        $key = strtolower($powerId);
        $candidates = self::$powerAliases[$key] ?? [$key];
        return self::findFirstAsset('characters/elements', $candidates);
    }

    /**
     * Résout le chemin relatif de l'asset pour le Fond / Aura de Rareté (Calque 0).
     */
    public static function getBackgroundAsset(?string $rarity): ?string
    {
        if (!$rarity) return null;
        $key = strtolower($rarity);
        return self::findFirstAsset('characters/backgrounds', [$key]);
    }

    /**
     * Vérifie si le personnage possède au moins un calque visuel disponible (ex: Race).
     */
    public static function hasVisualLayers(?string $raceId, ?string $weaponId = null, ?string $powerId = null): bool
    {
        return self::getRaceAsset($raceId) !== null
            || self::getWeaponAsset($weaponId) !== null
            || self::getPowerAsset($powerId) !== null;
    }

    /**
     * Retourne l'ensemble des calques résolus pour un personnage.
     */
    public static function resolveLayers(?string $raceId, ?string $weaponId, ?string $powerId, ?string $rarity): array
    {
        return [
            'background' => self::getBackgroundAsset($rarity),
            'race'       => self::getRaceAsset($raceId),
            'power'      => self::getPowerAsset($powerId),
            'weapon'     => self::getWeaponAsset($weaponId),
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
