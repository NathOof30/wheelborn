<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EnergyManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_energy_manager_recharges_spins_after_time_elapsed(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 0,
            'last_spin_regen_at' => now()->subMinutes(10), // 600s passées (recharge de 2 charges de 240s)
            'is_admin' => false,
        ]);

        $service = app(EnergyManagerService::class);
        $service->regenSpins($user);

        $user->refresh();
        $this->assertEquals(2, $user->spin_charges);
        $this->assertNotNull($user->last_spin_regen_at);
    }

    public function test_energy_manager_caps_at_max_charges_and_clears_timer(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 1,
            'last_spin_regen_at' => now()->subHours(2), // 2h passées
            'is_admin' => false,
        ]);

        $service = app(EnergyManagerService::class);
        $service->regenSpins($user);

        $user->refresh();
        $this->assertEquals(3, $user->spin_charges);
        $this->assertNull($user->last_spin_regen_at);
    }

    public function test_energy_manager_calculates_next_regen_seconds_accurately(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 2,
            'last_spin_regen_at' => now()->subSeconds(60), // 60s écoulées sur 240s
            'is_admin' => false,
        ]);

        $service = app(EnergyManagerService::class);
        $remaining = $service->getNextSpinRegenSeconds($user);

        // Doit rester environ 180s (entre 178 et 180)
        $this->assertGreaterThanOrEqual(178, $remaining);
        $this->assertLessThanOrEqual(180, $remaining);
    }
}
