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

/**
 * Arène contre les bots : choix des combattants, génération des bots, enregistrement des duels
 * (Élo, récompenses, palmarès) et vérification anti-triche des combats enregistrés.
 * Les règles du combat lui-même sont dans BattleEngineService.
 */
class ArenaService
{
    private const ELO_K_FACTOR = 32;
    private const BOT_ELO_SPREAD = 100;
    private const BOT_ELO_PER_STAT_POINT = 40;
    private const BOT_STAT_BONUS_MIN = -10;
    private const BOT_STAT_BONUS_MAX = 15;

    // Seules ces données influencent le combat : elles suffisent à le recalculer
    private const SNAPSHOT_FIELDS = ['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'luck'];

    public function __construct(
        private BattleEngineService $engine,
        private CharacterGeneratorService $generator,
    ) {
    }

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
        $statBonus = max(self::BOT_STAT_BONUS_MIN, min(self::BOT_STAT_BONUS_MAX, intdiv($user->elo - 1000, self::BOT_ELO_PER_STAT_POINT)));

        return $this->generator->generateBot($statBonus)
            + ['elo' => max(0, $user->elo + random_int(-self::BOT_ELO_SPREAD, self::BOT_ELO_SPREAD))];
    }

    /** Lance un duel contre un bot, puis enregistre le combat et la progression (Élo, pièces, XP, palmarès). */
    public function fightBot(User $user, Character $fighter, array $bot): array
    {
        $outcome = $this->engine->simulate($fighter, $bot);
        $won = $outcome['winner'] === 'challenger';
        $draw = $outcome['winner'] === BattleEngineService::DRAW;

        // Match nul : l'Élo ne bouge pas
        $oldElo = $user->elo;
        $newElo = $draw ? $oldElo : max(0, $oldElo + $this->eloDelta($oldElo, $bot['elo'], $won));

        // Match nul : petit lot de consolation
        $rewards = GameConfig::rewards();
        [$coins, $xp] = match (true) {
            $draw   => [$rewards['draw_coins'], $rewards['draw_xp']],
            $won    => [$rewards['win_coins_base'], $rewards['win_xp']],
            default => [$rewards['loss_coins_base'], $rewards['loss_xp']],
        };

        DB::transaction(function () use ($user, $fighter, $bot, $outcome, $won, $draw, $oldElo, $newElo, $coins, $xp) {
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

            // Un match nul compte comme un combat joué, sans victoire ni défaite
            $fighter->increment('battles_count');
            if (!$draw) {
                $fighter->increment($won ? 'wins' : 'losses');
            }

            $user->update(['elo' => $newElo, 'coins' => $user->coins + $coins, 'xp' => $user->xp + $xp]);
        });

        return [
            'won'       => $won,
            'draw'      => $draw,
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
        return $this->engine->simulate(
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
        $synergies = SynergyData::detect($data['race_id'], $data['weapon_id'], $data['power_id']);

        $rarityVal = $data['rarity'] ?? 'common';
        if ($rarityVal instanceof \App\Enums\Rarity) {
            $rarityKey = $rarityVal->value;
            $rarityLabel = $rarityVal->label();
        } else {
            $rarityKey = (string) $rarityVal;
            $rarityLabel = match($rarityKey) {
                'legendary' => 'Légendaire',
                'epic'      => 'Épique',
                'rare'      => 'Rare',
                default     => 'Commune',
            };
        }

        return [
            'character'      => $source,
            'name'           => $data['name'],
            'elo'            => $data['elo'] ?? null,
            'race'           => RaceData::find($data['race_id']),
            'weapon'         => WeaponData::find($data['weapon_id']),
            'power'          => PowerData::find($data['power_id']),
            'hp'             => (int) ($data['max_hp'] ?? $data['hp']),
            'strength'       => (int) $data['strength'],
            'speed'          => (int) $data['speed'],
            'defense'        => (int) $data['defense'],
            'power_score'    => (int) $data['power_score'],
            'rarity_key'     => $rarityKey,
            'rarity_label'   => $rarityLabel,
            'synergies'      => array_column($synergies, 'name'),
            'synergies_data' => $synergies,
            'wins'           => $data['wins'] ?? 0,
            'losses'         => $data['losses'] ?? 0,
            'is_locked'      => (bool) ($data['is_locked'] ?? false),
        ];
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
}
