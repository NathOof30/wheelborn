<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;
use App\Enums\Rarity;
use App\Enums\PowerTier;
use App\Data\GameConfig;
use App\Data\RaceData;
use App\Data\WeaponData;
use App\Data\PowerData;
use App\Data\SynergyData;
use App\Data\NameGenerator;
use Illuminate\Support\Str;

class CharacterGeneratorService
{
    public function __construct(private WorkshopService $workshop)
    {
    }

    public function generate(User $user, bool $isLuckySpin = false): Character
    {
        $race   = $this->weightedChoice(RaceData::all());
        $weapon = $this->weightedChoice(WeaponData::all());
        $power  = $this->selectPower($user, $isLuckySpin);

        $synergies = SynergyData::detect($race['id'], $weapon['id'], $power['id']);
        $stats = $this->rollStats($race, $weapon, $synergies, $user);

        $isUnstable = collect($synergies)->contains('is_unstable', true);
        $powerScore = $this->calculatePowerScore($stats, $synergies, $isUnstable);
        $rarity = $this->determineRarity($isLuckySpin, $powerScore, count($synergies));

        $recycleValue = $this->calculateRecycleValue($powerScore, count($synergies), $rarity);

        return Character::create([
            'user_id'           => $user->id,
            'name'              => NameGenerator::generate($race['id']),
            'race_id'           => $race['id'],
            'weapon_id'         => $weapon['id'],
            'power_id'          => $power['id'],
            'power_tier'        => PowerTier::from($power['tier']),
            'hp'                => $stats['hp'],
            'max_hp'            => $stats['hp'],
            'strength'          => $stats['strength'],
            'speed'             => $stats['speed'],
            'defense'           => $stats['defense'],
            'power_score'       => $powerScore,
            'rarity'            => $rarity,
            'is_lucky_spin'     => $isLuckySpin,
            'is_unstable'       => $isUnstable,
            'synergies_data'    => $synergies,
            'recycle_value'     => $recycleValue,
            'is_active_fighter' => false,
            'pending_reveal'    => true,
        ]);
    }

    /**
     * Génère un combattant bot d'arène (non persisté), au format attendu par BattleEngineService.
     * $statBonus ajuste la difficulté en décalant Force, Vitesse, Défense et PV.
     */
    public function generateBot(int $statBonus = 0): array
    {
        $race   = $this->weightedChoice(RaceData::all());
        $weapon = $this->weightedChoice(WeaponData::all());
        $power  = $this->weightedChoice(PowerData::all());

        $synergies = SynergyData::detect($race['id'], $weapon['id'], $power['id']);
        $stats = $this->rollStats($race, $weapon, $synergies);

        $stats = [
            'hp'       => max(30, min(140, $stats['hp'] + $statBonus)),
            'strength' => max(15, min(100, $stats['strength'] + $statBonus)),
            'speed'    => max(10, min(100, $stats['speed'] + $statBonus)),
            'defense'  => max(10, min(100, $stats['defense'] + $statBonus)),
        ];

        $isUnstable = collect($synergies)->contains('is_unstable', true);
        $powerScore = $this->calculatePowerScore($stats, $synergies, $isUnstable);
        $rarity = $this->determineRarity(false, $powerScore, count($synergies));

        return [
            'id'             => (string) Str::uuid(),
            'name'           => NameGenerator::generate($race['id']),
            'race_id'        => $race['id'],
            'weapon_id'      => $weapon['id'],
            'power_id'       => $power['id'],
            'hp'             => $stats['hp'],
            'max_hp'         => $stats['hp'],
            'strength'       => $stats['strength'],
            'speed'          => $stats['speed'],
            'defense'        => $stats['defense'],
            'power_score'    => $powerScore,
            'rarity'         => $rarity->value,
            'is_unstable'    => $isUnstable,
            'synergies'      => array_column($synergies, 'name'),
            'synergies_data' => $synergies,
        ];
    }

    private function weightedChoice(array $items): array
    {
        $totalWeight = array_sum(array_column($items, 'weight'));
        $rand = mt_rand(1, $totalWeight);

        $current = 0;
        foreach ($items as $item) {
            $current += $item['weight'];
            if ($rand <= $current) {
                return $item;
            }
        }

        return $items[0];
    }

    private function selectPower(User $user, bool $isLuckySpin): array
    {
        return $this->weightedChoice($this->powerPool($user, $isLuckySpin));
    }

    /**
     * Pouvoirs que ce joueur peut tirer, avec leurs poids ajustés par l'Aimant à Pouvoirs Rares :
     * chaque niveau fait passer 3 points de pourcentage des pouvoirs communs (puis rares, au Super Spin
     * où les communs sont exclus) vers les pouvoirs mythiques. La répartition interne de chaque tier est conservée.
     * Les poids sont multipliés par 100 pour rester entiers (weightedChoice tire un entier).
     */
    public function powerPool(User $user, bool $isLuckySpin = false): array
    {
        $powers = PowerData::all();

        if ($isLuckySpin) {
            // Exclure les pouvoirs communs lors d'un super spin
            $powers = array_values(array_filter($powers, fn ($p) => $p['tier'] !== 'common'));
        }

        $tierTotals = [];
        foreach ($powers as $power) {
            $tierTotals[$power['tier']] = ($tierTotals[$power['tier']] ?? 0) + $power['weight'];
        }

        $bonus = $this->workshop->rareMagnetBonus($user);
        $toMove = empty($tierTotals['mythic']) ? 0 : array_sum($tierTotals) * $bonus / 100;

        // Facteur appliqué aux poids de chaque tier : on prélève sur les communs, puis sur les rares si besoin
        $scale = array_fill_keys(array_keys($tierTotals), 1.0);
        $moved = 0;
        foreach (['common', 'rare'] as $tier) {
            if (empty($tierTotals[$tier]) || $moved >= $toMove) {
                continue;
            }
            $taken = min($tierTotals[$tier], $toMove - $moved);
            $scale[$tier] = ($tierTotals[$tier] - $taken) / $tierTotals[$tier];
            $moved += $taken;
        }
        if ($moved > 0) {
            $scale['mythic'] = ($tierTotals['mythic'] + $moved) / $tierTotals['mythic'];
        }

        return array_map(
            fn ($power) => ['weight' => (int) round($power['weight'] * $scale[$power['tier']] * 100)] + $power,
            $powers,
        );
    }

    private function rollStats(array $race, array $weapon, array $synergies, ?User $user = null): array
    {
        $cfg = GameConfig::baseStats();

        $hp       = mt_rand($cfg['hp_min'], $cfg['hp_max']) + ($race['mods']['hp'] ?? 0) + ($weapon['mods']['hp'] ?? 0);
        $strength = mt_rand($cfg['strength_min'], $cfg['strength_max']) + ($race['mods']['strength'] ?? 0) + ($weapon['mods']['strength'] ?? 0);
        $speed    = mt_rand($cfg['speed_min'], $cfg['speed_max']) + ($race['mods']['speed'] ?? 0) + ($weapon['mods']['speed'] ?? 0);
        $defense  = mt_rand($cfg['defense_min'], $cfg['defense_max']) + ($race['mods']['defense'] ?? 0) + ($weapon['mods']['defense'] ?? 0);

        // Mutant chaotique
        if (!empty($race['is_mutant'])) {
            $hp += mt_rand(-10, 20);
            $strength += mt_rand(-10, 20);
            $speed += mt_rand(-12, 18);
            $defense += mt_rand(-10, 18);
        }

        // Forge des Héros : bonus permanent de PV et de Force sur chaque tirage du joueur (les bots n'en ont pas)
        if ($user) {
            $forge = $this->workshop->heroForgeBonus($user);
            $hp += $forge['hp'];
            $strength += $forge['strength'];
        }

        // Bonus passifs des synergies
        foreach ($synergies as $syn) {
            if (!empty($syn['effects']['strength_bonus'])) $strength += $syn['effects']['strength_bonus'];
            if (!empty($syn['effects']['defense_bonus']))  $defense += $syn['effects']['defense_bonus'];
            if (!empty($syn['effects']['speed_bonus']))    $speed += $syn['effects']['speed_bonus'];
            if (!empty($syn['effects']['regen_bonus']))    $hp += (int) round($syn['effects']['regen_bonus'] * 0.5);
        }

        return [
            'hp'       => max(30, min(140, (int) round($hp))),
            'strength' => max(15, min(100, (int) round($strength))),
            'speed'    => max(10, min(100, (int) round($speed))),
            'defense'  => max(10, min(100, (int) round($defense))),
        ];
    }

    private function calculatePowerScore(array $stats, array $synergies, bool $isUnstable): int
    {
        $w = GameConfig::powerWeights();
        $raw = ($stats['hp'] * $w['hp'])
             + ($stats['strength'] * $w['strength'])
             + ($stats['defense'] * $w['defense'])
             + ($stats['speed'] * $w['speed'])
             + (count($synergies) * $w['synergy_bonus'])
             + ($isUnstable ? $w['unstable_bonus'] : 0);

        return max(20, min(100, (int) round($raw)));
    }

    private function determineRarity(bool $isLuckySpin, int $powerScore, int $synergiesCount): Rarity
    {
        $weights = GameConfig::rarityWeights($isLuckySpin);
        $pool = [
            ['rarity' => Rarity::Common,    'weight' => $weights['common']],
            ['rarity' => Rarity::Rare,      'weight' => $weights['rare']],
            ['rarity' => Rarity::Epic,      'weight' => $weights['epic']],
            ['rarity' => Rarity::Legendary, 'weight' => $weights['legendary']],
        ];

        $totalWeight = array_sum(array_column($pool, 'weight'));
        $rand = mt_rand(1, $totalWeight);

        $current = 0;
        $rolled = Rarity::Common;
        foreach ($pool as $p) {
            $current += $p['weight'];
            if ($rand <= $current) {
                $rolled = $p['rarity'];
                break;
            }
        }

        // Auto-promotion si combinaison exceptionnelle
        if ($powerScore >= 86 && $synergiesCount >= 2 && $rolled === Rarity::Common) {
            $rolled = Rarity::Rare;
        }
        if ($powerScore >= 95 && $synergiesCount >= 2 && $rolled === Rarity::Rare) {
            $rolled = Rarity::Epic;
        }

        return $rolled;
    }

    private function calculateRecycleValue(int $powerScore, int $synergyCount, Rarity $rarity): int
    {
        $raw = ($powerScore * 1.2) + ($synergyCount * 25);
        return max(35, (int) round($raw * $rarity->recycleMultiplier()));
    }
}
