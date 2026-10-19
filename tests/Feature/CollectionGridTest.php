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
}
