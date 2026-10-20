<?php

namespace App\Services;

use App\Data\GameConfig;
use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Moteur de combat tour par tour entre deux combattants, en PHP pur.
 * Déterministe : deux combattants + une même seed produisent toujours exactement le même combat.
 */
class BattleEngineService
{
    public const MAX_TURNS = 25;

    private const MIN_DAMAGE = 5;
    private const BASE_CRIT_MULTIPLIER = 1.5;
    private const MAX_CRIT_CHANCE = 0.75;
    private const MAX_DODGE_CHANCE = 0.60;

    // Stat Chance : dérivée de la vitesse si le combattant n'en fournit pas
    private const LUCK_MIN = 5;
    private const LUCK_MAX = 25;
    private const LUCK_CRIT_PER_POINT = 0.004;
    private const LUCK_DODGE_PER_POINT = 0.003;

    private const ALIEN_INNATE_DODGE = 0.08;
    private const VAMPIRE_LIFESTEAL = 0.12;
    private const ICE_SLOW_TURNS = 2;
    private const CHAOS_SHIFT_MIN = -10;
    private const CHAOS_SHIFT_MAX = 20;

    // Arène contre les bots
    private const ELO_K_FACTOR = 32;
    private const BOT_ELO_SPREAD = 100;
    private const BOT_ELO_PER_STAT_POINT = 40;
    private const BOT_STAT_BONUS_MIN = -10;
    private const BOT_STAT_BONUS_MAX = 15;

    // Seules ces données influencent le combat : elles suffisent à le recalculer
    private const SNAPSHOT_FIELDS = ['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'luck'];

    private Randomizer $rng;
    private array $fighters = [];
    private array $log = [];
    private int $turn = 0;

    public function __construct(private CharacterGeneratorService $generator = new CharacterGeneratorService())
    {
    }

    // ─── Arène : combats contre les bots ─────────────────────────────────

    /** Combattants du joueur pouvant entrer dans l'arène. */
    public function availableFighters(User $user): Collection
    {
        return Character::where('user_id', $user->id)
            ->where('pending_reveal', false)
            ->whereNull('recycled_at')
            ->orderByDesc('is_active_fighter')
            ->orderByDesc('power_score')
            ->get();
    }

    /** Génère un bot dont la difficulté et l'Élo sont indexés sur ceux du joueur. */
    public function generateBotOpponent(User $user): array
    {
        $statBonus = $this->clamp(intdiv($user->elo - 1000, self::BOT_ELO_PER_STAT_POINT), self::BOT_STAT_BONUS_MIN, self::BOT_STAT_BONUS_MAX);

        return $this->generator->generateBot($statBonus)
            + ['elo' => max(0, $user->elo + random_int(-self::BOT_ELO_SPREAD, self::BOT_ELO_SPREAD))];
    }

    /** Lance un duel contre un bot, puis enregistre le combat et la progression (Élo, pièces, XP, palmarès). */
    public function fightBot(User $user, Character $fighter, array $bot): array
    {
        $outcome = $this->simulate($fighter, $bot);
        $won = $outcome['winner'] === 'challenger';

        $oldElo = $user->elo;
        $newElo = max(0, $oldElo + $this->eloDelta($oldElo, $bot['elo'], $won));

        $rewards = GameConfig::rewards();
        $coins = $rewards[$won ? 'win_coins_base' : 'loss_coins_base'];
        $xp = $rewards[$won ? 'win_xp' : 'loss_xp'];

        DB::transaction(function () use ($user, $fighter, $bot, $outcome, $won, $oldElo, $newElo, $coins, $xp) {
            Battle::create([
                'player_id'      => $user->id,
                'challenger_id'  => $fighter->id,
                'opponent_name'  => $bot['name'],
                'opponent_elo'   => $bot['elo'],
                'winner_id'      => $outcome['winner_id'],
                'seed'           => $outcome['seed'],
                'turns_count'    => $outcome['turns'],
                'player_old_elo' => $oldElo,
                'player_new_elo' => $newElo,
                'elo_delta'      => $newElo - $oldElo,
                'coins_awarded'  => $coins,
                'xp_awarded'     => $xp,
                'turns_log'      => $outcome['log'],
                'fighters_snapshot' => [
                    'challenger' => $this->snapshot($fighter),
                    'opponent'   => $this->snapshot($bot),
                ],
            ]);

            $fighter->increment('battles_count');
            $fighter->increment($won ? 'wins' : 'losses');

            $user->update(['elo' => $newElo, 'coins' => $user->coins + $coins, 'xp' => $user->xp + $xp]);
        });

        return [
            'won'       => $won,
            'turns'     => $outcome['turns'],
            'reason'    => $outcome['reason'],
            'elo_delta' => $newElo - $oldElo,
            'coins'     => $coins,
            'xp'        => $xp,
            'max_hp'    => ['challenger' => $fighter->max_hp, 'opponent' => $bot['max_hp']],
            'log'       => $outcome['log'],
        ];
    }

    /** Recalcule un combat enregistré à partir de sa seed et des stats figées des combattants. */
    public function replay(Battle $battle): array
    {
        return $this->simulate(
            $battle->fighters_snapshot['challenger'],
            $battle->fighters_snapshot['opponent'],
            $battle->seed,
        );
    }

    /** Anti-triche : vrai si le recalcul redonne exactement le journal et le vainqueur enregistrés. */
    public function isAuthentic(Battle $battle): bool
    {
        if (empty($battle->fighters_snapshot) || empty($battle->turns_log)) {
            return false;
        }

        $replay = $this->replay($battle);

        return $replay['winner_id'] === $battle->winner_id && $replay['log'] === $battle->turns_log;
    }

    /** Élo FIDE : K × (S − E). */
    public function eloDelta(int $playerElo, int $opponentElo, bool $won): int
    {
        $expected = 1 / (1 + 10 ** (($opponentElo - $playerElo) / 400));

        return (int) round(self::ELO_K_FACTOR * ((int) $won - $expected));
    }

    /** Fiche d'affichage d'un combattant (personnage du joueur ou bot). */
    public function describeFighter(Character|array $source): array
    {
        $data = $this->attributes($source);

        return [
            'character'   => $source,
            'name'        => $data['name'],
            'elo'         => $data['elo'] ?? null,
            'race'        => RaceData::find($data['race_id']),
            'weapon'      => WeaponData::find($data['weapon_id']),
            'power'       => PowerData::find($data['power_id']),
            'hp'          => (int) ($data['max_hp'] ?? $data['hp']),
            'strength'    => (int) $data['strength'],
            'speed'       => (int) $data['speed'],
            'defense'     => (int) $data['defense'],
            'power_score' => (int) $data['power_score'],
            'synergies'   => array_column(SynergyData::detect($data['race_id'], $data['weapon_id'], $data['power_id']), 'name'),
        ];
    }

    // ─── Simulation ──────────────────────────────────────────────────────

    /**
     * Simule un duel complet.
     *
     * Sans seed, une seed est tirée puis renvoyée pour pouvoir rejouer le combat.
     *
     * @return array{seed:int, winner:string, winner_id:mixed, turns:int, reason:string, fighters:array, log:array}
     */
    public function simulate(Character|array $challenger, Character|array $opponent, ?int $seed = null): array
    {
        $seed ??= random_int(0, 2_147_483_647);
        // Mersenne Twister seedé (même algorithme que mt_srand), mais local : n'altère pas mt_rand() ailleurs
        $this->rng = new Randomizer(new Mt19937($seed));
        $this->log = [];
        $this->turn = 0;
        $this->fighters = [
            'challenger' => $this->buildFighter($challenger, 'challenger'),
            'opponent'   => $this->buildFighter($opponent, 'opponent'),
        ];

        foreach ($this->fighters as $key => $fighter) {
            if ($fighter['shield'] > 0) {
                $this->log('shield', $key, null, $fighter['shield'], "{$fighter['name']} s'entoure d'un bouclier arcanique de {$fighter['shield']} PV.");
            }
        }

        $winner = null;
        while ($winner === null && $this->turn < self::MAX_TURNS) {
            $this->turn++;
            $winner = $this->playTurn();
        }

        // Sécurité anti-boucle infinie : décision aux PV restants
        $reason = $winner ? 'ko' : 'turn_limit';
        $winner ??= $this->decideOnPoints();

        $winnerName = $this->fighters[$winner]['name'];
        $loser = $this->other($winner);
        $this->log('end', $winner, $loser, null, $reason === 'ko'
            ? "{$this->fighters[$loser]['name']} est vaincu ! {$winnerName} remporte le duel."
            : 'Limite de ' . self::MAX_TURNS . " tours atteinte : {$winnerName} l'emporte aux points.");

        return [
            'seed'      => $seed,
            'winner'    => $winner,
            'winner_id' => $this->fighters[$winner]['id'],
            'turns'     => $this->turn,
            'reason'    => $reason,
            'fighters'  => array_map(fn (array $f) => array_intersect_key($f, array_flip(['id', 'name', 'hp', 'max_hp', 'luck'])), $this->fighters),
            'log'       => $this->log,
        ];
    }

    /** Joue un tour complet ; retourne le vainqueur si l'un des deux tombe. */
    private function playTurn(): ?string
    {
        // Début de tour : mutations, soins et dégâts sur la durée
        $this->startOfTurn('challenger');
        $this->startOfTurn('opponent');
        if ($winner = $this->resolveKo()) {
            return $winner;
        }

        // Phase 1 : initiative, puis Phase 2 : échange de coups
        [$first, $second] = $this->initiative();

        $this->act($first, $second);
        if ($winner = $this->resolveKo()) {
            return $winner;
        }

        $this->act($second, $first);

        return $this->resolveKo();
    }

    private function buildFighter(Character|array $source, string $key): array
    {
        $data = $this->attributes($source);
        $weapon = WeaponData::find($data['weapon_id']) ?? [];
        $power = PowerData::find($data['power_id']) ?? [];

        // Les bonus de stats des synergies sont déjà intégrés à la génération :
        // seuls les effets de combat sont exploités ici.
        $effects = $this->mergeSynergyEffects(SynergyData::detect($data['race_id'], $data['weapon_id'], $data['power_id']));

        $maxHp = (int) ($data['max_hp'] ?? $data['hp']);
        $speed = (int) $data['speed'];

        return [
            'id'            => $data['id'] ?? null,
            'name'          => $data['name'] ?? ucfirst($key),
            'race_id'       => $data['race_id'],
            'power_id'      => $data['power_id'],
            'weapon'        => $weapon,
            'power_effect'  => $power['effect'] ?? [],
            'effects'       => $effects,
            'max_hp'        => $maxHp,
            'hp'            => $maxHp,
            'shield'        => (int) round(($effects['shield_hp'] ?? 0) * ($weapon['magic_empower'] ?? 1.0)),
            'strength'      => (int) $data['strength'],
            'speed'         => $speed,
            'defense'       => (int) $data['defense'],
            'base_strength' => (int) $data['strength'],
            'base_speed'    => $speed,
            'luck'          => (int) ($data['luck'] ?? $this->clamp((int) round(self::LUCK_MIN + $speed * 0.2), self::LUCK_MIN, self::LUCK_MAX)),
            'attacks_made'  => 0,
            'stealth_ready' => !empty($power['effect']['first_turn_dodge']),
            'pending_crush' => false,
            'stunned'       => false,
            'frozen'        => false,
            'slowed_until'  => 0,
            'slow_percent'  => 0,
            'burn'          => null,
            'poison'        => null,
            'bleed'         => null,
        ];
    }

    /**
     * Fusionne les effets de plusieurs synergies actives :
     * booléens en OU, multiplicateurs de dégâts multipliés, multiplicateurs critiques au max, le reste additionné.
     */
    private function mergeSynergyEffects(array $synergies): array
    {
        $merged = [];

        foreach ($synergies as $synergy) {
            foreach ($synergy['effects'] as $name => $value) {
                $merged[$name] = match (true) {
                    !isset($merged[$name]) => $value,
                    is_bool($value)        => $merged[$name] || $value,
                    in_array($name, ['damage_multiplier', 'spell_damage_multiplier', 'burn_damage_boost']) => $merged[$name] * $value,
                    in_array($name, ['crit_multiplier', 'first_crit_multiplier']) => max($merged[$name], $value),
                    default                => $merged[$name] + $value,
                };
            }
        }

        return $merged;
    }

    // ─── Déroulement d'un tour ───────────────────────────────────────────

    private function startOfTurn(string $key): void
    {
        $self = &$this->fighters[$key];

        // Singularité Vivante : force et vitesse mutent à chaque tour
        if (!empty($self['effects']['chaos_shifting'])) {
            $self['strength'] = $this->clamp($self['base_strength'] + $this->rng->getInt(self::CHAOS_SHIFT_MIN, self::CHAOS_SHIFT_MAX), 15, 100);
            $self['speed'] = $this->clamp($self['base_speed'] + $this->rng->getInt(self::CHAOS_SHIFT_MIN, self::CHAOS_SHIFT_MAX), 10, 100);
            $this->log('mutation', $key, null, null, "{$self['name']} mute : Force {$self['strength']}, Vitesse {$self['speed']}.");
        }

        // Régénération (amplifiée par le Bâton et les synergies)
        if (($self['power_effect']['type'] ?? null) === 'heal') {
            $amount = ($self['power_effect']['heal_per_turn'] + ($self['effects']['regen_bonus'] ?? 0)) * ($self['weapon']['magic_empower'] ?? 1.0);
            if ($healed = $this->heal($key, (int) round($amount))) {
                $this->log('regen', $key, null, $healed, "{$self['name']} régénère {$healed} PV.");
            }
        }

        // Infection Artificielle : corrosion interne
        if (!empty($self['effects']['self_corrosion_damage'])) {
            $lost = $this->applyDamage($key, $self['effects']['self_corrosion_damage']);
            $this->log('corrosion', $key, $key, $lost, "{$self['name']} subit {$lost} dégâts de corrosion interne.");
        }

        // Dégâts sur la durée
        foreach (['burn' => 'brûlure', 'bleed' => 'saignement', 'poison' => 'poison'] as $status => $label) {
            if ($self[$status] === null || $self['hp'] <= 0) {
                continue;
            }

            $lost = $this->applyDamage($key, $self[$status]['damage']);
            $this->log($status, $self[$status]['source'], $key, $lost, "{$self['name']} subit {$lost} dégâts de {$label}.");

            $self[$status]['damage'] += $self[$status]['ramp'];
            if (--$self[$status]['turns'] <= 0) {
                $self[$status] = null;
            }
        }
    }

    /** @return array{0:string, 1:string} */
    private function initiative(): array
    {
        $challengerSpeed = $this->effectiveSpeed('challenger');
        $opponentSpeed = $this->effectiveSpeed('opponent');
        $tie = $challengerSpeed === $opponentSpeed;

        $first = match (true) {
            $tie                             => $this->coinFlip(),
            $challengerSpeed > $opponentSpeed => 'challenger',
            default                          => 'opponent',
        };

        $this->log('initiative', $first, $this->other($first), null, "Tour {$this->turn} — {$this->fighters[$first]['name']} prend l'initiative ({$challengerSpeed} vs {$opponentSpeed})" . ($tie ? ' (égalité, tirage au sort)' : '') . '.');

        return [$first, $this->other($first)];
    }

    private function act(string $attackerKey, string $defenderKey): void
    {
        $attacker = &$this->fighters[$attackerKey];

        if ($attacker['stunned'] || $attacker['frozen']) {
            $state = $attacker['frozen'] ? 'gelé' : 'étourdi';
            $attacker['stunned'] = $attacker['frozen'] = false;
            $this->log('skip', $attackerKey, null, null, "{$attacker['name']} est {$state} et passe son tour.");
            return;
        }

        $this->attack($attackerKey, $defenderKey);
    }

    private function attack(string $attackerKey, string $defenderKey, bool $isCounter = false): void
    {
        $attacker = &$this->fighters[$attackerKey];
        $defender = &$this->fighters[$defenderKey];

        $isFirstAttack = $attacker['attacks_made']++ === 0;

        if ($this->tryDodge($attackerKey, $defenderKey)) {
            // Masse Déplacée : le golem réapparaît et écrase sa cible au coup suivant
            if (!empty($defender['effects']['crush_bonus_damage'])) {
                $defender['pending_crush'] = true;
            }
            // Distorsion Spatiale : contre-attaque immédiate (jamais en chaîne)
            if (!$isCounter && !empty($defender['effects']['counter_on_dodge'])) {
                $this->log('counter', $defenderKey, $attackerKey, null, "{$defender['name']} surgit de la faille et contre-attaque !");
                $this->attack($defenderKey, $attackerKey, true);
            }
            return;
        }

        // Dégâts bruts atténués par la défense ennemie
        $defense = $defender['defense'] * (!empty($attacker['effects']['acid_poison']) ? 0.7 : 1.0);
        $damage = max(self::MIN_DAMAGE, 0.75 * $attacker['strength'] - 0.38 * $defense + $this->rng->getInt(-3, 3));

        $multiplier = $attacker['effects']['damage_multiplier'] ?? 1.0;
        $tags = [];

        if ($isFirstAttack && !empty($attacker['weapon']['first_strike_bonus'])) {
            $multiplier *= 1 + $attacker['weapon']['first_strike_bonus'];
            $tags[] = 'estoc fulgurant';
        }
        if (!empty($attacker['weapon']['speed_advantage_bonus']) && $this->effectiveSpeed($attackerKey) > $this->effectiveSpeed($defenderKey)) {
            $multiplier *= 1 + $attacker['weapon']['speed_advantage_bonus'];
            $tags[] = 'opportunisme';
        }
        if (($defender['power_effect']['type'] ?? null) === 'gravity') {
            $multiplier *= 1 - $defender['power_effect']['damage_reduction_percent'] / 100;
        }
        if (!empty($defender['effects']['damage_reduction'])) {
            $multiplier *= 1 - $defender['effects']['damage_reduction'];
            $tags[] = 'parade';
        }
        if (!empty($attacker['effects']['unstable_volatility'])) {
            $multiplier *= $this->rng->getFloat(0.5, 2.2);
            $tags[] = 'choc thermique';
        }

        [$isCrit, $critMultiplier] = $this->rollCrit($attackerKey, $isFirstAttack);

        // Supraconducteur : les critiques électriques adverses rechargent au lieu de nuire
        if ($isCrit && $attacker['power_id'] === 'lightning' && !empty($defender['effects']['lightning_heal'])) {
            $isCrit = false;
            $healed = $this->heal($defenderKey, $defender['effects']['lightning_heal']);
            $this->log('absorb', $defenderKey, $attackerKey, $healed, "{$defender['name']} absorbe la décharge électrique (+{$healed} PV).");
        }
        if ($isCrit) {
            $multiplier *= $critMultiplier;
        }

        $damage = (int) round($damage * $multiplier);

        if ($attacker['pending_crush']) {
            $attacker['pending_crush'] = false;
            $damage += $attacker['effects']['crush_bonus_damage'];
            $tags[] = 'écrasement';
        }

        $lost = $this->applyDamage($defenderKey, $damage);
        $suffix = $tags ? ' [' . implode(', ', $tags) . ']' : '';
        $this->log($isCrit ? 'crit' : 'hit', $attackerKey, $defenderKey, $damage, $isCrit
            ? "COUP CRITIQUE ! {$attacker['name']} inflige {$damage} dégâts à {$defender['name']} (x{$critMultiplier}){$suffix}."
            : "{$attacker['name']} inflige {$damage} dégâts à {$defender['name']}{$suffix}.");

        // Vol de vie (Vampire, Sangsue Immortelle)
        $lifesteal = ($attacker['race_id'] === 'vampire' ? self::VAMPIRE_LIFESTEAL : 0) + ($attacker['effects']['lifesteal_bonus'] ?? 0);
        if ($lifesteal > 0 && $healed = $this->heal($attackerKey, (int) round($lost * $lifesteal))) {
            $this->log('lifesteal', $attackerKey, $defenderKey, $healed, "{$attacker['name']} draine {$healed} PV.");
        }

        if ($defender['hp'] > 0) {
            $this->applyOnHitEffects($attackerKey, $defenderKey);
        }
    }

    private function tryDodge(string $attackerKey, string $defenderKey): bool
    {
        $attacker = $this->fighters[$attackerKey];
        $defender = &$this->fighters[$defenderKey];

        // Pointe Gravitationnelle : la cible est clouée au sol
        if (!empty($attacker['effects']['pierce_dodge'])) {
            return false;
        }

        // Invisibilité : le tout premier coup reçu est toujours esquivé
        if ($defender['stealth_ready']) {
            $defender['stealth_ready'] = false;
            $this->log('dodge', $defenderKey, $attackerKey, null, "{$defender['name']} est invisible : le coup de {$attacker['name']} frappe le vide.");
            return true;
        }

        $chance = $defender['luck'] * self::LUCK_DODGE_PER_POINT
            + ($defender['race_id'] === 'alien' ? self::ALIEN_INNATE_DODGE : 0)
            + ($defender['power_effect']['dodge_chance'] ?? 0)
            + ($defender['effects']['dodge_bonus'] ?? 0)
            + ($defender['effects']['heavy_crush_dodge_bonus'] ?? 0);

        if (!$this->chance(min(self::MAX_DODGE_CHANCE, $chance))) {
            return false;
        }

        $this->log('dodge', $defenderKey, $attackerKey, null, "{$defender['name']} esquive l'attaque de {$attacker['name']} !");
        return true;
    }

    /** @return array{0:bool, 1:float} */
    private function rollCrit(string $attackerKey, bool $isFirstAttack): array
    {
        $attacker = $this->fighters[$attackerKey];

        // Assassin Fantôme puis Invisibilité : premier coup critique garanti
        $guaranteed = $attacker['effects']['first_crit_multiplier'] ?? $attacker['power_effect']['ambush_crit_multiplier'] ?? null;
        if ($isFirstAttack && $guaranteed) {
            return [true, (float) $guaranteed];
        }

        $chance = $attacker['luck'] * self::LUCK_CRIT_PER_POINT
            + ($attacker['weapon']['crit_chance'] ?? 0)
            + ($attacker['power_effect']['crit_chance'] ?? 0)
            + ($attacker['effects']['crit_chance_bonus'] ?? 0);

        $multiplier = max(
            self::BASE_CRIT_MULTIPLIER,
            $attacker['weapon']['crit_multiplier'] ?? 0,
            $attacker['power_effect']['crit_multiplier'] ?? 0,
            $attacker['effects']['crit_multiplier'] ?? 0,
        );

        return [$this->chance(min(self::MAX_CRIT_CHANCE, $chance)), (float) $multiplier];
    }

    /** Effets secondaires d'un coup porté : contrôles et dégâts sur la durée. */
    private function applyOnHitEffects(string $attackerKey, string $defenderKey): void
    {
        $attacker = $this->fighters[$attackerKey];
        $defender = &$this->fighters[$defenderKey];
        $weapon = $attacker['weapon'];
        $effect = $attacker['power_effect'];
        $type = $effect['type'] ?? null;
        $elementalBoost = (1 + ($weapon['elemental_boost'] ?? 0)) * ($attacker['effects']['spell_damage_multiplier'] ?? 1.0);
        $dotBonusTurns = (int) ($attacker['effects']['dot_duration_bonus'] ?? 0);

        // Coup contondant (Marteau, Impact Tellurique)
        if ($this->chance(($weapon['stun_chance'] ?? 0) + ($attacker['effects']['stun_chance_bonus'] ?? 0))) {
            $defender['stunned'] = true;
            $this->log('stun', $attackerKey, $defenderKey, null, "Coup contondant ! {$defender['name']} est étourdi.");
        }

        // Saignement : effet d'arme piloté par les données (bleed_chance / bleed_damage / bleed_duration)
        if ($this->chance($weapon['bleed_chance'] ?? 0)) {
            $this->inflictDot($attackerKey, $defenderKey, 'bleed', (int) $weapon['bleed_damage'], (int) $weapon['bleed_duration'], 0, "{$defender['name']} se met à saigner.");
        }

        // Brûlure (Feu, ou Combustion Glacée)
        if ($type === 'burn' || !empty($attacker['effects']['applies_both_burn_and_freeze'])) {
            $burn = $type === 'burn' ? $effect : PowerData::find('fire')['effect'];
            $damage = (int) round($burn['damage_per_turn'] * $elementalBoost * ($attacker['effects']['burn_damage_boost'] ?? 1.0));
            $this->inflictDot($attackerKey, $defenderKey, 'burn', $damage, $burn['duration'] + $dotBonusTurns, 0, "{$defender['name']} s'embrase ({$damage}/tour).");
        }

        // Poison progressif (rafraîchi, non cumulable)
        if ($type === 'poison') {
            $damage = (int) round($effect['base_damage'] * $elementalBoost);
            $ramp = (int) round($effect['ramping'] * $elementalBoost);
            $this->inflictDot($attackerKey, $defenderKey, 'poison', $damage, $effect['duration'] + $dotBonusTurns, $ramp, "{$defender['name']} est empoisonné.");
        }

        // Glace : ralentissement + chance de gel total
        if ($type === 'slow') {
            $defender['slow_percent'] = $effect['speed_reduction_percent'];
            $defender['slowed_until'] = $this->turn + self::ICE_SLOW_TURNS;

            if ($this->chance($effect['freeze_chance'] * ($weapon['magic_empower'] ?? 1.0) * $elementalBoost)) {
                $defender['frozen'] = true;
                $this->log('freeze', $attackerKey, $defenderKey, null, "{$defender['name']} est gelé sur place !");
            }
        }
    }

    // ─── Utilitaires ─────────────────────────────────────────────────────

    /** Pose (ou rafraîchit) un effet de dégâts sur la durée : brûlure, poison ou saignement. */
    private function inflictDot(string $attackerKey, string $defenderKey, string $status, int $damage, int $turns, int $ramp, string $message): void
    {
        $this->fighters[$defenderKey][$status] = ['damage' => $damage, 'ramp' => $ramp, 'turns' => $turns, 'source' => $attackerKey];
        $this->log("{$status}_applied", $attackerKey, $defenderKey, $damage, $message);
    }

    private function effectiveSpeed(string $key): int
    {
        $self = $this->fighters[$key];
        $enemy = $this->fighters[$this->other($key)];
        $speed = $self['speed'];

        if ($this->turn <= $self['slowed_until']) {
            $speed *= 1 - $self['slow_percent'] / 100;
        }
        // Gravité adverse : écrasement permanent de la vitesse
        if (($enemy['power_effect']['type'] ?? null) === 'gravity') {
            $speed *= 1 - $enemy['power_effect']['speed_reduction_percent'] / 100;
        }

        return (int) round($speed);
    }

    /** Le bouclier absorbe en premier ; retourne les dégâts réellement encaissés. */
    private function applyDamage(string $key, int $amount): int
    {
        $self = &$this->fighters[$key];

        $absorbed = min($self['shield'], $amount);
        $self['shield'] -= $absorbed;

        $lost = min($self['hp'], $amount - $absorbed);
        $self['hp'] -= $lost;

        return $absorbed + $lost;
    }

    private function heal(string $key, int $amount): int
    {
        $self = &$this->fighters[$key];
        $healed = max(0, min($amount, $self['max_hp'] - $self['hp']));
        $self['hp'] += $healed;

        return $healed;
    }

    private function resolveKo(): ?string
    {
        $challengerDown = $this->fighters['challenger']['hp'] <= 0;
        $opponentDown = $this->fighters['opponent']['hp'] <= 0;

        return match (true) {
            $challengerDown && $opponentDown => $this->coinFlip(),
            $challengerDown                  => 'opponent',
            $opponentDown                    => 'challenger',
            default                          => null,
        };
    }

    private function decideOnPoints(): string
    {
        $challengerRatio = $this->fighters['challenger']['hp'] / $this->fighters['challenger']['max_hp'];
        $opponentRatio = $this->fighters['opponent']['hp'] / $this->fighters['opponent']['max_hp'];

        return match (true) {
            $challengerRatio === $opponentRatio => $this->coinFlip(),
            $challengerRatio > $opponentRatio   => 'challenger',
            default                             => 'opponent',
        };
    }

    /** Tirage seedé : vrai avec la probabilité donnée. */
    private function chance(float $probability): bool
    {
        return $probability > 0 && $this->rng->getFloat(0, 1) < $probability;
    }

    private function coinFlip(): string
    {
        return $this->rng->getInt(0, 1) === 0 ? 'challenger' : 'opponent';
    }

    private function other(string $key): string
    {
        return $key === 'challenger' ? 'opponent' : 'challenger';
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    /** Stats figées d'un combattant au moment du duel. */
    private function snapshot(Character|array $source): array
    {
        return array_intersect_key($this->attributes($source), array_flip(self::SNAPSHOT_FIELDS));
    }

    private function attributes(Character|array $source): array
    {
        return $source instanceof Character ? $source->attributesToArray() : $source;
    }

    private function log(string $type, ?string $actor, ?string $target, ?int $value, string $message): void
    {
        $this->log[] = [
            'turn'    => $this->turn,
            'type'    => $type,
            'actor'   => $actor,
            'target'  => $target,
            'value'   => $value,
            'message' => $message,
            'hp'      => [
                'challenger' => $this->fighters['challenger']['hp'],
                'opponent'   => $this->fighters['opponent']['hp'],
            ],
        ];
    }
}
