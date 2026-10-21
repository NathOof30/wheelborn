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

    public function test_massive_power_advantage_dampens_win_elo_gain_anti_farm(): void
    {
        // 1000 vs 1000 : gain de base = +16
        // Challenger a le double de puissance (120 vs 60) : ratio = 2.0 -> mult = 0.40 -> +6 Élo
        $this->assertSame(6, $this->elo->calculateDelta(1000, 1000, true, 120, 60));
        $this->assertLessThan(10, $this->elo->calculateDelta(1000, 1000, true, 120, 60));
    }

    public function test_underdog_win_boosts_elo_gain_for_skill(): void
    {
        // Challenger outsider avec carte 2x plus faible (60 vs 120) : ratio = 0.5 -> mult = 1.50 -> +24 Élo
        $this->assertSame(24, $this->elo->calculateDelta(1000, 1000, true, 60, 120));
        $this->assertGreaterThan(20, $this->elo->calculateDelta(1000, 1000, true, 60, 120));
    }

    public function test_underdog_defeat_mitigates_elo_loss(): void
    {
        // Défaite prévisible face à un colosse (60 vs 120) : perte atténuée à -11 au lieu de -16
        $this->assertSame(-11, $this->elo->calculateDelta(1000, 1000, false, 60, 120));
    }

    public function test_overpowered_card_defeat_penalizes_severely(): void
    {
        // Défaite impardonnable avec le double de puissance (120 vs 60) : sanction amplifiée à -24
        $this->assertSame(-24, $this->elo->calculateDelta(1000, 1000, false, 120, 60));
    }
}
