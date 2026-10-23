<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkshopUpgrade;
use App\Services\WorkshopService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WorkshopService
    {
        return app(WorkshopService::class);
    }

    public function test_new_player_has_every_upgrade_at_level_zero(): void
    {
        $user = User::factory()->create();

        $overview = $this->service()->overview($user);

        $this->assertSame(['rare_magnet', 'hero_forge', 'arena_bonus'], array_keys($overview));
        foreach ($overview as $upgrade) {
            $this->assertSame(0, $upgrade['level']);
            $this->assertFalse($upgrade['is_maxed']);
        }
        $this->assertSame('Aimant à Pouvoirs Rares', $overview['rare_magnet']['name']);
        $this->assertSame('Coffre de Guerre', $overview['arena_bonus']['name']);
        $this->assertSame(5, $overview['rare_magnet']['max_level']);
        $this->assertSame(3, $overview['arena_bonus']['max_level']);
    }

    public function test_purchase_deducts_coins_and_saves_level(): void
    {
        $user = User::factory()->create(['coins' => 1000]);

        $result = $this->service()->purchase($user, 'hero_forge');

        $this->assertSame(['upgrade' => 'hero_forge', 'level' => 1, 'cost' => 150, 'coins' => 850], $result);
        $this->assertSame(850, $user->coins); // Le modèle de l'appelant est à jour
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 850]);
        $this->assertDatabaseHas('workshop_upgrades', ['user_id' => $user->id, 'hero_forge_level' => 1]);

        // Le niveau suivant coûte le palier 2
        $this->service()->purchase($user, 'hero_forge');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 500]);
        $this->assertDatabaseHas('workshop_upgrades', ['user_id' => $user->id, 'hero_forge_level' => 2]);
    }

    public function test_purchase_is_refused_without_enough_coins(): void
    {
        $user = User::factory()->create(['coins' => 299]);

        try {
            $this->service()->purchase($user, 'arena_bonus'); // Niveau 1 : 300 pièces
            $this->fail('Un achat sans assez de pièces doit être refusé.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Pas assez de pièces', $e->getMessage());
        }

        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 299]);
        $this->assertSame(0, $this->service()->upgradesOf($user)->levelOf('arena_bonus'));
    }

    public function test_purchase_is_refused_at_max_level(): void
    {
        $user = User::factory()->create(['coins' => 5000]);
        WorkshopUpgrade::create(['user_id' => $user->id, 'arena_bonus_level' => 3]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niveau maximum');

        try {
            $this->service()->purchase($user, 'arena_bonus');
        } finally {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 5000]);
        }
    }

    public function test_unknown_upgrade_is_refused(): void
    {
        $user = User::factory()->create(['coins' => 5000]);

        $this->expectException(DomainException::class);

        $this->service()->purchase($user, 'emergency_spin'); // Achat ponctuel (T4.3), pas un niveau permanent
    }

    public function test_levels_are_saved_per_player(): void
    {
        $alice = User::factory()->create(['coins' => 5000]);
        $bob = User::factory()->create(['coins' => 5000]);

        $this->service()->purchase($alice, 'rare_magnet');
        $this->service()->purchase($alice, 'rare_magnet');

        $this->assertSame(2, $this->service()->upgradesOf($alice)->levelOf('rare_magnet'));
        $this->assertSame(0, $this->service()->upgradesOf($bob)->levelOf('rare_magnet'));
        $this->assertDatabaseCount('workshop_upgrades', 2);
    }

    public function test_effects_follow_the_purchased_levels(): void
    {
        $user = User::factory()->create();

        // Sans amélioration : aucun effet
        $this->assertSame(0, $this->service()->rareMagnetBonus($user));
        $this->assertSame(['hp' => 0, 'strength' => 0], $this->service()->heroForgeBonus($user));
        $this->assertSame(1.0, $this->service()->arenaCoinMultiplier($user));

        WorkshopUpgrade::where('user_id', $user->id)->update([
            'rare_magnet_level' => 4,
            'hero_forge_level'  => 5,
            'arena_bonus_level' => 2,
        ]);

        $this->assertSame(12, $this->service()->rareMagnetBonus($user));                         // 4 × 3 %
        $this->assertSame(['hp' => 15, 'strength' => 10], $this->service()->heroForgeBonus($user)); // 5 × (+3 PV, +2 Force)
        $this->assertSame(1.5, $this->service()->arenaCoinMultiplier($user));                      // Niveau 2 : +50 %
    }
}
