<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Services\EloService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchmakingTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $owner, int $powerScore = 60, array $overrides = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $owner->id, 'name' => "Carte {$powerScore}", 'race_id' => 'human', 'weapon_id' => 'sword', 'power_id' => 'fire',
            'power_tier' => PowerTier::Common, 'hp' => 80, 'max_hp' => 80, 'strength' => 60, 'speed' => 60, 'defense' => 60,
            'power_score' => $powerScore, 'rarity' => Rarity::Common, 'synergies_data' => [], 'recycle_value' => 50, 'pending_reveal' => false,
        ], $overrides));
    }

    private function player(int $elo): User
    {
        return User::factory()->create(['elo' => $elo]);
    }

    private function find(Character $attackerCard): ?Character
    {
        return app(EloService::class)->findOpponent($attackerCard->user, $attackerCard);
    }

    public function test_closest_elo_window_is_searched_first(): void
    {
        $myCard = $this->card($this->player(1000));
        $close = $this->card($this->player(1080)); // dans ±100
        $this->card($this->player(1220));          // seulement dans ±250

        $this->assertTrue($close->is($this->find($myCard)));
    }

    public function test_window_widens_up_to_250_when_nobody_is_close(): void
    {
        $myCard = $this->card($this->player(1000));
        $far = $this->card($this->player(1240));

        $this->assertTrue($far->is($this->find($myCard)));
    }

    public function test_no_opponent_beyond_250(): void
    {
        $myCard = $this->card($this->player(1000));
        $this->card($this->player(1300));

        $this->assertNull($this->find($myCard));
    }

    public function test_picks_the_card_with_the_closest_power(): void
    {
        $myCard = $this->card($this->player(1000), 70);
        $opponent = $this->player(1020);
        $this->card($opponent, 40);
        $matching = $this->card($opponent, 72);
        $this->card($opponent, 95);

        $this->assertTrue($matching->is($this->find($myCard)));
    }

    public function test_own_characters_are_never_picked(): void
    {
        $me = $this->player(1000);
        $myCard = $this->card($me);
        $this->card($me, 61);

        $this->assertNull($this->find($myCard));
    }

    public function test_recycled_and_unrevealed_cards_are_ignored(): void
    {
        $myCard = $this->card($this->player(1000));
        $this->card($this->player(1010), 60, ['recycled_at' => now()]);
        $this->card($this->player(1020), 60, ['pending_reveal' => true]);

        $this->assertNull($this->find($myCard));
    }
}
