<?php

namespace App\Services;

use App\Enums\League;
use App\Models\User;

/**
 * Fantômes d'Arène : adversaires générés à la volée quand le matchmaking ne trouve aucun vrai joueur
 * dans la plage d'Élo (anti « cold start » : on peut jouer même si la base est vide).
 * Ils ne sont jamais enregistrés en base.
 */
class BotGeneratorService
{
    // Bonus appliqué à chaque stat selon la ligue du joueur : plus la ligue est haute, plus le fantôme est coriace
    private const STAT_BONUS_BY_LEAGUE = [
        'bronze'   => -10,
        'silver'   => 0,
        'gold'     => 5,
        'platinum' => 10,
        'master'   => 15,
    ];

    // Écart d'Élo maximum entre le fantôme et le joueur
    private const ELO_SPREAD = 100;

    // Nom procédural : un titre + une épithète (ex : "Gladiateur de l'Ombre", "Garde de Fer")
    private const TITLES = ['Gladiateur', 'Garde', 'Spectre', 'Sentinelle', 'Champion', 'Chevalier', 'Rôdeur', 'Colosse'];
    private const EPITHETS = ["de l'Ombre", 'de Fer', 'des Cendres', 'du Néant', 'des Abysses', 'de Givre', 'sans Visage', 'du Crépuscule'];

    public function __construct(private CharacterGeneratorService $characterGenerator)
    {
    }

    /**
     * Génère un Fantôme d'Arène adapté à la ligue actuelle du joueur :
     * stats ajustées selon la ligue, et Élo proche de celui du joueur sans sortir de sa ligue.
     */
    public function generateGhost(User $user): array
    {
        $playerLeague = $user->getLeague();

        $ghostStats = $this->characterGenerator->generateBot(self::STAT_BONUS_BY_LEAGUE[$playerLeague->value]);

        return array_merge($ghostStats, [
            'name' => $this->ghostName(),
            'elo'  => $this->ghostElo($user->elo, $playerLeague),
            'kind' => 'ghost',
        ]);
    }

    private function ghostName(): string
    {
        return self::TITLES[array_rand(self::TITLES)] . ' ' . self::EPITHETS[array_rand(self::EPITHETS)];
    }

    /** Élo tiré à ±100 autour du joueur, mais borné à sa ligue. */
    private function ghostElo(int $playerElo, League $playerLeague): int
    {
        $lowestElo = max($playerLeague->minElo(), $playerElo - self::ELO_SPREAD);
        $highestElo = min($playerLeague->maxElo() ?? PHP_INT_MAX, $playerElo + self::ELO_SPREAD);

        return random_int($lowestElo, max($lowestElo, $highestElo));
    }
}
