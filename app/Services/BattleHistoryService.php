<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\Character;
use App\Models\User;

/**
 * Historique des duels du joueur et données nécessaires pour revoir un combat dans l'arène.
 * Le combat n'est pas recalculé : on rejoue simplement le turns_log enregistré.
 */
class BattleHistoryService
{
    public const LIMIT = 10;

    // Données d'un combattant utiles à l'affichage de sa carte dans l'arène
    private const DISPLAY_FIELDS = ['id', 'name', 'race_id', 'weapon_id', 'power_id', 'hp', 'max_hp', 'strength', 'speed', 'defense', 'power_score'];

    /**
     * Les 10 derniers combats du joueur, du plus récent au plus ancien.
     *
     * @return array<int, array{id:int, outcome:string, opponent:string, opponent_kind:string, elo_delta:int, ago:string, can_replay:bool}>
     */
    public function recent(User $user): array
    {
        return Battle::where('player_id', $user->id)
            ->latest('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Battle $battle) => [
                'id'            => $battle->id,
                'outcome'       => $this->outcome($battle),
                'opponent'      => $battle->opponent_name,
                'opponent_kind' => $battle->opponent_id ? 'player' : 'ghost',
                'elo_delta'     => $battle->elo_delta,
                'ago'           => $battle->created_at->locale('fr')->shortRelativeDiffForHumans(), // "il y a 5 min"
                'can_replay'    => $this->canReplay($battle),
            ])
            ->all();
    }

    /**
     * Tout ce qu'il faut à l'arène pour revoir un combat : journal, PV de départ, bilan et cartes des deux combattants.
     * Retourne null si le combat n'existe pas, n'appartient pas au joueur ou n'a pas de journal (anciens combats).
     */
    public function replayData(User $user, int $battleId): ?array
    {
        $battle = Battle::where('player_id', $user->id)->find($battleId);

        if (!$battle || !$this->canReplay($battle)) {
            return null;
        }

        $snapshot = $battle->fighters_snapshot;
        $won = $battle->isPlayerVictory();
        $draw = $battle->isDraw();

        return [
            'log'    => $battle->turns_log,
            'max_hp' => [
                'challenger' => $snapshot['challenger']['max_hp'] ?? $snapshot['challenger']['hp'],
                'opponent'   => $snapshot['opponent']['max_hp'] ?? $snapshot['opponent']['hp'],
            ],
            // Si la carte existe encore en base, on l'utilise pour l'affichage, sinon les stats figées du duel
            'challenger' => $this->display($battle->challenger) ?? $snapshot['challenger'],
            'opponent'   => [
                ...($this->display($battle->opponent) ?? $snapshot['opponent']),
                'name'  => $battle->opponent_name,
                'elo'   => $battle->opponent_elo,
                'kind'  => $battle->opponent_id ? 'player' : 'ghost',
                'owner' => $battle->opponent?->user?->name,
            ],
            // Même format que le résultat d'un combat, pour réutiliser le bilan de l'arène
            'result' => [
                'won'           => $won,
                'draw'          => $draw,
                'turns'         => $battle->turns_count,
                'reason'        => null,
                'elo_delta'     => $battle->elo_delta,
                'league_change' => null,
                'coins'         => $battle->coins_awarded,
                'xp'            => $battle->xp_awarded,
                'replay'        => true,
            ],
        ];
    }

    /** Carte en base convertie en tableau simple (la rareté en texte), ou null si elle n'existe plus. */
    private function display(?Character $card): ?array
    {
        return $card ? ['rarity' => $card->rarity->value] + $card->only(self::DISPLAY_FIELDS) : null;
    }

    private function outcome(Battle $battle): string
    {
        return match (true) {
            $battle->isDraw()          => 'draw',
            $battle->isPlayerVictory() => 'win',
            default                    => 'loss',
        };
    }

    private function canReplay(Battle $battle): bool
    {
        return !empty($battle->turns_log) && !empty($battle->fighters_snapshot);
    }
}
