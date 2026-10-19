<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\PlayerHud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerHudTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_hud_displays_authenticated_user_stats(): void
    {
        $user = User::factory()->create([
            'coins' => 450,
            'elo' => 1200,
            'spin_charges' => 2,
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(PlayerHud::class)
            ->assertStatus(200)
            ->assertSee('450')
            ->assertSee('1200')
            ->assertSee('2/3');
    }

    public function test_player_hud_updates_when_stats_updated_event_is_dispatched(): void
    {
        $user = User::factory()->create([
            'coins' => 250,
            'spin_charges' => 3,
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(PlayerHud::class)
            ->assertSee('250');

        // L'utilisateur gagne des pièces (ex: suite à un recyclage)
        $user->increment('coins', 150);

        // On émet l'événement stats-updated
        $component->dispatch('stats-updated')
            ->assertSee('400');
    }
}
