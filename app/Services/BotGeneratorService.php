<?php

namespace App\Services;

use App\Enums\League;
use App\Models\Character;
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
    public function generateGhost(User $user, ?Character $targetFighter = null): array
    {
        $playerLeague = $user->getLeague();
        $statBonus = self::STAT_BONUS_BY_LEAGUE[$playerLeague->value] ?? 0;

        $ghostStats = $this->characterGenerator->generateBot($statBonus);

        return array_merge($ghostStats, [
            'id'   => 'ghost_' . bin2hex(random_bytes(6)),
            'name' => $this->ghostName(),
            'elo'  => $this->ghostElo($user->elo, $playerLeague),
            'kind' => 'ghost',
        ]);
    }

    /**
     * Génère un ensemble de Fantômes d'Arène diversifiés pour compléter le pool de matchmaking.
     * Assure des noms procéduraux distincts et une variété de stats autour du palier.
     */
    public function generateGhostPool(User $user, int $count, ?Character $targetFighter = null): array
    {
        $playerLeague = $user->getLeague();
        $baseBonus = self::STAT_BONUS_BY_LEAGUE[$playerLeague->value] ?? 0;
        $ghosts = [];
        $usedNames = [];

        for ($i = 0; $i < $count; $i++) {
            // Légère variance de stat (±3) pour varier les profils
            $variance = random_int(-3, 3);
            $ghostStats = $this->characterGenerator->generateBot($baseBonus + $variance);

            // Tirage d'un nom unique dans le lot si possible
            $name = $this->ghostName();
            $attempts = 0;
            while (in_array($name, $usedNames, true) && $attempts < 10) {
                $name = $this->ghostName();
                $attempts++;
            }
            $usedNames[] = $name;

            $ghosts[] = array_merge($ghostStats, [
                'id'   => 'ghost_' . bin2hex(random_bytes(6)),
                'name' => $name,
                'elo'  => $this->ghostElo($user->elo, $playerLeague),
                'kind' => 'ghost',
            ]);
        }

        return $ghosts;
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
