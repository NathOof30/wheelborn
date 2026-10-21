<?php

namespace App\Livewire;

use App\Enums\League;
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

    public function setLeague(string $league): void
    {
        $this->league = ($league === 'all' || League::tryFrom($league)) ? $league : 'all';
    }

    /** Onglets : "Tous" puis les ligues de la plus haute à la plus basse. */
    #[Computed]
    public function tabs(): array
    {
        $service = app(LeaderboardService::class);
        $leagues = array_map(fn (League $league) => $service->leagueData($league), array_reverse(League::cases()));

        return [['value' => 'all', 'label' => 'Tous', 'icon' => '🏆', 'color' => '#e2e8f0'], ...$leagues];
    }

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

    /** Joueurs suivants (à partir du 4e). */
    #[Computed]
    public function others(): array
    {
        return array_slice($this->players, 3);
    }

    /** Ligne épinglée du joueur connecté (null pour un admin, non classé). */
    #[Computed]
    public function me(): ?array
    {
        return app(LeaderboardService::class)->playerStanding(auth()->user());
    }

    public function render()
    {
        return view('livewire.leaderboard');
    }
}
