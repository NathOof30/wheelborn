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

        // 2. Armes existantes (Épée, Bâton, Arc, Dague, Marteau, Lance, Grimoire)
        $this->assertEquals('images/characters/weapons/epee.webp', PaperDollService::getWeaponAsset('sword'));
        $this->assertEquals('images/characters/weapons/baton.webp', PaperDollService::getWeaponAsset('staff'));
        $this->assertEquals('images/characters/weapons/arc.webp', PaperDollService::getWeaponAsset('bow'));
        $this->assertEquals('images/characters/weapons/dague.webp', PaperDollService::getWeaponAsset('dagger'));
        $this->assertEquals('images/characters/weapons/hammer.webp', PaperDollService::getWeaponAsset('hammer'));
        $this->assertEquals('images/characters/weapons/spear.webp', PaperDollService::getWeaponAsset('spear'));
        $this->assertEquals('images/characters/weapons/grimoire.webp', PaperDollService::getWeaponAsset('grimoire'));

        // 3. Armes non dessinées (pistolet / gun) renvoient null
        $this->assertNull(PaperDollService::getWeaponAsset('gun'));

        // 4. Détection des calques visuels disponibles
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'sword'));
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'gun')); // la race existe
        $this->assertFalse(PaperDollService::hasVisualLayers('elf', 'gun')); // ni la race ni l'arme n'existent
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

        // Créer un Elfe avec Pistolet (aucun des deux n'a d'asset pour l'instant)
        $elfGun = Character::create([
            'user_id' => $user->id,
            'name' => 'Legolas',
            'race_id' => 'elf',
            'weapon_id' => 'gun',
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
