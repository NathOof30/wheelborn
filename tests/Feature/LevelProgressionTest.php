<?php

namespace Tests\Feature;

use App\Data\GameConfig;
use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Services\RecycleService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_calculation_formula_and_thresholds(): void
    {
        // Niveau 1 commence à 0 XP
        $this->assertSame(0, User::totalXpRequiredForLevel(1));
        $this->assertSame(1, User::calculateLevelForXp(0));
        $this->assertSame(1, User::calculateLevelForXp(99));

        // Niveau 2 à partir de 100 XP
        $this->assertSame(100, User::totalXpRequiredForLevel(2));
        $this->assertSame(2, User::calculateLevelForXp(100));
        $this->assertSame(2, User::calculateLevelForXp(249));

        // Niveau 3 à partir de 250 XP (+150)
        $this->assertSame(250, User::totalXpRequiredForLevel(3));
        $this->assertSame(3, User::calculateLevelForXp(250));
        $this->assertSame(3, User::calculateLevelForXp(449));

        // Niveau 4 à partir de 450 XP (+200)
        $this->assertSame(450, User::totalXpRequiredForLevel(4));
        $this->assertSame(4, User::calculateLevelForXp(450));

        // Niveau 5 à partir de 700 XP (+250)
        $this->assertSame(700, User::totalXpRequiredForLevel(5));
        $this->assertSame(5, User::calculateLevelForXp(700));
    }

    public function test_current_level_progress_provides_accurate_percentage_and_title(): void
    {
        $user = User::factory()->create([
            'xp' => 175,
            'level' => 2,
        ]);

        $progress = $user->currentLevelProgress();

        // Total 175 XP :
        // Niveau 2 (Palier de 100 XP à 250 XP, soit 150 XP requis pour le niveau)
        // Avancée dans le niveau = 175 - 100 = 75 XP
        // Pourcentage = 75 / 150 = 50.0%
        $this->assertSame(2, $progress['level']);
        $this->assertSame(175, $progress['total_xp']);
        $this->assertSame(75, $progress['current_level_xp']);
        $this->assertSame(150, $progress['step_xp_required']);
        $this->assertSame(250, $progress['next_level_total']);
        $this->assertSame(50.0, $progress['percentage']);
        $this->assertSame('Néophyte des Étoiles', $progress['title']);
    }

    public function test_user_add_xp_updates_level_atomically_and_detects_levelup(): void
    {
        $user = User::factory()->create([
            'xp' => 0,
            'level' => 1,
        ]);

        // 1. Gain de 60 XP : reste niveau 1
        $result = $user->addXp(60);
        $this->assertFalse($result['leveled_up']);
        $this->assertSame(1, $result['new_level']);
        $this->assertSame(60, $result['total_xp']);
        $this->assertSame(1, $user->fresh()->level);

        // 2. Gain supplémentaire de 50 XP (total 110 XP) : passage au niveau 2
        $result2 = $user->addXp(50);
        $this->assertTrue($result2['leveled_up']);
        $this->assertSame(1, $result2['old_level']);
        $this->assertSame(2, $result2['new_level']);
        $this->assertSame(110, $result2['total_xp']);
        $this->assertSame(2, $user->fresh()->level);
    }

    public function test_user_favorite_characters_relationship_limits_to_three(): void
    {
        $user = User::factory()->create();

        // Créer 4 cartes marquées comme favorites
        for ($i = 1; $i <= 4; $i++) {
            Character::create([
                'user_id' => $user->id,
                'name' => "Hero {$i}",
                'race_id' => 'human',
                'weapon_id' => 'sword',
                'power_id' => 'fire',
                'power_tier' => PowerTier::Common,
                'hp' => 100,
                'max_hp' => 100,
                'strength' => 50,
                'speed' => 50,
                'defense' => 50,
                'power_score' => 250,
                'rarity' => Rarity::Rare,
                'is_lucky_spin' => false,
                'is_unstable' => false,
                'synergies_data' => [],
                'recycle_value' => 100,
                'is_active_fighter' => false,
                'is_favorite' => true,
                'pending_reveal' => false,
            ]);
        }

        // La relation favoriteCharacters doit être plafonnée à 3
        $this->assertCount(3, $user->favoriteCharacters);
    }

    public function test_favorite_character_cannot_be_recycled(): void
    {
        $user = User::factory()->create(['coins' => 0]);

        $favoriteHero = Character::create([
            'user_id' => $user->id,
            'name' => 'Favori Céleste',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 250,
            'rarity' => Rarity::Rare,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 120,
            'is_active_fighter' => false,
            'is_locked' => false,
            'is_favorite' => true,
            'pending_reveal' => false,
        ]);

        $recycleService = app(RecycleService::class);

        // 1. Le recyclage unitaire lève une exception
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('favoris');
        $recycleService->recycle($user, $favoriteHero->id);
    }

    public function test_favorite_character_is_excluded_from_batch_recycling(): void
    {
        $user = User::factory()->create(['coins' => 0]);

        // Héros favori (ne doit JAMAIS être recyclé)
        $favoriteHero = Character::create([
            'user_id' => $user->id,
            'name' => 'Favori Protégé',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 200,
            'rarity' => Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'is_locked' => false,
            'is_favorite' => true,
            'pending_reveal' => false,
        ]);

        // Héros standard (doit être recyclé)
        $standardHero = Character::create([
            'user_id' => $user->id,
            'name' => 'Guerrier Standard',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 200,
            'rarity' => Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'is_locked' => false,
            'is_favorite' => false,
            'pending_reveal' => false,
        ]);

        $recycleService = app(RecycleService::class);

        // Preview batch
        $preview = $recycleService->getBatchRecyclePreview($user, ['rarities' => ['common']]);
        $this->assertSame(1, $preview['count']);
        $this->assertSame(50, $preview['coins']);

        // Exécution du batch
        $batchResult = $recycleService->recycleBatch($user, ['rarities' => ['common']]);
        $this->assertSame(1, $batchResult['count']);
        $this->assertSame(50, $batchResult['coins']);

        // Le héros favori est intact
        $this->assertNull($favoriteHero->fresh()->recycled_at);
        // Le héros standard est bien recyclé
        $this->assertNotNull($standardHero->fresh()->recycled_at);
    }
}
