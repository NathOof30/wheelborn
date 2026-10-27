<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Character;
use App\Enums\Rarity;
use App\Enums\PowerTier;
use App\Livewire\CollectionGrid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_page_requires_authentication(): void
    {
        $response = $this->get('/collection');
        $response->assertRedirect('/login');
    }

    public function test_collection_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/collection');
        $response->assertStatus(200);
        $response->assertSee('Collection de Héros');
    }

    public function test_collection_grid_shows_empty_state_when_user_has_no_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->assertStatus(200)
            ->assertSee('Votre caserne est déserte !')
            ->assertSee('Faire tourner la Roue du Destin');
    }

    public function test_collection_grid_displays_owned_characters(): void
    {
        $user = User::factory()->create();

        $character = Character::create([
            'user_id'           => $user->id,
            'name'              => 'Eldrin Sombrelame',
            'race_id'           => 'elf',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => PowerTier::Common,
            'hp'                => 110,
            'max_hp'            => 110,
            'strength'          => 45,
            'speed'             => 60,
            'defense'           => 30,
            'power_score'       => 78,
            'rarity'            => Rarity::Rare,
            'recycle_value'     => 160,
            'pending_reveal'    => false,
            'recycled_at'       => null,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->assertStatus(200)
            ->assertSee('Eldrin Sombrelame')
            ->assertSee('78')
            ->assertSee('1 Héros')
            ->assertDontSee('Votre caserne est déserte !');
    }

    public function test_collection_grid_filters_out_pending_and_recycled_characters(): void
    {
        $user = User::factory()->create();

        // 1. Carte en attente de révélation (ne doit pas être affichée dans la collection)
        Character::create([
            'user_id'           => $user->id,
            'name'              => 'Héros Fantôme',
            'race_id'           => 'human',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => PowerTier::Common,
            'hp'                => 100,
            'max_hp'            => 100,
            'strength'          => 40,
            'speed'             => 40,
            'defense'           => 40,
            'power_score'       => 50,
            'rarity'            => Rarity::Common,
            'recycle_value'     => 70,
            'pending_reveal'    => true,
            'recycled_at'       => null,
        ]);

        // 2. Carte déjà recyclée (ne doit pas être affichée)
        Character::create([
            'user_id'           => $user->id,
            'name'              => 'Héros Poussière',
            'race_id'           => 'human',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => PowerTier::Common,
            'hp'                => 100,
            'max_hp'            => 100,
            'strength'          => 40,
            'speed'             => 40,
            'defense'           => 40,
            'power_score'       => 50,
            'rarity'            => Rarity::Common,
            'recycle_value'     => 70,
            'pending_reveal'    => false,
            'recycled_at'       => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->assertStatus(200)
            ->assertDontSee('Héros Fantôme')
            ->assertDontSee('Héros Poussière')
            ->assertSee('Votre caserne est déserte !');
    }

    public function test_character_can_be_recycled_directly_from_collection_grid(): void
    {
        $user = User::factory()->create(['coins' => 200]);

        $character = Character::create([
            'user_id'           => $user->id,
            'name'              => 'Grommash le Brisé',
            'race_id'           => 'demon',
            'weapon_id'         => 'hammer',
            'power_id'          => 'fire',
            'power_tier'        => PowerTier::Common,
            'hp'                => 105,
            'max_hp'            => 105,
            'strength'          => 50,
            'speed'             => 30,
            'defense'           => 30,
            'power_score'       => 65,
            'rarity'            => Rarity::Common,
            'recycle_value'     => 90,
            'pending_reveal'    => false,
            'recycled_at'       => null,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('recycleCharacter', $character->id)
            ->assertSee('+90 🪙 ajoutées à votre solde');

        $user->refresh();
        $this->assertEquals(290, $user->coins);

        $character->refresh();
        $this->assertNotNull($character->recycled_at);
    }

    public function test_collection_grid_can_search_by_name(): void
    {
        $user = User::factory()->create();

        Character::create([
            'user_id' => $user->id,
            'name' => 'Aurelia la Céleste',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'light',
            'power_tier' => PowerTier::Mythic,
            'hp' => 120,
            'max_hp' => 120,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 95,
            'rarity' => Rarity::Legendary,
            'recycle_value' => 300,
            'pending_reveal' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Kragor le Sanguinaire',
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 110,
            'max_hp' => 110,
            'strength' => 60,
            'speed' => 40,
            'defense' => 30,
            'power_score' => 70,
            'rarity' => Rarity::Common,
            'recycle_value' => 80,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->set('search', 'Aurelia')
            ->assertSee('Aurelia la Céleste')
            ->assertDontSee('Kragor le Sanguinaire')
            ->set('search', 'Kragor')
            ->assertSee('Kragor le Sanguinaire')
            ->assertDontSee('Aurelia la Céleste')
            ->set('search', 'Inexistant')
            ->assertSee('Aucun héros trouvé')
            ->assertDontSee('Aurelia la Céleste');
    }

    public function test_collection_grid_can_filter_by_rarity(): void
    {
        $user = User::factory()->create();

        Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Légendaire',
            'race_id' => 'dragon',
            'weapon_id' => 'spear',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Mythic,
            'hp' => 130,
            'max_hp' => 130,
            'strength' => 70,
            'speed' => 60,
            'defense' => 60,
            'power_score' => 100,
            'rarity' => Rarity::Legendary,
            'recycle_value' => 400,
            'pending_reveal' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Commun',
            'race_id' => 'goblin',
            'weapon_id' => 'daggers',
            'power_id' => 'poison',
            'power_tier' => PowerTier::Common,
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 30,
            'speed' => 50,
            'defense' => 20,
            'power_score' => 40,
            'rarity' => Rarity::Common,
            'recycle_value' => 50,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('setRarity', 'legendary')
            ->assertSee('Héros Légendaire')
            ->assertDontSee('Héros Commun')
            ->call('setRarity', 'common')
            ->assertSee('Héros Commun')
            ->assertDontSee('Héros Légendaire');
    }

    public function test_collection_grid_can_filter_by_race_and_weapon(): void
    {
        $user = User::factory()->create();

        Character::create([
            'user_id' => $user->id,
            'name' => 'Elfe Archer',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'wind',
            'power_tier' => PowerTier::Rare,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 40,
            'speed' => 70,
            'defense' => 25,
            'power_score' => 65,
            'rarity' => Rarity::Rare,
            'recycle_value' => 120,
            'pending_reveal' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Nain Guerrier',
            'race_id' => 'dwarf',
            'weapon_id' => 'hammer',
            'power_id' => 'earth',
            'power_tier' => PowerTier::Rare,
            'hp' => 120,
            'max_hp' => 120,
            'strength' => 60,
            'speed' => 20,
            'defense' => 60,
            'power_score' => 70,
            'rarity' => Rarity::Rare,
            'recycle_value' => 120,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->set('race', 'elf')
            ->assertSee('Elfe Archer')
            ->assertDontSee('Nain Guerrier')
            ->set('race', '')
            ->set('weapon', 'hammer')
            ->assertSee('Nain Guerrier')
            ->assertDontSee('Elfe Archer');
    }

    public function test_collection_grid_can_filter_by_synergies(): void
    {
        $user = User::factory()->create();

        Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Synergique',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'wind',
            'power_tier' => PowerTier::Rare,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 40,
            'speed' => 70,
            'defense' => 25,
            'power_score' => 75,
            'rarity' => Rarity::Rare,
            'synergies_data' => [
                ['name' => 'Tempête Elfique', 'icon' => '🌪️', 'is_unstable' => false]
            ],
            'recycle_value' => 140,
            'pending_reveal' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Sans Synergie',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 40,
            'speed' => 40,
            'defense' => 30,
            'power_score' => 50,
            'rarity' => Rarity::Common,
            'synergies_data' => [],
            'recycle_value' => 60,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('toggleOnlySynergies')
            ->assertSee('Héros Synergique')
            ->assertDontSee('Héros Sans Synergie')
            ->call('resetFilters')
            ->assertSee('Héros Synergique')
            ->assertSee('Héros Sans Synergie');
    }

    public function test_collection_grid_can_sort_characters(): void
    {
        $user = User::factory()->create();

        $cWeak = Character::create([
            'user_id' => $user->id,
            'name' => 'Faible Recrue',
            'race_id' => 'goblin',
            'weapon_id' => 'daggers',
            'power_id' => 'poison',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 20,
            'speed' => 30,
            'defense' => 10,
            'power_score' => 30,
            'rarity' => Rarity::Common,
            'recycle_value' => 40,
            'pending_reveal' => false,
        ]);

        $cStrong = Character::create([
            'user_id' => $user->id,
            'name' => 'Colosse Puissant',
            'race_id' => 'demon',
            'weapon_id' => 'hammer',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Mythic,
            'hp' => 130,
            'max_hp' => 130,
            'strength' => 80,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 99,
            'rarity' => Rarity::Legendary,
            'recycle_value' => 350,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        // Tri par défaut (power_desc)
        Livewire::test(CollectionGrid::class)
            ->set('sortBy', 'power_desc')
            ->assertSeeInOrder(['Colosse Puissant', 'Faible Recrue'])
            ->set('sortBy', 'power_asc')
            ->assertSeeInOrder(['Faible Recrue', 'Colosse Puissant']);
    }

    public function test_character_can_be_locked_and_unlocked(): void
    {
        $user = User::factory()->create();

        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Gardien Précieux',
            'race_id' => 'human',
            'weapon_id' => 'shield',
            'power_id' => 'light',
            'power_tier' => PowerTier::Rare,
            'hp' => 120,
            'max_hp' => 120,
            'strength' => 40,
            'speed' => 40,
            'defense' => 70,
            'power_score' => 85,
            'rarity' => Rarity::Rare,
            'recycle_value' => 150,
            'is_locked' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        // Verrouillage
        Livewire::test(CollectionGrid::class)
            ->call('toggleLock', $character->id)
            ->assertSee('est désormais verrouillé');

        $character->refresh();
        $this->assertTrue($character->is_locked);

        // Déverrouillage
        Livewire::test(CollectionGrid::class)
            ->call('toggleLock', $character->id)
            ->assertSee('a été déverrouillé');

        $character->refresh();
        $this->assertFalse($character->is_locked);
    }

    public function test_locked_character_cannot_be_recycled(): void
    {
        $user = User::factory()->create(['coins' => 100]);

        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Sanctuaire',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'wind',
            'power_tier' => PowerTier::Rare,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 40,
            'speed' => 70,
            'defense' => 30,
            'power_score' => 75,
            'rarity' => Rarity::Rare,
            'recycle_value' => 200,
            'is_locked' => true,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('recycleCharacter', $character->id)
            ->assertSee('Ce combattant est verrouillé')
            ->assertSee('Protégé');

        $user->refresh();
        $this->assertEquals(100, $user->coins);

        $character->refresh();
        $this->assertNull($character->recycled_at);
    }

    public function test_batch_recycling_modal_recycles_only_unlocked_matching_cards(): void
    {
        $user = User::factory()->create(['coins' => 50]);

        // 1. Commun non verrouillé (doit être recyclé)
        $common1 = Character::create([
            'user_id' => $user->id,
            'name' => 'Recrue 1',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 30,
            'speed' => 30,
            'defense' => 20,
            'power_score' => 40,
            'rarity' => Rarity::Common,
            'recycle_value' => 60,
            'is_locked' => false,
            'pending_reveal' => false,
        ]);

        // 2. Commun non verrouillé (doit être recyclé)
        $common2 = Character::create([
            'user_id' => $user->id,
            'name' => 'Recrue 2',
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'earth',
            'power_tier' => PowerTier::Common,
            'hp' => 85,
            'max_hp' => 85,
            'strength' => 35,
            'speed' => 25,
            'defense' => 20,
            'power_score' => 45,
            'rarity' => Rarity::Common,
            'recycle_value' => 70,
            'is_locked' => false,
            'pending_reveal' => false,
        ]);

        // 3. Commun VERROUILLÉ (ne doit PAS être recyclé)
        $lockedCommon = Character::create([
            'user_id' => $user->id,
            'name' => 'Recrue Favorite',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'wind',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 30,
            'speed' => 40,
            'defense' => 20,
            'power_score' => 45,
            'rarity' => Rarity::Common,
            'recycle_value' => 60,
            'is_locked' => true,
            'pending_reveal' => false,
        ]);

        // 4. Rare non verrouillé (ne doit PAS être recyclé si la modale ne cible que les Communs)
        $unlockedRare = Character::create([
            'user_id' => $user->id,
            'name' => 'Guerrier Rare',
            'race_id' => 'demon',
            'weapon_id' => 'hammer',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Rare,
            'hp' => 110,
            'max_hp' => 110,
            'strength' => 50,
            'speed' => 40,
            'defense' => 30,
            'power_score' => 70,
            'rarity' => Rarity::Rare,
            'recycle_value' => 120,
            'is_locked' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('openBatchModal')
            ->assertSet('showBatchModal', true)
            ->assertSee('Recyclage en Masse')
            ->call('confirmBatchRecycle')
            ->assertSet('showBatchModal', false)
            ->assertDispatched('stats-updated')
            ->assertSee('2 combattants recyclés pour +130 🪙');

        $user->refresh();
        // 50 init + 60 (common1) + 70 (common2) = 180 coins
        $this->assertEquals(180, $user->coins);

        $common1->refresh();
        $this->assertNotNull($common1->recycled_at);

        $common2->refresh();
        $this->assertNotNull($common2->recycled_at);

        // Le commun verrouillé est resté intact
        $lockedCommon->refresh();
        $this->assertNull($lockedCommon->recycled_at);

        // Le rare non sélectionné est resté intact
        $unlockedRare->refresh();
        $this->assertNull($unlockedRare->recycled_at);
    }

    public function test_advanced_batch_recycle_with_power_race_weapon_and_synergy_filters(): void
    {
        $user = User::factory()->create(['coins' => 100]);

        // Cible 1 : Orc avec Hache, 45 Power Score, SANS synergie -> Éligible
        $target = Character::create([
            'user_id' => $user->id,
            'name' => 'Orc Guerrier',
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'earth',
            'power_tier' => PowerTier::Common,
            'hp' => 85,
            'max_hp' => 85,
            'strength' => 35,
            'speed' => 25,
            'defense' => 20,
            'power_score' => 45,
            'rarity' => Rarity::Common,
            'recycle_value' => 70,
            'is_locked' => false,
            'synergies_data' => [],
            'pending_reveal' => false,
        ]);

        // Cible 2 : Orc avec Hache MAIS Power Score = 85 (> 50) -> Exclu par le score max
        $tooStrong = Character::create([
            'user_id' => $user->id,
            'name' => 'Orc Titan',
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 110,
            'max_hp' => 110,
            'strength' => 60,
            'speed' => 30,
            'defense' => 30,
            'power_score' => 85,
            'rarity' => Rarity::Common,
            'recycle_value' => 80,
            'is_locked' => false,
            'synergies_data' => [],
            'pending_reveal' => false,
        ]);

        // Cible 3 : Humain avec Hache, 40 Power Score -> Exclu par la race
        $differentRace = Character::create([
            'user_id' => $user->id,
            'name' => 'Humain Bûcheron',
            'race_id' => 'human',
            'weapon_id' => 'axe',
            'power_id' => 'earth',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 30,
            'speed' => 30,
            'defense' => 20,
            'power_score' => 40,
            'rarity' => Rarity::Common,
            'recycle_value' => 60,
            'is_locked' => false,
            'synergies_data' => [],
            'pending_reveal' => false,
        ]);

        // Cible 4 : Orc avec Épée, 40 Power Score -> Exclu par l'arme
        $differentWeapon = Character::create([
            'user_id' => $user->id,
            'name' => 'Orc Épéiste',
            'race_id' => 'orc',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 30,
            'speed' => 30,
            'defense' => 20,
            'power_score' => 40,
            'rarity' => Rarity::Common,
            'recycle_value' => 60,
            'is_locked' => false,
            'synergies_data' => [],
            'pending_reveal' => false,
        ]);

        // Cible 5 : Orc avec Hache, 40 Power Score MAIS avec synergie active -> Exclu car batchExcludeSynergies = true
        $withSynergy = Character::create([
            'user_id' => $user->id,
            'name' => 'Orc Chamane',
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 30,
            'speed' => 30,
            'defense' => 20,
            'power_score' => 40,
            'rarity' => Rarity::Common,
            'recycle_value' => 65,
            'is_locked' => false,
            'synergies_data' => [
                ['name' => 'Flamme Tellurique', 'icon' => '🔥', 'is_unstable' => false]
            ],
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('openBatchModal')
            ->set('batchRarities', ['common'])
            ->set('batchMaxPower', '50')
            ->set('batchRace', 'orc')
            ->set('batchWeapon', 'axe')
            ->set('batchExcludeSynergies', true)
            ->call('confirmBatchRecycle')
            ->assertSet('showBatchModal', false)
            ->assertDispatched('stats-updated')
            ->assertSee('1 combattants recyclés pour +70 🪙');

        $user->refresh();
        $this->assertEquals(170, $user->coins);

        $target->refresh();
        $this->assertNotNull($target->recycled_at);

        $tooStrong->refresh();
        $this->assertNull($tooStrong->recycled_at);

        $differentRace->refresh();
        $this->assertNull($differentRace->recycled_at);

        $differentWeapon->refresh();
        $this->assertNull($differentWeapon->recycled_at);

        $withSynergy->refresh();
        $this->assertNull($withSynergy->recycled_at);
    }

    public function test_character_inspector_modal_displays_full_details_and_passives(): void
    {
        $user = User::factory()->create();

        $hero = Character::create([
            'user_id' => $user->id,
            'name' => 'Sylvana Brise-Vent',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'ice',
            'power_tier' => PowerTier::Rare,
            'hp' => 110,
            'max_hp' => 110,
            'strength' => 45,
            'speed' => 80,
            'defense' => 30,
            'power_score' => 88,
            'rarity' => Rarity::Rare,
            'recycle_value' => 180,
            'is_locked' => false,
            'wins' => 7,
            'losses' => 3,
            'synergies_data' => [
                [
                    'name' => 'Archer Glacial',
                    'icon' => '🏹❄️',
                    'description' => 'Ralentit la cible et augmente les coups critiques de 20%.',
                    'is_unstable' => false,
                ]
            ],
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        // Ouvrir l'inspecteur
        Livewire::test(CollectionGrid::class)
            ->call('inspectCharacter', $hero->id)
            ->assertSet('inspectedCharacterId', $hero->id)
            ->assertSee('Sylvana Brise-Vent')
            ->assertSee('Grâce Sylvestre') // Passif race Elfe
            ->assertSee('Tir d\'alerte') // Passif arme Arc
            ->assertSee('Archer Glacial') // Nom synergie
            ->assertSee('Ralentit la cible') // Description synergie
            ->assertSee('70%') // Taux de victoire : 7 / 10 = 70%
            ->assertSee('Fermer la fiche')
            ->call('closeInspector')
            ->assertSet('inspectedCharacterId', null);
    }

    public function test_character_can_be_recycled_from_inspector_modal(): void
    {
        $user = User::factory()->create(['coins' => 100]);

        $hero = Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Éphémère',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 40,
            'speed' => 40,
            'defense' => 30,
            'power_score' => 50,
            'rarity' => Rarity::Common,
            'recycle_value' => 75,
            'is_locked' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('inspectCharacter', $hero->id)
            ->assertSet('inspectedCharacterId', $hero->id)
            ->call('recycleCharacter', $hero->id)
            ->assertSet('inspectedCharacterId', null)
            ->assertSee('+75 🪙 ajoutées à votre solde');

        $user->refresh();
        $this->assertEquals(175, $user->coins);

        $hero->refresh();
        $this->assertNotNull($hero->recycled_at);
    }

    public function test_character_can_be_favorited_and_unfavorited(): void
    {
        $user = User::factory()->create();
        $hero = Character::create([
            'user_id' => $user->id,
            'name' => 'Étoile d\'Argent',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 70,
            'rarity' => Rarity::Rare,
            'recycle_value' => 100,
            'is_locked' => false,
            'is_favorite' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        // 1. Mise en favori
        Livewire::test(CollectionGrid::class)
            ->call('toggleFavorite', $hero->id)
            ->assertSee('ajouté à tes favoris');

        $hero->refresh();
        $this->assertTrue($hero->is_favorite);

        // 2. Retrait des favoris
        Livewire::test(CollectionGrid::class)
            ->call('toggleFavorite', $hero->id)
            ->assertSee('retiré de tes favoris');

        $hero->refresh();
        $this->assertFalse($hero->is_favorite);
    }

    public function test_favorite_quota_enforces_maximum_three_characters(): void
    {
        $user = User::factory()->create();

        // Créer 3 cartes déjà en favoris
        for ($i = 1; $i <= 3; $i++) {
            Character::create([
                'user_id' => $user->id,
                'name' => "Favori {$i}",
                'race_id' => 'human',
                'weapon_id' => 'sword',
                'power_id' => 'fire',
                'power_tier' => PowerTier::Common,
                'hp' => 100,
                'max_hp' => 100,
                'strength' => 50,
                'speed' => 50,
                'defense' => 50,
                'power_score' => 60,
                'rarity' => Rarity::Rare,
                'recycle_value' => 100,
                'is_locked' => false,
                'is_favorite' => true,
                'pending_reveal' => false,
            ]);
        }

        // Créer une 4e carte non-favorite
        $fourthHero = Character::create([
            'user_id' => $user->id,
            'name' => 'Candidat 4',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 60,
            'rarity' => Rarity::Rare,
            'recycle_value' => 100,
            'is_locked' => false,
            'is_favorite' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        // Tentative d'ajouter une 4e carte en favori -> doit être refusée
        Livewire::test(CollectionGrid::class)
            ->call('toggleFavorite', $fourthHero->id)
            ->assertSee('Vitrine pleine');

        $fourthHero->refresh();
        $this->assertFalse($fourthHero->is_favorite);
    }

    public function test_collection_grid_can_filter_by_favorites(): void
    {
        $user = User::factory()->create();

        $favorite = Character::create([
            'user_id' => $user->id,
            'name' => 'Champion Favori',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 80,
            'rarity' => Rarity::Rare,
            'recycle_value' => 100,
            'is_locked' => false,
            'is_favorite' => true,
            'pending_reveal' => false,
        ]);

        $regular = Character::create([
            'user_id' => $user->id,
            'name' => 'Combattant Ordinaire',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 50,
            'rarity' => Rarity::Common,
            'recycle_value' => 50,
            'is_locked' => false,
            'is_favorite' => false,
            'pending_reveal' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CollectionGrid::class)
            ->call('toggleOnlyFavorites')
            ->assertSee('Champion Favori')
            ->assertDontSee('Combattant Ordinaire');
    }
}
