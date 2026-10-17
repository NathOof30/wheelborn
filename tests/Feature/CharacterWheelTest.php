<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\CharacterWheel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CharacterWheelTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_wheel_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->assertStatus(200)
            ->assertSee('La Roue du Destin')
            ->assertSee('Lancer la Roue');
    }

    public function test_character_wheel_can_spin_and_generate_character(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 3,
            'pity_counter' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->assertSet('spinCharges', 2)
            ->assertSet('pityCounter', 1)
            ->assertSee('Points de Vie')
            ->assertSee('Force')
            ->assertSee('Vitesse')
            ->assertSee('Défense');

        $this->assertDatabaseCount('characters', 1);
    }

    public function test_character_can_be_kept_in_collection(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 3,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->call('keepCharacter')
            ->assertSet('revealedCharacter', null);

        $this->assertDatabaseHas('characters', [
            'user_id' => $user->id,
            'pending_reveal' => false,
            'recycled_at' => null,
        ]);
    }

    public function test_character_can_be_recycled(): void
    {
        $user = User::factory()->create([
            'coins' => 250,
            'spin_charges' => 3,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->call('recycleCharacter')
            ->assertSet('revealedCharacter', null);

        $user->refresh();
        $this->assertGreaterThan(250, $user->coins);
    }
}
