<?php

namespace Tests\Feature;

use App\Livewire\CharacterWheel;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use App\Services\CharacterGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T4.2 : les améliorations de l'Atelier modifient réellement les prochains tirages de roue.
 */
class CharacterGeneratorWorkshopTest extends TestCase
{
    use RefreshDatabase;

    private function generator(): CharacterGeneratorService
    {
        return app(CharacterGeneratorService::class);
    }

    private function setLevels(User $user, array $levels): void
    {
        WorkshopUpgrade::updateOrCreate(['user_id' => $user->id], $levels);
    }

    /** Part (en %) de chaque tier dans le pool de pouvoirs. */
    private function tierShares(array $pool): array
    {
        $total = array_sum(array_column($pool, 'weight'));

        $shares = [];
        foreach ($pool as $power) {
            $shares[$power['tier']] = ($shares[$power['tier']] ?? 0) + $power['weight'] / $total * 100;
        }

        return $shares;
    }

    public function test_power_pool_is_unchanged_without_rare_magnet(): void
    {
        $user = User::factory()->create();

        $shares = $this->tierShares($this->generator()->powerPool($user));

        $this->assertEqualsWithDelta(60, $shares['common'], 0.05);
        $this->assertEqualsWithDelta(28, $shares['rare'], 0.05);
        $this->assertEqualsWithDelta(12, $shares['mythic'], 0.05);
    }

    public function test_rare_magnet_moves_three_points_per_level_from_common_to_mythic(): void
    {
        $user = User::factory()->create();
        $this->setLevels($user, ['rare_magnet_level' => 5]);

        $pool = $this->generator()->powerPool($user);
        $shares = $this->tierShares($pool);

        $this->assertEqualsWithDelta(45, $shares['common'], 0.05); // 60 - 5 × 3
        $this->assertEqualsWithDelta(28, $shares['rare'], 0.05);   // Inchangé
        $this->assertEqualsWithDelta(27, $shares['mythic'], 0.05); // 12 + 5 × 3

        // Au sein d'un tier, les proportions sont conservées (les 3 mythiques restent à égalité)
        $weights = array_column($pool, 'weight', 'id');
        $this->assertSame($weights['gravity'], $weights['teleportation']);
        $this->assertSame($weights['gravity'], $weights['invisibility']);
    }

    public function test_rare_magnet_takes_from_rares_on_super_spin(): void
    {
        $user = User::factory()->create();
        $this->setLevels($user, ['rare_magnet_level' => 5]);

        $shares = $this->tierShares($this->generator()->powerPool($user, isLuckySpin: true));

        $this->assertArrayNotHasKey('common', $shares);
        $this->assertEqualsWithDelta(55, $shares['rare'], 0.05);   // 70 - 15
        $this->assertEqualsWithDelta(45, $shares['mythic'], 0.05); // 30 + 15
    }

    public function test_rare_magnet_increases_mythic_draws(): void
    {
        $user = User::factory()->create();
        $this->setLevels($user, ['rare_magnet_level' => 5]);

        mt_srand(42);
        $mythics = 0;
        for ($i = 0; $i < 2000; $i++) {
            $power = $this->generator()->generate($user);
            $mythics += $power->power_tier->value === 'mythic' ? 1 : 0;
        }
        mt_srand();

        // 27 % attendus (12 % sans Aimant) : on vérifie que le tirage réel suit bien le pool ajusté
        $this->assertGreaterThan(2000 * 0.22, $mythics);
        $this->assertLessThan(2000 * 0.32, $mythics);
    }

    public function test_hero_forge_adds_hp_and_strength_to_new_characters(): void
    {
        $user = User::factory()->create();

        foreach ([1, 7, 42, 1234, 99999] as $seed) {
            $this->setLevels($user, ['hero_forge_level' => 0]);
            mt_srand($seed);
            $base = $this->generator()->generate($user);

            $this->setLevels($user, ['hero_forge_level' => 5]);
            mt_srand($seed);
            $forged = $this->generator()->generate($user);

            // Même tirage (même seed), seuls les bonus de la Forge diffèrent : +15 PV et +10 Force, dans les plafonds
            $this->assertSame([$base->race_id, $base->weapon_id, $base->power_id], [$forged->race_id, $forged->weapon_id, $forged->power_id]);
            $this->assertSame(min(140, $base->hp + 15), $forged->hp);
            $this->assertSame(min(100, $base->strength + 10), $forged->strength);
            $this->assertSame($base->speed, $forged->speed);
            $this->assertSame($base->defense, $forged->defense);
        }
        mt_srand();
    }

    public function test_arena_bots_do_not_get_hero_forge_bonus(): void
    {
        $user = User::factory()->create();
        $this->setLevels($user, ['hero_forge_level' => 5]);

        mt_srand(7);
        $bot = $this->generator()->generateBot();
        $this->setLevels($user, ['hero_forge_level' => 0]);
        mt_srand(7);
        $sameBot = $this->generator()->generateBot();
        mt_srand();

        $this->assertSame($sameBot['hp'], $bot['hp']);
        $this->assertSame($sameBot['strength'], $bot['strength']);
    }

    public function test_power_wheel_slices_follow_the_rare_magnet(): void
    {
        $user = User::factory()->create();
        $this->setLevels($user, ['rare_magnet_level' => 5]);

        $component = Livewire::actingAs($user)->test(CharacterWheel::class);
        $segments = collect($component->instance()->wheels()['power']['segments'])->keyBy('id');

        $this->assertEqualsWithDelta(22.5, $segments['fire']['chance'], 0.01);        // 30 × 45/60
        $this->assertEqualsWithDelta(9.0, $segments['teleportation']['chance'], 0.01); // 4 × 27/12
    }
}
