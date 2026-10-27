<?php

namespace App\Livewire;

use App\Enums\League;
use App\Models\Battle;
use App\Models\User;
use App\Services\LeaderboardService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Leaderboard extends Component
{
    // Podium néon : couleur, médaille et hauteur de marche (en px) pour chaque place
    private const PODIUM = [
        1 => ['color' => '#fbbf24', 'medal' => '🥇', 'height' => 140],
        2 => ['color' => '#cbd5e1', 'medal' => '🥈', 'height' => 110],
        3 => ['color' => '#cd7f32', 'medal' => '🥉', 'height' => 85],
    ];

    // Onglet actif ("all" ou une ligue), gardé dans l'URL pour pouvoir partager le lien
    #[Url]
    public string $league = 'all';

    public ?array $inspectedPlayer = null;
    public bool $showInspectorModal = false;

    public function setLeague(string $league): void
    {
        $this->league = ($league === 'all' || League::tryFrom($league)) ? $league : 'all';
    }

    /**
     * Ouvre la modale d'inspection d'un rival depuis le classement.
     */
    public function inspectPlayer(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $progress = $user->currentLevelProgress();
        $league = $user->getLeague();
        $stats = $user->battleStats();
        $battles = $stats['total'];
        $wins = $stats['wins'];
        $winrate = $stats['winrate'];
        $favorites = $user->favoriteCharacters()->get()->map(fn ($c) => [
            'id'          => (string) $c->id,
            'name'        => $c->name,
            'rarity'      => $c->rarity->label(),
            'rarity_key'  => $c->rarity->value,
            'power_score' => $c->power_score,
            'race'        => $c->race_label,
            'weapon'      => $c->weapon_label,
        ])->all();

        $this->inspectedPlayer = [
            'id'          => $user->id,
            'name'        => $user->name,
            'elo'         => $user->elo,
            'league'      => [
                'name'  => $league->name,
                'icon'  => $league->icon(),
                'color' => $league->color(),
            ],
            'level'       => $progress['level'],
            'title'       => $progress['title'],
            'battles'     => $battles,
            'wins'        => $wins,
            'winrate'     => $winrate,
            'favorites'   => $favorites,
            'profile_url' => route('player.profile', $user->name),
        ];
        $this->showInspectorModal = true;
    }

    public function closeInspector(): void
    {
        $this->showInspectorModal = false;
        $this->inspectedPlayer = null;
    }

    /** Onglets : "Tous" puis les ligues de la plus haute à la plus basse. */
    #[Computed]
    public function leagueTabs(): array
    {
        $leaderboard = app(LeaderboardService::class);
        $leagueTabs = array_map(fn (League $league) => $leaderboard->leagueData($league), array_reverse(League::cases()));

        return [['value' => 'all', 'label' => 'Tous', 'icon' => '🏆', 'color' => '#e2e8f0'], ...$leagueTabs];
    }

    /** Joueurs classés de l'onglet actif (servis par le cache de 60 secondes). */
    #[Computed]
    public function players(): array
    {
        return app(LeaderboardService::class)->top(League::tryFrom($this->league));
    }

    /** Top 3 dans l'ordre visuel du podium (2e, 1er, 3e), avec couleur, médaille et hauteur de marche. */
    #[Computed]
    public function podium(): array
    {
        $podium = [];

        foreach ([2, 1, 3] as $place) {
            if (isset($this->players[$place - 1])) {
                $podium[] = $this->players[$place - 1] + ['place' => $place] + self::PODIUM[$place];
            }
        }

        return $podium;
    }

    /** Joueurs affichés sous le podium (à partir du 4e). */
    #[Computed]
    public function playersAfterPodium(): array
    {
        return array_slice($this->players, 3);
    }

    /** Ligne épinglée du joueur connecté (null pour un admin, non classé). */
    #[Computed]
    public function currentPlayer(): ?array
    {
        return app(LeaderboardService::class)->playerStanding(auth()->user());
    }

    public function render()
    {
        return view('livewire.leaderboard');
    }
}
