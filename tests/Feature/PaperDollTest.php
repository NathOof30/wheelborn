<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Character;
use App\Services\PaperDollService;
use Livewire\Livewire;
use App\Livewire\CharacterWheel;
use App\Livewire\CollectionGrid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaperDollTest extends TestCase
{
    use RefreshDatabase;

    public function test_paper_doll_resolves_existing_assets_correctly(): void
    {
        // 1. Race Humain existe
        $this->assertEquals('images/characters/races/human.webp', PaperDollService::getRaceAsset('human'));

        // 2. Armes existantes (Épée, Bâton, Arc, Dague)
        $this->assertEquals('images/characters/weapons/epee.webp', PaperDollService::getWeaponAsset('sword'));
        $this->assertEquals('images/characters/weapons/baton.webp', PaperDollService::getWeaponAsset('staff'));
        $this->assertEquals('images/characters/weapons/arc.webp', PaperDollService::getWeaponAsset('bow'));
        $this->assertEquals('images/characters/weapons/dague.webp', PaperDollService::getWeaponAsset('dagger'));

        // 3. Armes non dessinées (marteau, lance, pistolet, grimoire) renvoient null
        $this->assertNull(PaperDollService::getWeaponAsset('hammer'));
        $this->assertNull(PaperDollService::getWeaponAsset('spear'));
        $this->assertNull(PaperDollService::getWeaponAsset('gun'));

        // 4. Détection des calques visuels disponibles
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'sword'));
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'spear')); // la race existe
        $this->assertFalse(PaperDollService::hasVisualLayers('elf', 'spear')); // ni la race ni l'arme n'existent
    }

    public function test_avatar_component_renders_layered_images_for_human_with_sword_or_staff(): void
    {
        $user = User::factory()->create();

        // Créer un Humain avec Épée
        $humanSword = Character::create([
            'user_id' => $user->id,
            'name' => 'Gareth',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 250,
            'rarity' => \App\Enums\Rarity::Rare,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 100,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        // Rendu dans CollectionGrid
        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/epee.webp')
            ->assertDontSee('Artwork en attente');
    }

    public function test_avatar_component_gracefully_handles_missing_assets(): void
    {
        $user = User::factory()->create();

        // Créer un Elfe avec Lance (aucun des deux n'a d'asset pour l'instant)
        $elfSpear = Character::create([
            'user_id' => $user->id,
            'name' => 'Legolas',
            'race_id' => 'elf',
            'weapon_id' => 'spear',
            'power_id' => 'wind',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 45,
            'speed' => 60,
            'defense' => 35,
            'power_score' => 230,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        // Rendu dans CollectionGrid : Doit afficher le fallback placeholder sans planter
        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('Artwork en attente')
            ->assertDontSee('images/characters/races/elf.webp');
    }

    public function test_admin_can_force_test_spin_with_sword_or_staff(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@wheelborn.test',
            'is_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(CharacterWheel::class)
            ->call('adminForceTestSpin', 'sword')
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/epee.webp')
            ->assertDontSee('Artwork en attente');
    }
}
