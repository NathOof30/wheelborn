<?php

namespace Tests\Feature;

use App\Livewire\CharacterWheel;
use App\Models\User;
use App\Services\WorkshopService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T4.3 : Ticket d'Urgence, achat d'une charge de spin quand la réserve est vide
 * (120 🪙 puis 250 🪙, 2 achats par jour au maximum).
 */
class EmergencySpinTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WorkshopService
    {
        return app(WorkshopService::class);
    }

    /** Joueur sans charge, avec une recharge qui vient de démarrer (aucune charge ne revient pendant le test). */
    private function playerWithoutCharges(int $coins = 1000): User
    {
        return User::factory()->create([
            'coins'              => $coins,
            'spin_charges'       => 0,
            'last_spin_regen_at' => now(),
        ]);
    }

    public function test_first_purchase_gives_one_charge_for_120_coins(): void
    {
        $user = $this->playerWithoutCharges();

        $result = $this->service()->buyEmergencySpin($user);

        $this->assertSame(['cost' => 120, 'coins' => 880, 'spin_charges' => 1], $result);
        $this->assertSame(880, $user->coins); // Le modèle de l'appelant est à jour
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 880, 'spin_charges' => 1]);
        $this->assertDatabaseHas('workshop_upgrades', ['user_id' => $user->id, 'emergency_purchases_today' => 1]);
    }

    public function test_second_purchase_costs_250_and_third_is_refused(): void
    {
        $user = $this->playerWithoutCharges();

        $this->service()->buyEmergencySpin($user);
        $user->update(['spin_charges' => 0]); // La charge achetée a été utilisée

        $this->assertSame(250, $this->service()->buyEmergencySpin($user)['cost']);
        $user->update(['spin_charges' => 0]);

        $this->assertSame(['purchases_today' => 2, 'remaining' => 0, 'next_cost' => null], $this->service()->emergencySpinStatus($user));

        try {
            $this->service()->buyEmergencySpin($user);
            $this->fail('Un 3e Ticket d\'Urgence dans la journée doit être refusé.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Limite atteinte', $e->getMessage());
        }

        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 630, 'spin_charges' => 0]);
    }

    public function test_purchase_is_refused_while_player_still_has_charges(): void
    {
        $user = User::factory()->create(['coins' => 1000, 'spin_charges' => 1, 'last_spin_regen_at' => now()]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('encore des charges');

        try {
            $this->service()->buyEmergencySpin($user);
        } finally {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 1000, 'spin_charges' => 1]);
        }
    }

    public function test_purchase_is_refused_if_a_charge_regenerated_in_the_meantime(): void
    {
        // Affiché à 0 charge, mais la recharge de 4 minutes est arrivée entre-temps
        $user = User::factory()->create(['coins' => 1000, 'spin_charges' => 0, 'last_spin_regen_at' => now()->subMinutes(5)]);

        try {
            $this->service()->buyEmergencySpin($user);
            $this->fail('Le joueur a déjà récupéré une charge : rien ne doit être vendu.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('encore des charges', $e->getMessage());
        }

        // Aucune pièce débitée, et la charge rechargée n'est pas perdue (le chrono n'a pas bougé)
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 1000]);
        app(\App\Services\EnergyManagerService::class)->regenSpins($user->fresh());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'spin_charges' => 1]);
    }

    public function test_purchase_is_refused_without_enough_coins(): void
    {
        $user = $this->playerWithoutCharges(coins: 119);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Pas assez de pièces');

        try {
            $this->service()->buyEmergencySpin($user);
        } finally {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 119, 'spin_charges' => 0]);
        }
    }

    public function test_daily_limit_resets_the_next_day(): void
    {
        $user = $this->playerWithoutCharges();
        $this->service()->buyEmergencySpin($user);
        $user->update(['spin_charges' => 0]);
        $this->service()->buyEmergencySpin($user);

        $this->travel(1)->days();
        $user->update(['spin_charges' => 0, 'last_spin_regen_at' => now()]);

        $this->assertSame(['purchases_today' => 0, 'remaining' => 2, 'next_cost' => 120], $this->service()->emergencySpinStatus($user));
        $this->assertSame(120, $this->service()->buyEmergencySpin($user)['cost']);
    }

    public function test_wheel_offers_emergency_ticket_only_when_charges_are_empty(): void
    {
        $withCharges = User::factory()->create(['spin_charges' => 3]);
        Livewire::actingAs($withCharges)->test(CharacterWheel::class)
            ->assertDontSee("Ticket d'Urgence");

        $empty = $this->playerWithoutCharges();
        Livewire::actingAs($empty)->test(CharacterWheel::class)
            ->assertSee("Ticket d'Urgence : +1 charge (120 🪙)", false)
            ->call('buyEmergencySpin')
            ->assertSet('spinCharges', 1)
            ->assertSet('coins', 880)
            ->assertSet('emergencyCost', 250)
            ->assertDispatched('stats-updated')
            ->call('spin') // La charge achetée permet de relancer la roue
            ->assertSet('spinCharges', 0);

        $this->assertDatabaseCount('characters', 1);
    }

    public function test_wheel_shows_error_when_emergency_purchase_is_refused(): void
    {
        $user = $this->playerWithoutCharges(coins: 50);

        Livewire::actingAs($user)->test(CharacterWheel::class)
            ->assertSee('Pas assez de pièces (50/120 🪙)', false)
            ->call('buyEmergencySpin')
            ->assertSet('spinCharges', 0)
            ->assertSet('notificationType', 'error');
    }
}
