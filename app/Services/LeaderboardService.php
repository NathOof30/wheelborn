<?php

namespace App\Services;

use App\Enums\League;
use App\Models\User;
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
            fn () => $this->ranked($league),
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
            'rank'   => $this->rankOf($user->elo),
            'league' => $this->leagueData($user->getLeague()),
        ];
    }

    /** Données d'affichage d'une ligue (nom, icône, couleur). */
    public function leagueData(League $league): array
    {
        return ['value' => $league->value, 'label' => $league->label(), 'icon' => $league->icon(), 'color' => $league->color()];
    }

    private function ranked(?League $league): array
    {
        $query = $this->rankedPlayers()
            ->withCount('battles')
            ->orderByDesc('elo')
            ->orderBy('id')
            ->limit(self::LIMIT);

        if ($league) {
            $query->where('elo', '>=', $league->minElo());
            if ($league->maxElo() !== null) {
                $query->where('elo', '<=', $league->maxElo());
            }
        }

        $players = $query->get(['id', 'name', 'elo']);
        if ($players->isEmpty()) {
            return [];
        }

        // Un seul calcul en base pour le premier joueur. La liste étant triée et sans trou,
        // le rang suivant vaut ce rang + la position, sauf à Élo égal où l'on garde le rang précédent.
        $firstRank = $this->rankOf($players->first()->elo);
        $rank = $firstRank;
        $previousElo = $players->first()->elo;

        return $players->values()->map(function (User $user, int $index) use ($firstRank, &$rank, &$previousElo) {
            if ($user->elo !== $previousElo) {
                $rank = $firstRank + $index;
            }
            $previousElo = $user->elo;

            return [
                'id'      => $user->id,
                'name'    => $user->name,
                'elo'     => $user->elo,
                'rank'    => $rank,
                'battles' => $user->battles_count,
                'league'  => $this->leagueData($user->getLeague()),
            ];
        })->all();
    }

    /**
     * Rang au classement général : 1 + nombre de joueurs ayant strictement plus d'Élo.
     * Deux joueurs à Élo égal partagent donc le même rang (1, 2, 2, 4…).
     */
    private function rankOf(int $elo): int
    {
        return $this->rankedPlayers()->where('elo', '>', $elo)->count() + 1;
    }

    private function rankedPlayers()
    {
        return User::query()->where('is_admin', false);
    }
}
