<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use App\Services\ArenaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coffre de Guerre (Atelier) : l'or des victoires d'arène est multiplié (×1,25 / ×1,5 / ×2), pas celui des défaites.
 */
class ArenaWarChestTest extends TestCase
{
    use RefreshDatabase;

    private function fighter(User $user, int $stat): Character
    {
        return Character::create([
            'user_id'        => $user->id,
            'name'           => 'Champion',
            'race_id'        => 'human',
            'weapon_id'      => 'sword',
            'power_id'       => 'fire',
            'power_tier'     => PowerTier::Common,
            'hp'             => $stat === 100 ? 140 : 30,
            'max_hp'         => $stat === 100 ? 140 : 30,
            'strength'       => $stat,
            'speed'          => $stat,
            'defense'        => $stat,
            'power_score'    => $stat,
            'rarity'         => Rarity::Common,
            'synergies_data' => [],
            'recycle_value'  => 40,
            'pending_reveal' => false,
        ]);
    }

    /** Fantôme d'arène au format attendu par ArenaService::fight(). */
    private function ghost(int $stat): array
    {
        return [
            'id'          => 'ghost-test',
            'name'        => 'Fantôme de test',
            'race_id'     => 'human',
            'weapon_id'   => 'sword',
            'power_id'    => 'fire',
            'hp'          => $stat === 100 ? 140 : 30,
            'max_hp'      => $stat === 100 ? 140 : 30,
            'strength'    => $stat,
            'speed'       => $stat,
            'defense'     => $stat,
            'power_score' => $stat,
            'rarity'      => 'common',
            'elo'         => 1000,
            'kind'        => 'ghost',
        ];
    }

    public function test_victory_coins_are_multiplied_by_the_war_chest_level(): void
    {
        $expected = [0 => 65, 1 => 81, 2 => 98, 3 => 130]; // 65 × 1 / 1,25 / 1,5 / 2

        foreach ($expected as $level => $coins) {
            $user = User::factory()->create();
            WorkshopUpgrade::create(['user_id' => $user->id, 'arena_bonus_level' => $level]);

            $result = app(ArenaService::class)->fight($user, $this->fighter($user, 100), $this->ghost(15));

            $this->assertTrue($result['won'], 'Le champion surpuissant doit gagner ce duel.');
            $this->assertSame($coins, $result['coins'], "Coffre de Guerre niveau {$level}");
            $this->assertDatabaseHas('battles', ['player_id' => $user->id, 'coins_awarded' => $coins]);
        }
    }

    public function test_defeat_coins_are_not_multiplied(): void
    {
        $user = User::factory()->create();
        WorkshopUpgrade::create(['user_id' => $user->id, 'arena_bonus_level' => 3]);

        $result = app(ArenaService::class)->fight($user, $this->fighter($user, 15), $this->ghost(100));

        $this->assertFalse($result['won'], 'Le combattant très faible doit perdre ce duel.');
        $this->assertSame(20, $result['coins']); // Lot de défaite de base, sans bonus
    }
}
