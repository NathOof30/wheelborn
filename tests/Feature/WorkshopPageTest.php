<?php

namespace Tests\Feature;

use App\Livewire\Workshop;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T4.4 : page Atelier (composant Livewire Workshop).
 */
class WorkshopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_page_is_reachable_from_the_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertSee(route('workshop'), false)
            ->assertSee('Atelier');

        $this->actingAs($user)->get('/workshop')
            ->assertOk()
            ->assertSeeLivewire(Workshop::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/workshop')->assertRedirect('/login');
    }

    public function test_cards_show_level_progress_and_next_cost(): void
    {
        $user = User::factory()->create(['coins' => 1000]);
        WorkshopUpgrade::create(['user_id' => $user->id, 'hero_forge_level' => 2]);

        Livewire::actingAs($user)->test(Workshop::class)
            ->assertSee('Aimant à Pouvoirs Rares')
            ->assertSee('Forge des Héros')
            ->assertSee('Coffre de Guerre')
            ->assertSee('Niv. 2/5')                    // Forge
            ->assertSee('Niv. 0/3')                    // Coffre
            ->assertSee('Améliorer · 650 🪙', false)   // Forge niveau 3
            ->assertSee('+6 PV et +4 Force à chaque tirage')
            ->assertSee('+9 PV et +6 Force à chaque tirage');
    }

    public function test_purchase_updates_level_coins_hud_and_plays_unlock(): void
    {
        $user = User::factory()->create(['coins' => 1000]);

        Livewire::actingAs($user)->test(Workshop::class)
            ->call('purchase', 'rare_magnet')
            ->assertSet('coins', 800)
            ->assertSet('justUnlocked', 'rare_magnet')
            ->assertSet('notificationType', 'success')
            ->assertSee('Niveau 1 débloqué')
            ->assertSee('Niv. 1/5')
            ->assertSee('Améliorer · 450 🪙', false)
            ->assertDispatched('stats-updated', coins: 800);

        $this->assertDatabaseHas('workshop_upgrades', ['user_id' => $user->id, 'rare_magnet_level' => 1]);
    }

    public function test_insufficient_funds_are_shown_and_purchase_is_refused(): void
    {
        $user = User::factory()->create(['coins' => 100]);

        Livewire::actingAs($user)->test(Workshop::class)
            ->assertSee('Fonds insuffisants : il manque 50 🪙', false)   // Forge : 150
            ->assertSee('Fonds insuffisants : il manque 200 🪙', false)  // Coffre : 300
            ->call('purchase', 'arena_bonus')                           // Même si le bouton est contourné
            ->assertSet('coins', 100)
            ->assertSet('justUnlocked', null)
            ->assertSet('notificationType', 'error');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 100]);
    }

    public function test_maxed_upgrade_shows_max_level(): void
    {
        $user = User::factory()->create(['coins' => 5000]);
        WorkshopUpgrade::create(['user_id' => $user->id, 'arena_bonus_level' => 3]);

        Livewire::actingAs($user)->test(Workshop::class)
            ->assertSee('Niv. 3/3')
            ->assertSee('Niveau maximum atteint')
            ->assertSee('Or des victoires d\'arène ×2');
    }
}
