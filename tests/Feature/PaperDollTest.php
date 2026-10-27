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

        // 2. Armes existantes (Sword, Staff, Bow, Dagger, Hammer, Spear, Grimoire)
        $this->assertEquals('images/characters/weapons/sword.webp', PaperDollService::getWeaponAsset('sword'));
        $this->assertEquals('images/characters/weapons/staff.webp', PaperDollService::getWeaponAsset('staff'));
        $this->assertEquals('images/characters/weapons/bow.webp', PaperDollService::getWeaponAsset('bow'));
        $this->assertEquals('images/characters/weapons/dagger.webp', PaperDollService::getWeaponAsset('dagger'));
        $this->assertEquals('images/characters/weapons/hammer.webp', PaperDollService::getWeaponAsset('hammer'));
        $this->assertEquals('images/characters/weapons/spear.webp', PaperDollService::getWeaponAsset('spear'));
        $this->assertEquals('images/characters/weapons/grimoire.webp', PaperDollService::getWeaponAsset('grimoire'));

        // Résolution tolérante : les alias français résolvent vers les assets anglais
        $this->assertEquals('images/characters/weapons/sword.webp', PaperDollService::getWeaponAsset('epee'));
        $this->assertEquals('images/characters/weapons/staff.webp', PaperDollService::getWeaponAsset('baton'));
        $this->assertEquals('images/characters/weapons/bow.webp', PaperDollService::getWeaponAsset('arc'));
        $this->assertEquals('images/characters/weapons/dagger.webp', PaperDollService::getWeaponAsset('dague'));

        // 3. Armes non dessinées (gun / pistolet) renvoient null
        $this->assertNull(PaperDollService::getWeaponAsset('gun'));
        $this->assertNull(PaperDollService::getWeaponAsset('pistolet'));

        // 4. Auras élémentaires d'armes (Les 8 auras de l'Épée existent : Fire, Ice, Lightning, Poison, Gravity, Regeneration, Teleportation, Invisibility)
        $this->assertEquals('images/characters/weapons/sword_auras/sword_fire.webp', PaperDollService::getAuraAsset('sword', 'fire'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_ice.webp', PaperDollService::getAuraAsset('sword', 'ice'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_lightning.webp', PaperDollService::getAuraAsset('sword', 'lightning'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_poison.webp', PaperDollService::getAuraAsset('sword', 'poison'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_gravity.webp', PaperDollService::getAuraAsset('sword', 'gravity'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_regeneration.webp', PaperDollService::getAuraAsset('sword', 'regeneration'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_teleportation.webp', PaperDollService::getAuraAsset('sword', 'teleportation'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_invisibility.webp', PaperDollService::getAuraAsset('sword', 'invisibility'));

        // Alias français résolus vers les assets anglais
        $this->assertEquals('images/characters/weapons/sword_auras/sword_fire.webp', PaperDollService::getAuraAsset('epee', 'feu'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_ice.webp', PaperDollService::getAuraAsset('epee', 'glace'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_lightning.webp', PaperDollService::getAuraAsset('epee', 'foudre'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_poison.webp', PaperDollService::getAuraAsset('epee', 'poison'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_gravity.webp', PaperDollService::getAuraAsset('epee', 'gravite'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_regeneration.webp', PaperDollService::getAuraAsset('epee', 'regeneration'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_teleportation.webp', PaperDollService::getAuraAsset('epee', 'teleportation'));
        $this->assertEquals('images/characters/weapons/sword_auras/sword_invisibility.webp', PaperDollService::getAuraAsset('epee', 'invisibilite'));

        // Auras du Bâton (Staff)
        $this->assertEquals('images/characters/weapons/staff_auras/staff_fire.webp', PaperDollService::getAuraAsset('staff', 'fire'));
        $this->assertEquals('images/characters/weapons/staff_auras/staff_ice.webp', PaperDollService::getAuraAsset('staff', 'ice'));
        $this->assertEquals('images/characters/weapons/staff_auras/staff_fire.webp', PaperDollService::getAuraAsset('baton', 'feu'));

        // Auras non encore dessinées renvoient null (ex: bow + fire)
        $this->assertNull(PaperDollService::getAuraAsset('bow', 'fire'));

        // 5. Détection des calques visuels disponibles
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'sword'));
        $this->assertTrue(PaperDollService::hasVisualLayers('human', 'gun')); // la race existe
        $this->assertTrue(PaperDollService::hasVisualLayers(null, 'sword', 'fire')); // l'aura existe
        $this->assertTrue(PaperDollService::hasVisualLayers(null, 'sword', 'invisibility')); // l'aura d'invisibilité existe
        $this->assertFalse(PaperDollService::hasVisualLayers('elf', 'gun', 'gravity')); // aucun calque existant
    }

    public function test_avatar_component_renders_layered_images_for_human_with_sword_or_staff(): void
    {
        $user = User::factory()->create();

        // Créer un Humain avec Épée et Feu (possède Race, Arme ET Aura)
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

        // Rendu dans CollectionGrid : Doit afficher le corps, l'arme ET l'aura au-dessus
        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/sword.webp')
            ->assertSee('images/characters/weapons/sword_auras/sword_fire.webp')
            ->assertDontSee('Artwork en attente');
    }

    public function test_avatar_component_renders_elemental_auras_for_ice_and_lightning(): void
    {
        $user = User::factory()->create();

        // Créer Humain + Épée + Glace
        $charIce = Character::create([
            'user_id' => $user->id,
            'name' => 'Frostdane',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'ice',
            'power_tier' => \App\Enums\PowerTier::Rare,
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

        // Rendu dans CollectionGrid avec aura de glace
        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('images/characters/weapons/sword_auras/sword_ice.webp');

        // Créer Humain + Épée + Foudre
        $charLightning = Character::create([
            'user_id' => $user->id,
            'name' => 'Voltstrike',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'lightning',
            'power_tier' => \App\Enums\PowerTier::Rare,
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

        // Rendu dans CollectionGrid avec aura de foudre
        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('images/characters/weapons/sword_auras/sword_lightning.webp');
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
            ->call('adminForceTestSpin', 'sword', 'fire')
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/sword.webp')
            ->assertSee('images/characters/weapons/sword_auras/sword_fire.webp')
            ->assertDontSee('Artwork en attente')
            ->call('keepCharacter')
            ->call('adminForceTestSpin', 'sword', 'ice')
            ->assertSee('images/characters/weapons/sword_auras/sword_ice.webp')
            ->call('keepCharacter')
            ->call('adminForceTestSpin', 'sword', 'lightning')
            ->assertSee('images/characters/weapons/sword_auras/sword_lightning.webp');
    }

    public function test_admin_can_use_custom_forge_to_select_and_spawn_character(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@wheelborn.test',
            'is_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(CharacterWheel::class)
            // 1. Ouvrir la Forge
            ->call('toggleAdminForge')
            ->assertSet('showAdminForge', true)
            ->assertSee('Forge Personnalisée')
            // 2. Configurer une combinaison personnalisée (Humain + Épée + Glace + Légendaire)
            ->set('adminSelectedRace', 'human')
            ->set('adminSelectedWeapon', 'sword')
            ->set('adminSelectedPower', 'ice')
            ->set('adminSelectedRarity', 'legendary')
            // 3. Spawner en mode révélation directe
            ->call('adminSpawnCustom', false)
            ->assertSet('showAdminForge', false)
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/sword.webp')
            ->assertSee('images/characters/weapons/sword_auras/sword_ice.webp')
            ->call('keepCharacter')
            // 4. Tester avec animation de roue
            ->set('adminSelectedRace', 'human')
            ->set('adminSelectedWeapon', 'sword')
            ->set('adminSelectedPower', 'lightning')
            ->call('adminSpawnCustom', true)
            ->assertSet('isSpinning', true)
            ->call('finishSpin')
            ->assertSee('images/characters/weapons/sword_auras/sword_lightning.webp');
    }

    public function test_paper_doll_hides_base_weapon_when_invisibility_aura_is_active(): void
    {
        $user = User::factory()->create();

        // 1. Test direct de PaperDollService::resolveLayers :
        // Pour l'Épée avec Feu : l'arme de base et l'aura sont toutes deux présentes
        $layersFire = PaperDollService::resolveLayers('human', 'sword', 'fire', 'rare');
        $this->assertEquals('images/characters/weapons/sword.webp', $layersFire['weapon']);
        $this->assertEquals('images/characters/weapons/sword_auras/sword_fire.webp', $layersFire['aura']);

        // Pour l'Épée avec Invisibilité : l'arme de base est STRICTEMENT masquée (null) car l'aura reprend l'outline
        $layersInvis = PaperDollService::resolveLayers('human', 'sword', 'invisibility', 'legendary');
        $this->assertNull($layersInvis['weapon'], "L'arme de base doit être null quand l'aura d'invisibilité est active.");
        $this->assertEquals('images/characters/weapons/sword_auras/sword_invisibility.webp', $layersInvis['aura']);

        // Même test avec alias français
        $layersInvisFr = PaperDollService::resolveLayers('human', 'epee', 'invisibilite', 'legendary');
        $this->assertNull($layersInvisFr['weapon']);
        $this->assertEquals('images/characters/weapons/sword_auras/sword_invisibility.webp', $layersInvisFr['aura']);

        // 2. Test des méthodes utilitaires génériques valables pour TOUTES les armes
        $this->assertTrue(PaperDollService::isInvisibilityPower('invisibility'));
        $this->assertTrue(PaperDollService::isInvisibilityPower('invisibilite'));
        $this->assertFalse(PaperDollService::isInvisibilityPower('fire'));
        $this->assertTrue(PaperDollService::isInvisibilityAura('invisibility', 'images/characters/weapons/bow_auras/bow_invisibility.webp'));
        $this->assertTrue(PaperDollService::isInvisibilityAura('invisibility', 'images/characters/weapons/staff_auras/staff_invisibility.webp'));

        // 3. Rendu UI via Livewire CollectionGrid :
        // Un héros avec Épée + Invisibilité affiche l'aura outline MAIS PAS l'épée colorée
        $heroInvis = Character::create([
            'user_id' => $user->id,
            'name' => 'Spectre',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'invisibility',
            'power_tier' => \App\Enums\PowerTier::Mythic,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 250,
            'rarity' => \App\Enums\Rarity::Legendary,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 150,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->assertSee('images/characters/races/human.webp')
            ->assertSee('images/characters/weapons/sword_auras/sword_invisibility.webp')
            ->assertDontSee('images/characters/weapons/sword.webp')
            ->assertDontSee('Artwork en attente');
    }
}

