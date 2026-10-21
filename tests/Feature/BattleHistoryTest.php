<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Livewire\BattleArena;
use App\Livewire\BattleHistory;
use App\Models\Character;
use App\Models\User;
use App\Services\ArenaService;
use App\Services\BattleHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BattleHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $owner): Character
    {
        return Character::create([
            'user_id' => $owner->id, 'name' => 'Héros', 'race_id' => 'human', 'weapon_id' => 'sword', 'power_id' => 'fire',
            'power_tier' => PowerTier::Common, 'hp' => 80, 'max_hp' => 80, 'strength' => 60, 'speed' => 60, 'defense' => 60,
            'power_score' => 60, 'rarity' => Rarity::Common, 'synergies_data' => [], 'recycle_value' => 50, 'pending_reveal' => false,
        ]);
    }

    /** Joue un vrai duel contre un fantôme et le renvoie tel qu'enregistré. */
    private function playDuel(User $user): void
    {
        $arena = app(ArenaService::class);
        $fighter = $this->card($user);
        $arena->fight($user, $fighter, $arena->findOpponent($user, $fighter));
    }

    public function test_recent_lists_only_the_last_10_battles_of_the_player(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        foreach (range(1, 12) as $i) {
            $this->playDuel($me);
        }
        $this->playDuel($other);

        $history = app(BattleHistoryService::class)->recent($me);

        $this->assertCount(10, $history);
        foreach ($history as $row) {
            $this->assertContains($row['outcome'], ['win', 'loss', 'draw']);
            $this->assertSame('ghost', $row['opponent_kind']);
            $this->assertTrue($row['can_replay']);
        }
    }

    public function test_date_is_relative_and_in_french(): void
    {
        $me = User::factory()->create();
        $this->playDuel($me);

        $this->travel(5)->minutes();

        $this->assertSame('il y a 5 min', app(BattleHistoryService::class)->recent($me)[0]['ago']);
    }

    public function test_replay_reloads_the_turns_log_in_the_arena_without_cost(): void
    {
        $me = User::factory()->create(['arena_tickets' => 5]);
        $this->playDuel($me);
        $me->refresh();
        $battle = $me->battles()->first();
        $eloBefore = $me->elo;

        Livewire::actingAs($me)
            ->test(BattleArena::class)
            ->call('replayBattle', $battle->id)
            ->assertDispatched('battle-started', fn ($event, $params) => $params['log'] === $battle->turns_log && $params['replay'] === true && $params['stats'] === null)
            ->assertSet('result.replay', true)
            ->assertSet('result.elo_delta', $battle->elo_delta)
            ->assertSee('Ton combattant (replay)');

        $this->assertSame(1, $me->battles()->count(), 'Revoir un combat ne doit pas en créer un nouveau');
        $this->assertSame($eloBefore, $me->fresh()->elo);
        $this->assertSame(5, $me->fresh()->arena_tickets);
    }

    public function test_cannot_replay_another_players_battle(): void
    {
        $owner = User::factory()->create();
        $this->playDuel($owner);
        $battle = $owner->battles()->first();

        $intruder = User::factory()->create();
        $this->card($intruder);

        Livewire::actingAs($intruder)
            ->test(BattleArena::class)
            ->call('replayBattle', $battle->id)
            ->assertNotDispatched('battle-started')
            ->assertSet('result', null);
    }

    public function test_history_component_lists_battles_with_replay_button(): void
    {
        $me = User::factory()->create();
        $this->playDuel($me);

        Livewire::actingAs($me)
            ->test(BattleHistory::class)
            ->assertSee('Derniers combats')
            ->assertSee('Revoir');
    }

    public function test_history_is_shown_on_the_arena_page(): void
    {
        $me = User::factory()->create();
        $this->card($me);

        $this->actingAs($me)->get('/arena')->assertOk()->assertSee('Derniers combats');
    }
}
