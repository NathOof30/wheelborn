<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\Rarity;
use App\Models\Achievement;
use App\Models\Character;
use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PublicPlayerProfile extends Component
{
    #[Locked]
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function render()
    {
        $levelProgress = $this->user->currentLevelProgress();
        $league = $this->user->getLeague();
        $favoriteCharacters = $this->user->favoriteCharacters()->where('pending_reveal', false)->get();

        $battleStats = $this->user->battleStats();
        $battlesCount = $battleStats['total'];
        $winsCount = $battleStats['wins'];
        $lossesCount = $battleStats['losses'];
        $drawsCount = $battleStats['draws'];
        $winrate = $battleStats['winrate'];

        $activeCharacters = Character::where('user_id', $this->user->id)
            ->where('pending_reveal', false)
            ->whereNull('recycled_at')
            ->get();

        $collectionCount = $activeCharacters->count();
        $legendariesCount = $activeCharacters->where('rarity', Rarity::Legendary)->count();

        $discoveredSynergies = [];
        foreach ($activeCharacters as $char) {
            foreach ($char->synergies_data ?? [] as $syn) {
                if (! empty($syn['name'])) {
                    $discoveredSynergies[$syn['name']] = true;
                }
            }
        }
        $synergiesCount = count($discoveredSynergies);

        $recycledCount = Character::where('user_id', $this->user->id)->whereNotNull('recycled_at')->count();
        $upgrades = $this->user->workshopUpgrades;
        $workshopLevels = [
            'rare_magnet' => (int) ($upgrades?->rare_magnet_level ?? 0),
            'hero_forge' => (int) ($upgrades?->hero_forge_level ?? 0),
            'arena_bonus' => (int) ($upgrades?->arena_bonus_level ?? 0),
        ];
        $workshopLevel = array_sum($workshopLevels);

        $unlockedAchievements = Achievement::where('user_id', $this->user->id)
            ->get()
            ->keyBy('achievement_key');

        $achievementsList = [];
        foreach (Achievement::DEFINITIONS as $key => $def) {
            $unlocked = isset($unlockedAchievements[$key]);
            $achievementsList[] = [
                'key' => $key,
                'slug' => $key,
                'name' => $def['name'],
                'title' => $def['name'],
                'description' => $def['description'],
                'icon' => $def['icon'],
                'reward' => $def['reward'],
                'reward_coins' => $def['reward'],
                'is_unlocked' => $unlocked,
                'unlocked' => $unlocked,
                'unlocked_at' => $unlocked ? $unlockedAchievements[$key]->unlocked_at?->format('d/m/Y') : null,
            ];
        }

        return view('livewire.public-player-profile', [
            'levelProgress' => $levelProgress,
            'league' => $league,
            'favoriteCharacters' => $favoriteCharacters,
            'battlesCount' => $battlesCount,
            'winsCount' => $winsCount,
            'lossesCount' => $lossesCount,
            'drawsCount' => $drawsCount,
            'winrate' => $winrate,
            'collectionCount' => $collectionCount,
            'legendariesCount' => $legendariesCount,
            'synergiesCount' => $synergiesCount,
            'recycledCount' => $recycledCount,
            'workshopLevel' => $workshopLevel,
            'workshopLevels' => $workshopLevels,
            'achievementsList' => $achievementsList,
            'isOwnProfile' => auth()->check() && auth()->id() === $this->user->id,
        ]);
    }
}
