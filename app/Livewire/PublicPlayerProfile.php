<?php

namespace App\Livewire;

use App\Data\GameConfig;
use App\Enums\Rarity;
use App\Models\Achievement;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use App\Services\RecycleService;
use Livewire\Component;

class PublicPlayerProfile extends Component
{
    public User $user;
    public ?string $inspectedCharacterId = null;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Ouvre la modale d'inspection grand format d'un héros de la vitrine.
     */
    public function inspectCharacter(string $characterId): void
    {
        $this->inspectedCharacterId = $characterId;
    }

    public function closeInspector(): void
    {
        $this->inspectedCharacterId = null;
    }

    public function render()
    {
        $levelProgress = $this->user->currentLevelProgress();
        $league = $this->user->getLeague();
        $favoriteCharacters = $this->user->favoriteCharacters()->get();

        // Statistiques d'Arène fidèles
        $battleStats = $this->user->battleStats();
        $battlesCount = $battleStats['total'];
        $winsCount = $battleStats['wins'];
        $lossesCount = $battleStats['losses'];
        $drawsCount = $battleStats['draws'];
        $winrate = $battleStats['winrate'];

        // Statistiques de Collection
        $collectionCount = Character::where('user_id', $this->user->id)->whereNull('recycled_at')->count();
        $legendariesCount = Character::where('user_id', $this->user->id)
            ->whereNull('recycled_at')
            ->where('rarity', Rarity::Legendary)
            ->count();

        // Calcul des synergies uniques découvertes
        $activeCharacters = Character::where('user_id', $this->user->id)->whereNull('recycled_at')->get();
        $discoveredSynergies = [];
        foreach ($activeCharacters as $char) {
            foreach ($char->synergies_data ?? [] as $syn) {
                if (!empty($syn['name'])) {
                    $discoveredSynergies[$syn['name']] = true;
                }
            }
        }
        $synergiesCount = count($discoveredSynergies);

        // Statistiques Économiques
        $recycledCount = Character::where('user_id', $this->user->id)->whereNotNull('recycled_at')->count();
        $workshopLevel = (int) ($this->user->workshop_level ?? 1);

        // Liste des Succès
        $unlockedAchievements = Achievement::where('user_id', $this->user->id)
            ->get()
            ->keyBy('slug');

        $achievementsList = [];
        $definitions = Achievement::DEFINITIONS ?? [
            'first_spin' => [
                'title'       => 'Premier Destin',
                'description' => 'Tourner la Roue cosmique pour la première fois.',
                'icon'        => '🎡',
                'reward_coins' => 100,
            ],
            'first_win' => [
                'title'       => 'Première Victoire',
                'description' => 'Remporter un premier duel dans l\'Arène.',
                'icon'        => '⚔️',
                'reward_coins' => 100,
            ],
            'recycle_10' => [
                'title'       => 'Maître Alchimiste',
                'description' => 'Recycler 10 cartes.',
                'icon'        => '⚗️',
                'reward_coins' => 150,
            ],
            'first_legendary' => [
                'title'       => 'Bénédiction Divine',
                'description' => 'Obtenir une carte Légendaire.',
                'icon'        => '🌟',
                'reward_coins' => 250,
            ],
            'risky_spin' => [
                'title'       => 'Combinaison Instable',
                'description' => 'Tirer une synergie à risque.',
                'icon'        => '💥',
                'reward_coins' => 150,
            ],
        ];

        foreach ($definitions as $slug => $def) {
            $unlocked = isset($unlockedAchievements[$slug]);
            $achievementsList[] = [
                'slug'         => $slug,
                'title'        => $def['title'],
                'description'  => $def['description'],
                'icon'         => $def['icon'],
                'reward_coins' => $def['reward_coins'] ?? 100,
                'unlocked'     => $unlocked,
                'unlocked_at'  => $unlocked ? $unlockedAchievements[$slug]->unlocked_at?->format('d/m/Y') : null,
            ];
        }

        // Héros inspecté dans la modale plein écran
        $inspectedCharacter = null;
        if ($this->inspectedCharacterId) {
            $inspectedCharacter = Character::where('id', $this->inspectedCharacterId)
                ->where('user_id', $this->user->id)
                ->whereNull('recycled_at')
                ->first();
        }

        return view('livewire.public-player-profile', [
            'levelProgress'      => $levelProgress,
            'league'             => $league,
            'favoriteCharacters' => $favoriteCharacters,
            'battlesCount'       => $battlesCount,
            'winsCount'          => $winsCount,
            'lossesCount'        => $lossesCount,
            'drawsCount'         => $drawsCount,
            'winrate'            => $winrate,
            'collectionCount'    => $collectionCount,
            'legendariesCount'   => $legendariesCount,
            'synergiesCount'     => $synergiesCount,
            'recycledCount'      => $recycledCount,
            'workshopLevel'      => $workshopLevel,
            'achievementsList'   => $achievementsList,
            'inspectedCharacter' => $inspectedCharacter,
            'isOwnProfile'       => auth()->check() && auth()->id() === $this->user->id,
        ]);
    }
}

