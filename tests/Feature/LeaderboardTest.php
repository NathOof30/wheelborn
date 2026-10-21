<?php

namespace Tests\Feature;

use App\Enums\League;
use App\Livewire\Leaderboard;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_requires_authentication(): void
    {
        $this->get('/leaderboard')->assertRedirect('/login');
    }

    public function test_page_is_accessible(): void
    {
        $this->actingAs(User::factory()->create())->get('/leaderboard')->assertOk()->assertSee("Classement de l'Arène", false);
    }

    public function test_players_are_ranked_by_elo_and_admins_are_excluded(): void
    {
        User::factory()->create(['name' => 'Bronze', 'elo' => 900]);
        User::factory()->create(['name' => 'Maitre', 'elo' => 2300]);
        User::factory()->create(['name' => 'Or', 'elo' => 1500]);
        User::factory()->create(['name' => 'Admin', 'elo' => 9999, 'is_admin' => true]);

        $players = app(LeaderboardService::class)->top();

        $this->assertSame(['Maitre', 'Or', 'Bronze'], array_column($players, 'name'));
        $this->assertSame([1, 2, 3], array_column($players, 'rank'));
    }

    public function test_equal_elo_shares_the_same_rank(): void
    {
        User::factory()->create(['elo' => 1500]);
        User::factory()->create(['elo' => 1200]);
        User::factory()->create(['elo' => 1200]);
        User::factory()->create(['elo' => 1000]);

        $this->assertSame([1, 2, 2, 4], array_column(app(LeaderboardService::class)->top(), 'rank'));
    }

    public function test_league_filter_keeps_global_ranks(): void
    {
        User::factory()->create(['elo' => 2300]);
        User::factory()->create(['name' => 'Or1', 'elo' => 1600]);
        User::factory()->create(['name' => 'Or2', 'elo' => 1450]);
        User::factory()->create(['elo' => 1000]);

        $gold = app(LeaderboardService::class)->top(League::Gold);

        $this->assertSame(['Or1', 'Or2'], array_column($gold, 'name'));
        $this->assertSame([2, 3], array_column($gold, 'rank'));
    }

    public function test_result_is_cached_for_60_seconds(): void
    {
        User::factory()->create(['elo' => 1000]);
        $service = app(LeaderboardService::class);

        $this->assertCount(1, $service->top());

        User::factory()->create(['elo' => 1100]);
        $this->assertCount(1, $service->top(), 'Le cache doit encore servir l\'ancien classement');

        $this->travel(61)->seconds();
        $this->assertCount(2, $service->top());
    }

    public function test_pinned_row_shows_rank_elo_and_league_of_current_player(): void
    {
        User::factory()->create(['elo' => 1500]);
        $me = User::factory()->create(['elo' => 1450, 'name' => 'Moi']);

        Livewire::actingAs($me)
            ->test(Leaderboard::class)
            ->assertSet('currentPlayer.rank', 2)
            ->assertSet('currentPlayer.elo', 1450)
            ->assertSet('currentPlayer.league.label', 'Or');
    }

    public function test_podium_is_in_visual_order_second_first_third(): void
    {
        User::factory()->create(['name' => 'Premier', 'elo' => 1500]);
        User::factory()->create(['name' => 'Deuxieme', 'elo' => 1400]);
        User::factory()->create(['name' => 'Troisieme', 'elo' => 1300]);

        $podium = Livewire::actingAs(User::factory()->create(['elo' => 100]))->test(Leaderboard::class)->get('podium');

        $this->assertSame(['Deuxieme', 'Premier', 'Troisieme'], array_column($podium, 'name'));
        $this->assertSame([2, 1, 3], array_column($podium, 'place'));
    }

    public function test_league_tabs_filter_the_list(): void
    {
        User::factory()->create(['name' => 'Joueur Or', 'elo' => 1500]);
        User::factory()->create(['name' => 'Joueur Bronze', 'elo' => 500]);
        Cache::flush();

        Livewire::actingAs(User::factory()->create(['elo' => 1000]))
            ->test(Leaderboard::class)
            ->call('setLeague', 'gold')
            ->assertSee('Joueur Or')
            ->assertDontSee('Joueur Bronze');
    }
}
