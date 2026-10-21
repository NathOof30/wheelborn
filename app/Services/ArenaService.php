<?php

namespace App\Services;

use App\Data\GameConfig;
use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Enums\League;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Arène : choix des combattants, recherche d'un adversaire (vrai joueur ou Fantôme d'Arène),
 * enregistrement des duels (Élo, récompenses, palmarès) et vérification anti-triche des combats enregistrés.
 * Les règles du combat lui-même sont dans BattleEngineService.
 */
class ArenaService
{
    // Seules ces données influencent le combat : elles suffisent à le recalculer
    private const SNAPSHOT_FIELDS = ['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'luck'];

    public function __construct(
        private BattleEngineService $engine,
        private BotGeneratorService $bots,
        private EloService $elo,
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

    /**
     * Adversaire pour la carte choisie : d'abord un vrai joueur proche en Élo (carte de puissance proche),
     * sinon un Fantôme d'Arène généré à la volée. Le résultat est toujours un tableau au même format.
     */
    public function findOpponent(User $user, Character $fighter): array
    {
        $card = $this->elo->findOpponent($user, $fighter);

        return $card ? $this->playerOpponent($card) : $this->bots->generateGhost($user);
    }

    /** Lance un duel, puis enregistre le combat et la progression du joueur (Élo, pièces, XP, palmarès). */
    public function fight(User $user, Character $fighter, array $opponent): array
    {
        $outcome = $this->engine->simulate($fighter, $opponent);
        $won = $outcome['winner'] === 'challenger';
        $draw = $outcome['winner'] === BattleEngineService::DRAW;

        // Match nul : l'Élo ne bouge pas
        $oldElo = $user->elo;
        $newElo = $draw ? $oldElo : $this->elo->newRating($oldElo, $opponent['elo'], $won);

        // Changement de ligue : promotion si on monte, relégation si on descend
        $oldLeague = League::fromElo($oldElo);
        $newLeague = League::fromElo($newElo);
        $leagueChange = match (true) {
            $newLeague->isHigherThan($oldLeague) => 'promotion',
            $oldLeague->isHigherThan($newLeague) => 'relegation',
            default                              => null,
        };

        // Match nul : petit lot de consolation
        $rewards = GameConfig::rewards();
        [$coins, $xp] = match (true) {
            $draw   => [$rewards['draw_coins'], $rewards['draw_xp']],
            $won    => [$rewards['win_coins_base'], $rewards['win_xp']],
            default => [$rewards['loss_coins_base'], $rewards['loss_xp']],
        };

        DB::transaction(function () use ($user, $fighter, $opponent, $outcome, $won, $draw, $oldElo, $newElo, $coins, $xp) {
            Battle::create([
                'player_id'      => $user->id,
                'challenger_id'  => $fighter->id,
                'opponent_id'    => $opponent['kind'] === 'player' ? $opponent['id'] : null, // Fantôme : pas de carte en base
                'opponent_name'  => $opponent['name'],
                'opponent_elo'   => $opponent['elo'],
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
                    'opponent'   => $this->snapshot($opponent),
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
            'league_change' => $leagueChange
                ? ['type' => $leagueChange, 'label' => $newLeague->label(), 'icon' => $newLeague->icon(), 'color' => $newLeague->color()]
                : null,
            'coins'     => $coins,
            'xp'        => $xp,
            'max_hp'    => ['challenger' => $fighter->max_hp, 'opponent' => $opponent['max_hp']],
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

    /**
     * Carte d'un vrai joueur, mise au même format qu'un fantôme.
     * L'Élo affiché et utilisé pour le calcul est celui de son propriétaire (qui, lui, n'est pas modifié par le duel).
     */
    private function playerOpponent(Character $card): array
    {
        return $card->only(['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'power_score'])
            + [
                'rarity' => $card->rarity->value,
                'elo'    => $card->user->elo,
                'owner'  => $card->user->name,
                'kind'   => 'player',
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
