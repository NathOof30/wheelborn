<?php

namespace App\Livewire;

use App\Data\GameConfig;
use App\Models\Achievement;
use App\Models\Character;
use App\Models\User;
use App\Services\RecycleService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserProfile extends Component
{
    public string $activeTab = 'overview'; // 'overview' ou 'settings'
    public ?string $inspectedCharacterId = null;

    public ?string $notification = null;
    public ?string $notificationType = null;

    public function mount(): void
    {
        // Si la session contient une redirection de mise à jour de mot de passe ou d'erreur, basculer sur l'onglet réglages
        if (session('status') || session('errors')) {
            $this->activeTab = 'settings';
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'settings'], true)) {
            $this->activeTab = $tab;
        }
    }

    /**
     * Bascule l'état favori d'un héros (max 3).
     */
    public function toggleFavorite(string $characterId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        DB::transaction(function () use ($user, $characterId) {
            $character = Character::where('id', $characterId)
                ->where('user_id', $user->id)
                ->whereNull('recycled_at')
                ->lockForUpdate()
                ->first();

            if (!$character) {
                $this->notify("Combattant introuvable.", 'error');
                return;
            }

            if ($character->is_favorite) {
                $character->update(['is_favorite' => false]);
                $this->notify("⭐ {$character->name} a été retiré de ta vitrine.", 'info');
            } else {
                $count = Character::where('user_id', $user->id)
                    ->whereNull('recycled_at')
                    ->where('is_favorite', true)
                    ->count();

                if ($count >= 3) {
                    $this->notify("Vitrine pleine ! Tu as déjà 3 héros favoris sélectionnés.", 'warning');
                    return;
                }

                $character->update(['is_favorite' => true]);
                $this->notify("⭐ {$character->name} a été placé dans ta vitrine de profil !", 'success');
            }
        });
    }

    /**
     * Bascule le verrou de sécurité anti-recyclage.
     */
    public function toggleLock(string $characterId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $character = Character::where('id', $characterId)
            ->where('user_id', $user->id)
            ->whereNull('recycled_at')
            ->first();

        if (!$character) {
            return;
        }

        $character->update(['is_locked' => !$character->is_locked]);
        $this->notify(
            $character->is_locked ? "🔒 {$character->name} est désormais protégé." : "🔓 {$character->name} est déverrouillé.",
            'info'
        );
    }

    /**
     * Recyclage unitaire d'un héros depuis la modale d'inspection.
     */
    public function recycleCharacter(string $characterId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $character = Character::where('id', $characterId)
            ->where('user_id', $user->id)
            ->whereNull('recycled_at')
            ->first();

        if (!$character) {
            return;
        }

        if ($character->is_locked || $character->is_favorite) {
            $this->notify("Impossible de recycler une carte protégée ou en vitrine.", 'warning');
            return;
        }

        $recycleService = app(RecycleService::class);
        $coins = $recycleService->recycle($user, $character);

        $this->closeInspector();
        $this->notify("♻️ {$character->name} a été recyclé avec succès pour +{$coins} 🪙 !", 'success');
    }

    /**
     * Retire un combattant de la vitrine des favoris.
     */
    public function removeFavorite(string $characterId): void
    {
        $this->toggleFavorite($characterId);
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

    public function notify(string $message, string $type = 'info'): void
    {
        $this->notification = $message;
        $this->notificationType = $type;
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
        $this->notificationType = null;
    }

    public function render()
    {
        $user = auth()->user();

        if (!$user) {
            return view('livewire.user-profile');
        }

        // Progression de niveau & Ligue
        $levelProgress = $user->currentLevelProgress();
        $league = $user->getLeague();

        // Vitrine : Top 3 cartes favorites
        $favoriteCharacters = $user->favoriteCharacters()->get();

        // Statistiques d'Arène fidèles
        $battleStats = $user->battleStats();
        $battlesCount = $battleStats['total'];
        $winsCount = $battleStats['wins'];
        $lossesCount = $battleStats['losses'];
        $drawsCount = $battleStats['draws'];
        $winrate = $battleStats['winrate'];

        // Statistiques de Collection
        $heroesCount = $user->characters()->count();
        $legendariesCount = $user->characters()->where('rarity', 'legendary')->count();
        $epicsCount = $user->characters()->where('rarity', 'epic')->count();
        $maxPowerScore = $user->characters()->max('power_score') ?? 0;

        // Statistiques d'Économie & Recyclage
        $recycledCount = $user->allCharacters()->whereNotNull('recycled_at')->count();
        $coinsEarnedTotal = $user->playerStats?->coins_earned_total ?? ($recycledCount * 65);

        // Niveaux de l'Atelier
        $workshopUpgrades = $user->workshopUpgrades;
        $workshopLevels = [
            'rare_magnet' => $workshopUpgrades?->rare_magnet_level ?? 0,
            'hero_forge'  => $workshopUpgrades?->hero_forge_level ?? 0,
            'arena_bonus' => $workshopUpgrades?->arena_bonus_level ?? 0,
        ];

        // Armoire à trophées (Succès débloqués et verrouillés)
        $unlockedAchievements = $user->achievements()->get()->keyBy('achievement_key');
        $achievementsList = [];

        foreach (Achievement::DEFINITIONS as $key => $definition) {
            $unlocked = isset($unlockedAchievements[$key]);
            $achievementsList[] = [
                'key'         => $key,
                'name'        => $definition['name'],
                'icon'        => $definition['icon'],
                'description' => $definition['description'],
                'title'       => $definition['title'],
                'reward'      => $definition['reward'],
                'is_unlocked' => $unlocked,
                'unlocked_at' => $unlocked ? $unlockedAchievements[$key]->unlocked_at?->format('d/m/Y') : null,
            ];
        }

        // Héros inspecté dans la modale grand format
        $inspectedCharacter = $this->inspectedCharacterId
            ? Character::where('id', $this->inspectedCharacterId)
                ->where('user_id', $user->id)
                ->whereNull('recycled_at')
                ->first()
            : null;

        return view('livewire.user-profile', [
            'user'                 => $user,
            'levelProgress'        => $levelProgress,
            'league'               => $league,
            'favoriteCharacters'   => $favoriteCharacters,
            'battlesCount'         => $battlesCount,
            'winsCount'            => $winsCount,
            'lossesCount'          => $lossesCount,
            'drawsCount'           => $drawsCount,
            'winrate'              => $winrate,
            'heroesCount'          => $heroesCount,
            'legendariesCount'     => $legendariesCount,
            'epicsCount'           => $epicsCount,
            'maxPowerScore'        => $maxPowerScore,
            'recycledCount'        => $recycledCount,
            'coinsEarnedTotal'     => $coinsEarnedTotal,
            'workshopLevels'       => $workshopLevels,
            'achievementsList'     => $achievementsList,
            'inspectedCharacter'   => $inspectedCharacter,
        ]);
    }
}

