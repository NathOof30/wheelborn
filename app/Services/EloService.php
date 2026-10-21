<?php

namespace App\Services;

use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Classement Élo selon la formule officielle des échecs (FIDE), enrichie d'un modulateur
 * de difficulté de combat (anti-farm & valorisation d'outsider) et matchmaking par proximité d'Élo.
 *
 * Exemples FIDE (K = 32, puissances équivalentes) :
 * - 1000 bat 1000 : +16 ; 1000 perd contre 1000 : -16
 * - 1000 bat 1150 : +23 (exploit récompensé) ; 1000 perd contre 1150 : -9 (défaite prévisible)
 *
 * Modulateur de puissance (ratio PowerScore challenger / opponent) :
 * - Victoire écrasante (carte 2x plus forte) : gain amorti (+4 à +6 Élo) anti-farm
 * - Victoire d'outsider (carte 2x plus faible) : gain boosté (+22 à +28 Élo)
 */
class EloService
{
    // Volatilité : gain ou perte maximum de base sur un seul duel
    public const K_FACTOR = 32;

    // L'Élo ne peut jamais devenir négatif
    public const MIN_ELO = 0;

    // Matchmaking : fenêtres d'Élo essayées l'une après l'autre (±100 en priorité, jusqu'à ±250)
    public const MATCHMAKING_WINDOWS = [100, 150, 200, 250];

    /**
     * Trouve une liste ordonnée de candidats réels pour la carte choisie :
     * 1. Joueurs dont l'Élo est proche (±100 en priorité, élargi jusqu'à ±250 si personne) ;
     * 2. Cartes dont la puissance (power_score) est la plus proche de l'attaquant.
     */
    public function findOpponentCandidates(User $user, Character $attackerCard, int $limit = 10): Collection
    {
        $widestWindow = max(self::MATCHMAKING_WINDOWS);

        $windowNumberSql = 'CASE';
        $windowNumberBindings = [];
        foreach (self::MATCHMAKING_WINDOWS as $windowNumber => $windowSize) {
            $windowNumberSql .= " WHEN ABS(users.elo - ?) <= {$windowSize} THEN {$windowNumber}";
            $windowNumberBindings[] = $user->elo;
        }
        $windowNumberSql .= ' END';

        return Character::query()
            ->select('characters.*')
            ->with('user')
            ->join('users', 'users.id', '=', 'characters.user_id')
            ->where('characters.user_id', '!=', $user->id) // Jamais ses propres personnages
            ->whereNull('characters.recycled_at')
            ->where('characters.pending_reveal', false)
            ->whereBetween('users.elo', [$user->elo - $widestWindow, $user->elo + $widestWindow])
            ->orderByRaw($windowNumberSql, $windowNumberBindings)                                  // 1. Élo le plus proche d'abord
            ->orderByRaw('ABS(characters.power_score - ?)', [$attackerCard->power_score])           // 2. Puissance la plus proche
            ->inRandomOrder()                                                                      // 3. À égalité, on varie les adversaires
            ->limit($limit)
            ->get();
    }

    /**
     * Trouve le meilleur adversaire réel unique pour la carte choisie.
     * Retourne null si aucun adversaire n'est trouvé dans la fenêtre ±250.
     */
    public function findOpponent(User $user, Character $attackerCard): ?Character
    {
        return $this->findOpponentCandidates($user, $attackerCard, 1)->first();
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
     * Formule de base FIDE : Delta = round(K × (S - E_A)), avec S = 1 en victoire, 0 en défaite.
     *
     * Modulateur de difficulté de puissance :
     * - Si le joueur combat avec un avantage de puissance écrasant, le gain d'Élo est amorti pour éviter le farm facile.
     * - Si le joueur combat en outsider avec une carte plus faible, la victoire est gratifiée d'un bonus de bravoure.
     */
    public function calculateDelta(
        int $playerElo,
        int $opponentElo,
        bool $isWin,
        ?int $challengerPower = null,
        ?int $opponentPower = null,
    ): int {
        $score = $isWin ? 1 : 0;
        $baseDelta = (int) round(self::K_FACTOR * ($score - $this->expectedScore($playerElo, $opponentElo)));

        // Sans données de puissance ou à puissances nulles/égales, formule FIDE standard
        if ($challengerPower === null || $opponentPower === null || $challengerPower <= 0 || $opponentPower <= 0) {
            return $baseDelta;
        }

        $powerRatio = $challengerPower / $opponentPower;

        if ($isWin) {
            if ($powerRatio > 1.0) {
                // Avantage de puissance : gain amorti pour décourager le farm unilatéral sans risque
                // Ex: Ratio 1.25 -> 0.85 ; Ratio 1.5 -> 0.70 ; Ratio 2.0 -> 0.40 ; plancher 0.25
                $multiplier = max(0.25, 1.0 - ($powerRatio - 1.0) * 0.60);
            } else {
                // Carte plus faible (outsider) : bonus récompensant le skill et la tactique
                // Ex: Ratio 0.75 -> 1.25 ; Ratio 0.50 -> 1.50 ; plafond 1.75
                $multiplier = min(1.75, 1.0 + (1.0 - $powerRatio) * 1.0);
            }

            $scaledDelta = (int) round($baseDelta * $multiplier);

            // Garantir au moins +2 Élo lors d'une victoire
            return max(2, $scaledDelta);
        } else {
            if ($powerRatio > 1.0) {
                // Défaite humiliante avec une carte bien plus puissante : sanction amplifiée
                $multiplier = min(1.50, 1.0 + ($powerRatio - 1.0) * 0.50);
            } else {
                // Défaite prévisible face à un adversaire nettement supérieur : défaite atténuée
                $multiplier = max(0.50, 1.0 - (1.0 - $powerRatio) * 0.60);
            }

            $scaledDelta = (int) round($baseDelta * $multiplier);

            // Garantir au moins -2 Élo lors d'une défaite
            return min(-2, $scaledDelta);
        }
    }

    /**
     * Nouvel Élo du joueur après un duel, avec plancher à 0.
     */
    public function newRating(
        int $playerElo,
        int $opponentElo,
        bool $isWin,
        ?int $challengerPower = null,
        ?int $opponentPower = null,
    ): int {
        return max(self::MIN_ELO, $playerElo + $this->calculateDelta($playerElo, $opponentElo, $isWin, $challengerPower, $opponentPower));
    }
}
