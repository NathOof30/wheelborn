<?php

namespace Tests\Unit;

use App\Services\EloService;
use PHPUnit\Framework\TestCase;

class EloServiceTest extends TestCase
{
    private EloService $elo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->elo = new EloService();
    }

    public function test_expected_score_is_fifty_percent_between_equal_players(): void
    {
        $this->assertEqualsWithDelta(0.5, $this->elo->expectedScore(1000, 1000), 0.0001);
    }

    public function test_balanced_match_gives_plus_or_minus_16(): void
    {
        $this->assertSame(16, $this->elo->calculateDelta(1000, 1000, true));
        $this->assertSame(-16, $this->elo->calculateDelta(1000, 1000, false));
    }

    public function test_examples_from_game_design_document(): void
    {
        // docs/NOUVELLE_BOUCLE_GAMEPLAY.md § 5.3
        $this->assertSame(23, $this->elo->calculateDelta(1000, 1150, true));
        $this->assertSame(-9, $this->elo->calculateDelta(1000, 1150, false));
        $this->assertSame(-23, $this->elo->calculateDelta(1150, 1000, false));
    }

    public function test_win_against_stronger_player_gives_big_gain(): void
    {
        $this->assertGreaterThan(16, $this->elo->calculateDelta(1000, 1200, true));

        // Écart énorme : on gagne presque le maximum (K = 32)
        $this->assertSame(32, $this->elo->calculateDelta(1000, 1800, true));
    }

    public function test_loss_against_weaker_player_gives_big_loss(): void
    {
        $this->assertLessThan(-16, $this->elo->calculateDelta(1200, 1000, false));

        $this->assertSame(-32, $this->elo->calculateDelta(1800, 1000, false));
    }

    public function test_elo_never_goes_below_zero(): void
    {
        // 10 Élo et une défaite à -16 : on s'arrête à 0, pas à -6
        $this->assertSame(0, $this->elo->newRating(10, 10, false));
        $this->assertSame(0, $this->elo->newRating(0, 500, false));
    }

    public function test_new_rating_applies_delta(): void
    {
        $this->assertSame(1016, $this->elo->newRating(1000, 1000, true));
        $this->assertSame(984, $this->elo->newRating(1000, 1000, false));
    }
}
