<?php

namespace App\Listeners;

use App\Enums\Rarity;
use App\Events\ArenaBattleFinished;
use App\Events\CharacterGenerated;
use App\Events\CharactersRecycled;
use App\Models\Achievement;
use App\Models\Character;

/**
 * T4.5 : débloque les succès à partir des événements de jeu.
 * Chaque méthode handle* est découverte automatiquement par Laravel grâce au type de son événement.
 * Achievement::unlock() ignore un succès déjà obtenu, on peut donc l'appeler sans vérification préalable.
 */
class ProcessAchievements
{
    /** « Premier Sang » : première victoire en arène. */
    public function handleArenaBattleFinished(ArenaBattleFinished $event): void
    {
        if ($event->won) {
            Achievement::unlock($event->user, 'first_blood');
        }
    }

    /** « Maître Alchimiste » : 10 cartes recyclées au total (unitaire et en masse confondus). */
    public function handleCharactersRecycled(CharactersRecycled $event): void
    {
        $recycled = Character::where('user_id', $event->user->id)->whereNotNull('recycled_at')->count();

        if ($recycled >= Achievement::ALCHEMIST_RECYCLE_GOAL) {
            Achievement::unlock($event->user, 'master_alchemist');
        }
    }

    /** « Bénédiction Divine » (carte Légendaire) et « Combinaison Instable » (synergie à risque) au tirage. */
    public function handleCharacterGenerated(CharacterGenerated $event): void
    {
        if ($event->character->rarity === Rarity::Legendary) {
            Achievement::unlock($event->user, 'divine_blessing');
        }

        if ($event->character->is_unstable) {
            Achievement::unlock($event->user, 'unstable_combination');
        }
    }
}
