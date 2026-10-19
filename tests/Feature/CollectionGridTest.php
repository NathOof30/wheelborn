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
}
