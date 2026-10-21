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
            ->assertSee('Malakor le Dévoreur')
            ->call('openFighterModal')
            ->assertSee('Valérian Brise-Fer')
            ->assertSee("Bot d'arène")
            ->call('fight')
            ->assertDispatched('battle-started');
    }

    public function test_user_with_zero_tickets_cannot_fight_and_sees_error(): void
    {
        $user = User::factory()->create([
            'arena_tickets' => 0,
            'last_arena_regen_at' => now(),
            'is_admin' => false,
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => 'Gladiateur',
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
            ->assertSet('arenaTickets', 0)
            ->call('fight')
            ->assertNotDispatched('battle-started')
            ->assertSee("Plus de ticket d'arène");
    }

    public function test_can_switch_to_another_opponent(): void
    {
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'Gladiateur',
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

        $test = Livewire::actingAs($user)->test(BattleArena::class);
        $firstBotId = $test->get('opponent')['id'];

        $test->call('newOpponent');
        $secondBotId = $test->get('opponent')['id'];

        $this->assertNotEmpty($firstBotId);
        $this->assertNotEmpty($secondBotId);
        $this->assertNotEquals($firstBotId, $secondBotId);
    }

    public function test_comparative_duel_bars_are_calculated_and_rematch_resets_battle(): void
    {
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'Kaelen',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 120,
            'max_hp' => 120,
            'strength' => 60,
            'speed' => 70,
            'defense' => 45,
            'power_score' => 75,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        $test = Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->assertSee('Comparatif des Forces')
            ->assertSee('Points de Vie')
            ->assertSee('Force d\'Attaque');

        $comparison = $test->get('comparison');
        $this->assertNotNull($comparison);
        $this->assertArrayHasKey('stats', $comparison);
        $this->assertArrayHasKey('hp', $comparison['stats']);
        $this->assertGreaterThan(0, $comparison['stats']['hp']['ratio_c']);

        // Fight then Rematch
        $test->call('fight')
            ->assertDispatched('battle-started')
            ->assertSee('Revanche')
            ->call('rematch')
            ->assertSee('Comparatif des Forces');
    }

    public function test_first_reroll_is_free_and_subsequent_rerolls_cost_gold(): void
    {
        $user = User::factory()->create(['coins' => 50, 'has_free_arena_reroll' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'Fighter',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 200,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        $test = Livewire::actingAs($user)->test(BattleArena::class);

        // Au départ : a droit à son reroll gratuit
        $this->assertSame(0, $test->get('rerollCost'));
        $test->assertSee('Gratuit');

        // 1er reroll : gratuit
        $test->call('rerollOpponent')
            ->assertDispatched('stats-updated');
        $this->assertSame(50, $user->fresh()->coins);
        $this->assertFalse($user->fresh()->has_free_arena_reroll);
        $this->assertSame(10, $test->get('rerollCost'));
        $test->assertSee('10 🪙');

        // Test Anti-Triche F5 : recharger la page ne redonne PAS le reroll gratuit
        $reloaded = Livewire::actingAs($user)->test(BattleArena::class);
        $this->assertSame(10, $reloaded->get('rerollCost'));
        $reloaded->assertSee('10 🪙');

        // 2e reroll : payant (coûte 10 pièces)
        $test->call('rerollOpponent')
            ->assertDispatched('stats-updated');
        $this->assertSame(40, $user->fresh()->coins);
    }

    public function test_combat_completion_restores_the_free_reroll_for_next_match(): void
    {
        $user = User::factory()->create(['coins' => 50, 'has_free_arena_reroll' => false]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'Fighter',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 200,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        $test = Livewire::actingAs($user)->test(BattleArena::class);
        $this->assertSame(10, $test->get('rerollCost'));

        // Le joueur fait un combat
        $test->call('fight');

        // À l'issue du combat, le reroll gratuit est restauré en BDD pour le duel suivant
        $this->assertTrue($user->fresh()->has_free_arena_reroll);
    }

    public function test_reroll_fails_if_user_has_insufficient_gold(): void
    {
        $user = User::factory()->create(['coins' => 5, 'has_free_arena_reroll' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'Fighter',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
            'power_score' => 200,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        $test = Livewire::actingAs($user)->test(BattleArena::class);

        // 1er reroll : gratuit
        $test->call('rerollOpponent');
        $this->assertSame(5, $user->fresh()->coins);
        $this->assertFalse($user->fresh()->has_free_arena_reroll);

        // 2e reroll : doit échouer car seulement 5 pièces
        $test->call('rerollOpponent');
        $this->assertSame(5, $user->fresh()->coins); // Aucune pièce déduite
        $test->assertSee('Pas assez de pièces (10 🪙 requis)');
    }
}

