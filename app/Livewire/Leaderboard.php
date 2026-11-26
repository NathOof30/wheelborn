<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\League;
use App\Models\Character;
use App\Models\User;
use App\Services\LeaderboardService;
use App\View\Presenters\FolioCardPresenter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Leaderboard extends Component
{
    private const PODIUM = [
        1 => ['color' => '#74520A', 'medal' => '🥇', 'height' => 148],
        2 => ['color' => '#536270', 'medal' => '🥈', 'height' => 116],
        3 => ['color' => '#8A4F24', 'medal' => '🥉', 'height' => 92],
    ];

    // Onglet actif ("all" ou une ligue), gardé dans l'URL pour pouvoir partager le lien
    #[Url]
    public string $league = 'all';

    #[Locked]
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
        if (! $user) {
            return;
        }

        $progress = $user->currentLevelProgress();
        $league = $user->getLeague();
        $stats = $user->battleStats();
        $battles = $stats['total'];
        $wins = $stats['wins'];
        $winrate = $stats['winrate'];
        $presenter = app(FolioCardPresenter::class);
        $favorites = $user->favoriteCharacters()
            ->where('pending_reveal', false)
            ->get()
            ->map(fn (Character $c): array => $presenter->present($c))
            ->all();

        $this->inspectedPlayer = [
            'id' => $user->id,
            'name' => $user->name,
            'elo' => $user->elo,
            'league' => [
                'name' => $league->label(),
                'icon' => $league->icon(),
                'color' => $league->color(),
                'ink_color' => FolioCardPresenter::leagueInkColor($league->value),
            ],
            'level' => $progress['level'],
            'title' => $progress['title'],
            'battles' => $battles,
            'wins' => $wins,
            'winrate' => $winrate,
            'favorites' => $favorites,
            'profile_url' => route('player.profile', $user->name),
        ];
        $this->showInspectorModal = true;
    }

    public function closeInspector(): void
    {
        $this->showInspectorModal = false;
        $this->inspectedPlayer = null;
    }

    #[Computed]
    public function leagueTabs(): array
    {
        $leaderboard = app(LeaderboardService::class);
        $leagueTabs = array_map(function (League $league) use ($leaderboard): array {
            $data = $leaderboard->leagueData($league);
            $data['ink_color'] = FolioCardPresenter::leagueInkColor($league->value);

            return $data;
        }, array_reverse(League::cases()));

        return [
            ['value' => 'all', 'label' => 'Tous', 'icon' => '🏆', 'color' => '#6E4F0E', 'ink_color' => '#6E4F0E'],
            ...$leagueTabs,
        ];
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
