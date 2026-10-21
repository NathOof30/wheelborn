<?php

namespace App\Services;

use App\Models\Character;
use App\Models\User;

/**
 * Classement Élo selon la formule officielle des échecs (FIDE), et matchmaking par proximité d'Élo.
 *
 * Exemples (K = 32) :
 * - 1000 bat 1000 : +16 ; 1000 perd contre 1000 : -16
 * - 1000 bat 1150 : +23 (exploit récompensé) ; 1000 perd contre 1150 : -9 (défaite prévisible)
 */
class EloService
{
    // Volatilité : gain ou perte maximum possible sur un seul duel
    public const K_FACTOR = 32;

    // L'Élo ne peut jamais devenir négatif
    public const MIN_ELO = 0;

    // Matchmaking : fenêtres d'Élo essayées l'une après l'autre (±100 en priorité, jusqu'à ±250)
    public const MATCHMAKING_WINDOWS = [100, 150, 200, 250];

    /**
     * Trouve un adversaire réel pour la carte choisie par le joueur :
     * 1. un autre joueur dont l'Élo est proche (±100 en priorité, élargi jusqu'à ±250 si personne) ;
     * 2. dans sa collection, la carte dont la puissance (power_score) est la plus proche de celle de l'attaquant.
     * Retourne null si aucun adversaire n'est trouvé, même à ±250 (l'arène peut alors proposer un bot).
     */
    public function findOpponent(User $user, Character $attackerCard): ?Character
    {
        foreach (self::MATCHMAKING_WINDOWS as $window) {
            $opponent = Character::query()
                ->where('user_id', '!=', $user->id) // Jamais ses propres personnages
                ->whereNull('recycled_at')
                ->where('pending_reveal', false)
                ->whereHas('user', fn ($owner) => $owner->whereBetween('elo', [$user->elo - $window, $user->elo + $window]))
                ->orderByRaw('ABS(power_score - ?)', [$attackerCard->power_score]) // Puissance la plus proche d'abord
                ->inRandomOrder() // À puissance égale, on varie les adversaires
                ->first();

            if ($opponent) {
                return $opponent;
            }
        }

        return null;
    }

    /**
     * Probabilité que le joueur gagne, selon l'écart d'Élo (entre 0 et 1).
     * E_A = 1 / (1 + 10^((R_B - R_A) / 400))
     */
    public function expectedScore(int $playerElo, int $opponentElo): float
    {
        return 1 / (1 + 10 ** (($opponentElo - $playerElo) / 400));
    }

    /**
     * Variation d'Élo après un duel.
     * Delta = round(K × (S - E_A)), avec S = 1 en cas de victoire et 0 en cas de défaite.
     */
    public function calculateDelta(int $playerElo, int $opponentElo, bool $isWin): int
    {
        $score = $isWin ? 1 : 0;

        return (int) round(self::K_FACTOR * ($score - $this->expectedScore($playerElo, $opponentElo)));
    }

    /**
     * Nouvel Élo du joueur après un duel, avec plancher à 0.
     */
    public function newRating(int $playerElo, int $opponentElo, bool $isWin): int
    {
        return max(self::MIN_ELO, $playerElo + $this->calculateDelta($playerElo, $opponentElo, $isWin));
    }
}
