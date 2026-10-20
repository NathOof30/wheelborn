<?php

namespace App\Services;

/**
 * Classement Élo selon la formule officielle des échecs (FIDE).
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
