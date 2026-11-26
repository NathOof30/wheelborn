<?php

declare(strict_types=1);

namespace App\View\Presenters;

use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Services\PaperDollService;

final readonly class FolioCardPresenter
{
    /** @var array<string, array{pigment_hex: string, pigment_soft: string}> */
    public const POWER_PIGMENTS = [
        'fire' => [
            'pigment_hex' => '#8A1A14',
            'pigment_soft' => 'rgba(138, 26, 20, 0.16)',
        ],
        'poison' => [
            'pigment_hex' => '#3E5438',
            'pigment_soft' => 'rgba(62, 84, 56, 0.16)',
        ],
        'ice' => [
            'pigment_hex' => '#2A3A7A',
            'pigment_soft' => 'rgba(42, 58, 122, 0.18)',
        ],
        'lightning' => [
            'pigment_hex' => '#75500A',
            'pigment_soft' => 'rgba(154, 111, 24, 0.22)',
        ],
        'regeneration' => [
            'pigment_hex' => '#456037',
            'pigment_soft' => 'rgba(69, 96, 55, 0.16)',
        ],
        'gravity' => [
            'pigment_hex' => '#2F2A45',
            'pigment_soft' => 'rgba(47, 42, 69, 0.18)',
        ],
        'teleportation' => [
            'pigment_hex' => '#54274A',
            'pigment_soft' => 'rgba(84, 39, 74, 0.18)',
        ],
        'invisibility' => [
            'pigment_hex' => '#5C5349',
            'pigment_soft' => 'rgba(92, 83, 73, 0.16)',
        ],
    ];

    /** @var array<string, array{seal_hex: string, seal_name: string}> */
    public const RARITY_SEALS = [
        'common' => [
            'seal_hex' => '#6B4A2A',
            'seal_name' => 'Argile',
        ],
        'rare' => [
            'seal_hex' => '#2A3A7A',
            'seal_name' => 'Outremer',
        ],
        'epic' => [
            'seal_hex' => '#54274A',
            'seal_name' => 'Pourpre',
        ],
        'legendary' => [
            'seal_hex' => '#74520A',
            'seal_name' => 'Or',
        ],
    ];

    /** @var array<string, string> */
    public const LEAGUE_INK_COLORS = [
        'bronze' => '#8A5226',
        'silver' => '#536270',
        'gold' => '#75500A',
        'platinum' => '#1F5C7A',
        'master' => '#5C2B6E',
    ];

    /**
     * @return array{pigment_hex: string, pigment_soft: string}
     */
    public static function powerPigment(string $powerId): array
    {
        return self::POWER_PIGMENTS[$powerId] ?? [
            'pigment_hex' => '#6B4A2A',
            'pigment_soft' => 'rgba(107, 74, 42, 0.16)',
        ];
    }

    /**
     * @return array{seal_hex: string, seal_name: string}
     */
    public static function raritySeal(string $rarity): array
    {
        return self::RARITY_SEALS[$rarity] ?? self::RARITY_SEALS['common'];
    }

    public static function leagueInkColor(string $leagueValue): string
    {
        return self::LEAGUE_INK_COLORS[$leagueValue] ?? '#536270';
    }

    /**
     * @param  Character|array<string, mixed>  $source
     * @return array<string, mixed>
     */
    public function present(Character|array $source): array
    {
        if (is_array($source) && isset($source['abilities'], $source['layers'], $source['seal_hex'], $source['power_pigment_hex'])) {
            return $source;
        }

        $data = $this->extractRawAttributes($source);

        $raceId = (string) ($data['race_id'] ?? 'human');
        $weaponId = (string) ($data['weapon_id'] ?? 'sword');
        $powerId = (string) ($data['power_id'] ?? 'fire');

        $race = RaceData::find($raceId);
        $weapon = WeaponData::find($weaponId);
        $power = PowerData::find($powerId);

        $rarityValue = $this->resolveRarityValue($data['rarity'] ?? $data['rarity_key'] ?? 'common');
        $rarityEnum = Rarity::tryFrom($rarityValue) ?? Rarity::Common;
        $seal = self::raritySeal($rarityEnum->value);
        $pigment = self::powerPigment($powerId);

        $raceName = (string) ($race['name'] ?? ucfirst($raceId));
        $raceInitial = mb_strtoupper(mb_substr($raceName, 0, 1));
        $racePassive = (string) ($race['passive'] ?? '');
        $racePassiveDesc = (string) ($race['passive_desc'] ?? '');

        $weaponName = (string) ($weapon['name'] ?? ucfirst($weaponId));
        $weaponPassive = (string) ($weapon['passive'] ?? '');
        $weaponPassiveDesc = (string) ($weapon['passive_desc'] ?? '');

        $powerName = (string) ($power['name'] ?? ucfirst($powerId));
        $rawPowerTier = $data['power_tier'] ?? ($power['tier'] ?? 'common');
        $powerTier = $rawPowerTier instanceof PowerTier ? $rawPowerTier->value : (string) $rawPowerTier;
        $powerTierLabel = (string) ($power['tier_label'] ?? match ($powerTier) {
            'mythic' => 'Mythique',
            'rare' => 'Rare',
            default => 'Commun',
        });
        $powerEffectLabel = (string) ($power['effect']['label'] ?? $powerName);
        $powerDescription = (string) ($power['description'] ?? '');

        $synergies = $this->resolveSynergies($data, $raceId, $weaponId, $powerId);
        $isUnstable = (bool) ($data['is_unstable'] ?? collect($synergies)->contains('is_unstable', true));

        $hp = (int) ($data['max_hp'] ?? $data['hp'] ?? 0);
        $strength = (int) ($data['strength'] ?? 0);
        $speed = (int) ($data['speed'] ?? 0);
        $defense = (int) ($data['defense'] ?? 0);
        $powerScore = (int) ($data['power_score'] ?? 0);

        $recycleValue = isset($data['recycle_value'])
            ? (int) $data['recycle_value']
            : max(35, (int) round((($powerScore * 1.2) + (count($synergies) * 25)) * $rarityEnum->recycleMultiplier()));

        $wins = (int) ($data['wins'] ?? 0);
        $losses = (int) ($data['losses'] ?? 0);
        $battles = $wins + $losses;
        $winrate = $battles > 0 ? (int) round(($wins / $battles) * 100) : null;

        $resolved = PaperDollService::resolveLayers($raceId, $weaponId, $powerId, $rarityEnum->value);
        $hasPortrait = ! empty($resolved['race']);

        $layers = $hasPortrait
            ? [
                'race' => asset((string) $resolved['race']),
                'weapon' => $resolved['weapon'] !== null ? asset((string) $resolved['weapon']) : null,
                'aura' => $resolved['aura'] !== null ? asset((string) $resolved['aura']) : null,
                'is_invisibility' => $resolved['aura'] !== null && PaperDollService::isInvisibilityAura($powerId, $resolved['aura']),
                'is_pale_aura' => $resolved['aura'] !== null && in_array($powerId, ['ice', 'lightning'], true),
                'is_teleportation_aura' => $resolved['aura'] !== null && $powerId === 'teleportation',
            ]
            : [
                'race' => null,
                'weapon' => null,
                'aura' => null,
                'is_invisibility' => false,
                'is_pale_aura' => false,
                'is_teleportation_aura' => false,
            ];

        $abilities = $this->buildAbilitiesList(
            raceId: $raceId,
            raceName: $raceName,
            racePassive: $racePassive,
            racePassiveDesc: $racePassiveDesc,
            weaponId: $weaponId,
            weaponName: $weaponName,
            weaponPassive: $weaponPassive,
            weaponPassiveDesc: $weaponPassiveDesc,
            powerId: $powerId,
            powerName: $powerName,
            powerTier: $powerTier,
            powerTierLabel: $powerTierLabel,
            powerEffectLabel: $powerEffectLabel,
            powerDescription: $powerDescription,
            powerPigmentHex: $pigment['pigment_hex'],
            synergies: $synergies,
        );

        $name = (string) ($data['name'] ?? 'Combattant');
        $id = (string) ($data['id'] ?? md5($name.'|'.$raceId.'|'.$weaponId.'|'.$powerId.'|'.$powerScore));

        return [
            'id' => $id,
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
            'race_id' => $raceId,
            'race_name' => $raceName,
            'race_initial' => $raceInitial,
            'race_passive' => $racePassive,
            'race_passive_desc' => $racePassiveDesc,
            'weapon_id' => $weaponId,
            'weapon_name' => $weaponName,
            'weapon_passive' => $weaponPassive,
            'weapon_passive_desc' => $weaponPassiveDesc,
            'power_id' => $powerId,
            'power_name' => $powerName,
            'power_tier' => $powerTier,
            'power_tier_label' => $powerTierLabel,
            'power_effect_label' => $powerEffectLabel,
            'power_description' => $powerDescription,
            'power_pigment_hex' => $pigment['pigment_hex'],
            'power_pigment_soft' => $pigment['pigment_soft'],
            'rarity' => $rarityEnum->value,
            'rarity_label' => $rarityEnum->label(),
            'seal_hex' => $seal['seal_hex'],
            'seal_name' => $seal['seal_name'],
            'hp' => $hp,
            'max_hp' => $hp,
            'strength' => $strength,
            'speed' => $speed,
            'defense' => $defense,
            'power_score' => $powerScore,
            'recycle_value' => $recycleValue,
            'is_locked' => (bool) ($data['is_locked'] ?? false),
            'is_favorite' => (bool) ($data['is_favorite'] ?? false),
            'is_lucky_spin' => (bool) ($data['is_lucky_spin'] ?? false),
            'is_active_fighter' => (bool) ($data['is_active_fighter'] ?? false),
            'is_unstable' => $isUnstable,
            'wins' => $wins,
            'losses' => $losses,
            'winrate' => $winrate,
            'synergies' => $synergies,
            'abilities' => $abilities,
            'has_portrait' => $hasPortrait,
            'layers' => $layers,
        ];
    }

    /**
     * @param  Character|array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function extractRawAttributes(Character|array $source): array
    {
        if ($source instanceof Character) {
            return $source->attributesToArray();
        }

        $base = [];
        if (isset($source['character'])) {
            $inner = $source['character'];
            if ($inner instanceof Character) {
                $base = $inner->attributesToArray();
            } elseif (is_array($inner)) {
                $base = $inner;
            }
        }

        $merged = array_merge($base, $source);

        if (empty($merged['race_id']) && isset($source['race']['id'])) {
            $merged['race_id'] = $source['race']['id'];
        }
        if (empty($merged['weapon_id']) && isset($source['weapon']['id'])) {
            $merged['weapon_id'] = $source['weapon']['id'];
        }
        if (empty($merged['power_id']) && isset($source['power']['id'])) {
            $merged['power_id'] = $source['power']['id'];
        }
        if (empty($merged['rarity']) && ! empty($source['rarity_key'])) {
            $merged['rarity'] = $source['rarity_key'];
        }

        return $merged;
    }

    private function resolveRarityValue(mixed $raw): string
    {
        if ($raw instanceof Rarity) {
            return $raw->value;
        }

        return strtolower((string) $raw);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function resolveSynergies(array $data, string $raceId, string $weaponId, string $powerId): array
    {
        $raw = $data['synergies_data'] ?? $data['synergies'] ?? null;

        if (is_array($raw) && count($raw) > 0 && is_array(reset($raw))) {
            return array_values($raw);
        }

        return SynergyData::detect($raceId, $weaponId, $powerId);
    }

    /**
     * @param  list<array<string, mixed>>  $synergies
     * @return list<array<string, mixed>>
     */
    private function buildAbilitiesList(
        string $raceId,
        string $raceName,
        string $racePassive,
        string $racePassiveDesc,
        string $weaponId,
        string $weaponName,
        string $weaponPassive,
        string $weaponPassiveDesc,
        string $powerId,
        string $powerName,
        string $powerTier,
        string $powerTierLabel,
        string $powerEffectLabel,
        string $powerDescription,
        string $powerPigmentHex,
        array $synergies,
    ): array {
        $abilities = [];

        if ($racePassive !== '') {
            $abilities[] = [
                'kind' => 'race',
                'category' => 'races',
                'icon_id' => $raceId,
                'label' => $racePassive,
                'meta' => 'Race',
                'tooltip_eyebrow' => 'RACE · '.mb_strtoupper($raceName),
                'tooltip_title' => $racePassive,
                'tooltip_desc' => $racePassiveDesc,
                'tooltip_tone' => 'default',
                'accent_hex' => null,
                'is_mythic' => false,
                'is_unstable' => false,
            ];
        }

        if ($weaponPassive !== '') {
            $abilities[] = [
                'kind' => 'weapon',
                'category' => 'weapons',
                'icon_id' => $weaponId,
                'label' => $weaponPassive,
                'meta' => 'Arme',
                'tooltip_eyebrow' => 'ARME · '.mb_strtoupper($weaponName),
                'tooltip_title' => $weaponPassive,
                'tooltip_desc' => $weaponPassiveDesc,
                'tooltip_tone' => 'default',
                'accent_hex' => null,
                'is_mythic' => false,
                'is_unstable' => false,
            ];
        }

        if ($powerEffectLabel !== '') {
            $abilities[] = [
                'kind' => 'power',
                'category' => 'powers',
                'icon_id' => $powerId,
                'label' => $powerEffectLabel,
                'meta' => $powerTier === 'mythic' ? 'Mythique' : 'Pouvoir',
                'tooltip_eyebrow' => 'POUVOIR · '.mb_strtoupper($powerTierLabel),
                'tooltip_title' => $powerName.' — '.$powerEffectLabel,
                'tooltip_desc' => $powerDescription,
                'tooltip_tone' => 'power',
                'accent_hex' => $powerPigmentHex,
                'is_mythic' => $powerTier === 'mythic',
                'is_unstable' => false,
            ];
        }

        foreach ($synergies as $syn) {
            $unstable = ! empty($syn['is_unstable']);
            $abilities[] = [
                'kind' => 'synergy',
                'category' => null,
                'icon_id' => null,
                'label' => (string) ($syn['name'] ?? ''),
                'meta' => $unstable ? 'Instable' : 'Synergie',
                'tooltip_eyebrow' => $unstable ? 'SYNERGIE · INSTABLE' : 'SYNERGIE · ACCORD',
                'tooltip_title' => (string) ($syn['name'] ?? ''),
                'tooltip_desc' => (string) ($syn['description'] ?? ''),
                'tooltip_tone' => $unstable ? 'unstable' : 'synergy',
                'accent_hex' => $unstable ? '#8A1A14' : '#74520A',
                'is_mythic' => false,
                'is_unstable' => $unstable,
            ];
        }

        return $abilities;
    }
}
