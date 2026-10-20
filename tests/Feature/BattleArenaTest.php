<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Character;
use App\Livewire\BattleArena;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BattleArenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_arena_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/arena');
        $response->assertStatus(200);
        $response->assertSee('Arène de Duel');
    }

    public function test_arena_shows_empty_state_when_user_has_no_characters(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->assertSee('Aucun combattant disponible');
    }

    public function test_arena_loads_fighters_and_generates_opponent(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'Conan',
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
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->assertSee('Conan')
            ->assertSee("Bot d'arène")
            ->assertSee('Combattre !')
            ->call('fight')
            ->assertDispatched('battle-started');
    }

    public function test_seeded_admin_can_use_battle_arena(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::where('email', 'admin@wheelborn.test')->first();

        Livewire::actingAs($admin)
            ->test(BattleArena::class)
            ->assertSee('Valérian Brise-Fer')
            ->assertSee("Bot d'arène")
            ->call('fight')
            ->assertDispatched('battle-started');
    }

    public function test_can_fight_with_thorgal(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::where('email', 'admin@wheelborn.test')->first();
        $thorgal = Character::where('name', 'Thorgal Poing-de-Roc')->first();

        Livewire::actingAs($admin)
            ->test(BattleArena::class)
            ->call('selectFighter', $thorgal->id)
            ->call('fight')
            ->assertDispatched('battle-started');
    }
}
