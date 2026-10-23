<?php

namespace App\Services;

use App\Data\GameConfig;
use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Enums\League;
use App\Events\ArenaBattleFinished;
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
    // Données qui influencent le combat (elles suffisent à le recalculer), plus power_score pour l'afficher en replay
    private const SNAPSHOT_FIELDS = ['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'luck', 'power_score'];

    public function __construct(
        private BattleEngineService $engine,
        private BotGeneratorService $bots,
        private EloService $elo,
        private WorkshopService $workshop,
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
     * Adversaire pour la carte choisie :
     * 1. Construit un pool hybride d'au moins 10 candidats (vrais joueurs proches en Élo + Fantômes d'Arène).
     * 2. Applique un filtre anti-répétition (cooldown des derniers adversaires affrontés pour éviter le farm en boucle).
     * 3. Exclut l'adversaire actuel si un reroll est demandé.
     * 4. Sélectionne le meilleur adversaire éligible en favorisant les vrais joueurs non récents.
     */
    public function findOpponent(User $user, Character $fighter, string|int|null $excludeOpponentId = null): array
    {
        $config = GameConfig::matchmaking();
        $minPoolSize = $config['min_pool_size'] ?? 10;
        $recentLimit = $config['recent_cooldown'] ?? 3;

        // 1. Récupération des cartes de vrais joueurs dans la fenêtre d'Élo
        $realCards = $this->elo->findOpponentCandidates($user, $fighter, $minPoolSize);

        // 2. Récupération des IDs d'adversaires récemment affrontés (anti-farm)
        $recentOpponentIds = Battle::where('player_id', $user->id)
            ->latest('id')
            ->take($recentLimit)
            ->pluck('opponent_id')
            ->filter()
            ->all();

        // 3. Conversion des cartes de vrais joueurs
        $pool = [];
        foreach ($realCards as $card) {
            $data = $this->playerOpponent($card);
            $data['is_recent'] = in_array($card->id, $recentOpponentIds);
            $pool[] = $data;
        }

        // 4. Compléter le pool avec des Fantômes d'Arène jusqu'à au moins minPoolSize (10)
        $missingCount = max(0, $minPoolSize - count($pool));
        if ($missingCount > 0) {
            $ghosts = $this->bots->generateGhostPool($user, $missingCount, $fighter);
            foreach ($ghosts as $ghost) {
                $ghost['is_recent'] = false;
                $pool[] = $ghost;
            }
        }

        // 5. Exclure l'adversaire actuellement affiché si reroll
        $available = array_filter($pool, function ($cand) use ($excludeOpponentId) {
            if ($excludeOpponentId === null) {
                return true;
            }
            $candId = (string) ($cand['id'] ?? $cand['name']);
            return $candId !== (string) $excludeOpponentId;
        });

        if (empty($available)) {
            $available = $pool;
        }

        // 6. Choix de l'adversaire :
        // Pour la proposition initiale (hors reroll), on privilégie en priorité un vrai joueur non récent
        if ($excludeOpponentId === null) {
            $freshReal = array_values(array_filter($available, fn ($c) => $c['kind'] === 'player' && !$c['is_recent']));
            if (!empty($freshReal)) {
                return $freshReal[0];
            }
        }

        // Lors d'un reroll (ou si aucun vrai joueur frais n'est dispo) :
        // On tire au sort parmi l'ensemble des candidats disponibles non récents (vrais joueurs + bots)
        // pour garantir une diversité totale et éviter le ping-pong entre 2 cartes
        $freshOthers = array_values(array_filter($available, fn ($c) => !$c['is_recent']));
        if (!empty($freshOthers)) {
            return $freshOthers[array_rand($freshOthers)];
        }

        // Fallback ultime si tous les candidats sont marqués récents
        $availableValues = array_values($available);
        return $availableValues[array_rand($availableValues)];
    }

    /** Lance un duel, puis enregistre le combat et la progression du joueur (Élo, pièces, XP, palmarès). */
    public function fight(User $user, Character $fighter, array $opponent): array
    {
        $outcome = $this->engine->simulate($fighter, $opponent);
        $won = $outcome['winner'] === 'challenger';
        $draw = $outcome['winner'] === BattleEngineService::DRAW;

        // Match nul : l'Élo ne bouge pas
        $oldElo = $user->elo;
        $challengerPower = (int) $fighter->power_score;
        $opponentPower = (int) ($opponent['power_score'] ?? 50);
        $newElo = $draw ? $oldElo : $this->elo->newRating($oldElo, $opponent['elo'], $won, $challengerPower, $opponentPower);

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

        // Coffre de Guerre (Atelier) : multiplie l'or des victoires uniquement
        if ($won) {
            $coins = (int) round($coins * $this->workshop->arenaCoinMultiplier($user));
        }

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

            $user->update([
                'elo' => $newElo,
                'coins' => $user->coins + $coins,
                'xp' => $user->xp + $xp,
                'has_free_arena_reroll' => true,
            ]);
        });

        // Succès liés à l'arène (« Premier Sang »)
        ArenaBattleFinished::dispatch($user, $won, $draw);

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
            'power_score'    => (int) ($data['power_score'] ?? 0), // Absent des combats enregistrés avant le replay
            'rarity_key'     => $rarityKey,
            'rarity_label'   => $rarityLabel,
            'synergies'      => array_column($synergies, 'name'),
            'synergies_data' => $synergies,
            'wins'           => $data['wins'] ?? 0,
            'losses'         => $data['losses'] ?? 0,
            'is_locked'      => (bool) ($data['is_locked'] ?? false),
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
