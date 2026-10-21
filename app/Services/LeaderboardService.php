<?php

namespace App\Services;

use App\Enums\League;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Classement de l'arène par Élo, filtrable par ligue.
 * Les comptes administrateurs ne sont pas classés (Élo et ressources de test).
 */
class LeaderboardService
{
    // Durée de mise en cache du classement, pour ne pas recalculer à chaque affichage
    public const CACHE_SECONDS = 60;

    // Nombre de joueurs affichés
    public const LIMIT = 50;

    /**
     * Meilleurs joueurs, tous ou d'une seule ligue, du plus haut au plus bas Élo.
     * Le résultat est mis en cache 60 secondes par onglet.
     *
     * @return array<int, array{id:int, name:string, elo:int, rank:int, battles:int, league:array}>
     */
    public function top(?League $league = null): array
    {
        return Cache::remember(
            'leaderboard:' . ($league?->value ?? 'all'),
            self::CACHE_SECONDS,
            fn () => $this->buildRanking($league),
        );
    }

    /** Rang, Élo et ligue du joueur connecté (calculé à chaque fois : il doit refléter son dernier duel). */
    public function playerStanding(User $user): ?array
    {
        if ($user->isAdmin()) {
            return null;
        }

        return [
            'name'   => $user->name,
            'elo'    => $user->elo,
            'rank'   => $this->globalRankForElo($user->elo),
            'league' => $this->leagueData($user->getLeague()),
        ];
    }

    /** Données d'affichage d'une ligue (nom, icône, couleur). */
    public function leagueData(League $league): array
    {
        return ['value' => $league->value, 'label' => $league->label(), 'icon' => $league->icon(), 'color' => $league->color()];
    }

    /** Liste classée (2 requêtes au total : la liste, puis le rang du premier joueur). */
    private function buildRanking(?League $league): array
    {
        $playersQuery = $this->rankablePlayers()
            ->withCount('battles')
            ->orderByDesc('elo')
            ->orderBy('id')
            ->limit(self::LIMIT);

        if ($league) {
            $playersQuery->where('elo', '>=', $league->minElo());
            if ($league->maxElo() !== null) {
                $playersQuery->where('elo', '<=', $league->maxElo());
            }
        }

        $players = $playersQuery->get(['id', 'name', 'elo']);
        if ($players->isEmpty()) {
            return [];
        }

        // Un seul calcul de rang en base, pour le premier joueur. La liste étant triée et sans trou,
        // le rang d'un joueur vaut ce premier rang + sa position, sauf à Élo égal où il garde le rang précédent.
        $firstRank = $this->globalRankForElo($players->first()->elo);
        $ranking = [];

        foreach ($players->values() as $position => $player) {
            $previousEntry = end($ranking);
            $sharesPreviousRank = $previousEntry && $previousEntry['elo'] === $player->elo;

            $ranking[] = [
                'id'      => $player->id,
                'name'    => $player->name,
                'elo'     => $player->elo,
                'rank'    => $sharesPreviousRank ? $previousEntry['rank'] : $firstRank + $position,
                'battles' => $player->battles_count,
                'league'  => $this->leagueData($player->getLeague()),
            ];
        }

        return $ranking;
    }

    /**
     * Rang au classement général : 1 + nombre de joueurs ayant strictement plus d'Élo.
     * Deux joueurs à Élo égal partagent donc le même rang (1, 2, 2, 4…).
     */
    private function globalRankForElo(int $elo): int
    {
        return $this->rankablePlayers()->where('elo', '>', $elo)->count() + 1;
    }

    /** Joueurs pris en compte dans le classement (les admins en sont exclus). */
    private function rankablePlayers(): Builder
    {
        return User::query()->where('is_admin', false);
    }
}
