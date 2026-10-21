<?php

namespace Tests\Feature;

use App\Enums\League;
use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use App\Services\ArenaService;
use App\Services\BotGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArenaGhostTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $owner, int $powerScore = 60): Character
    {
        return Character::create([
            'user_id' => $owner->id, 'name' => "Carte {$powerScore}", 'race_id' => 'human', 'weapon_id' => 'sword', 'power_id' => 'fire',
            'power_tier' => PowerTier::Common, 'hp' => 80, 'max_hp' => 80, 'strength' => 60, 'speed' => 60, 'defense' => 60,
            'power_score' => $powerScore, 'rarity' => Rarity::Common, 'synergies_data' => [], 'recycle_value' => 50, 'pending_reveal' => false,
        ]);
    }

    public function test_ghost_is_generated_when_no_real_player_is_in_range(): void
    {
        $user = User::factory()->create(['elo' => 1000]);

        $opponent = app(ArenaService::class)->findOpponent($user, $this->card($user));

        $this->assertSame('ghost', $opponent['kind']);
    }

    public function test_real_player_is_preferred_over_a_ghost(): void
    {
        $user = User::factory()->create(['elo' => 1000]);
        $other = User::factory()->create(['elo' => 1050, 'name' => 'Rival']);
        $rivalCard = $this->card($other);

        $opponent = app(ArenaService::class)->findOpponent($user, $this->card($user));

        $this->assertSame('player', $opponent['kind']);
        $this->assertSame($rivalCard->id, $opponent['id']);
        $this->assertSame('Rival', $opponent['owner']);
        $this->assertSame(1050, $opponent['elo']);
    }

    public function test_ghost_has_a_procedural_name(): void
    {
        $ghost = app(BotGeneratorService::class)->generateGhost(User::factory()->create());

        // Un titre + une épithète, ex : "Gladiateur de l'Ombre"
        $this->assertMatchesRegularExpression('/^\S+ .+$/u', $ghost['name']);
    }

    public function test_ghost_elo_stays_inside_the_player_league(): void
    {
        // 1420 Élo = ligue Or (1400-1799) : le fantôme ne doit jamais descendre sous 1400
        $user = User::factory()->create(['elo' => 1420]);
        $generator = app(BotGeneratorService::class);

        foreach (range(1, 30) as $i) {
            $this->assertSame(League::Gold, League::fromElo($generator->generateGhost($user)['elo']));
        }
    }

    public function test_elo_applies_normally_against_a_ghost_and_the_real_owner_is_untouched(): void
    {
        $user = User::factory()->create(['elo' => 1000]);
        $other = User::factory()->create(['elo' => 1000]);
        $this->card($other);
        $arena = app(ArenaService::class);

        // Contre un vrai joueur : opponent_id enregistré, son Élo ne bouge pas
        $result = $arena->fight($user, $this->card($user), $arena->findOpponent($user, $this->card($user)));

        $this->assertSame(1000 + $result['elo_delta'], $user->fresh()->elo);
        $this->assertSame(1000, $other->fresh()->elo);
        $this->assertNotNull(Battle::sole()->opponent_id);
    }

    public function test_anti_farm_cooldown_breaks_infinite_single_card_farming_loop(): void
    {
        $user = User::factory()->create(['elo' => 1000]);
        $rival = User::factory()->create(['elo' => 1000, 'name' => 'SeulRival']);
        $rivalCard = $this->card($rival);
        $myCard = $this->card($user);
        $arena = app(ArenaService::class);

        // 1er combat : Rival est sélectionné
        $opponent1 = $arena->findOpponent($user, $myCard);
        $this->assertSame('player', $opponent1['kind']);
        $this->assertSame($rivalCard->id, $opponent1['id']);

        // Duel joué et enregistré
        $arena->fight($user, $myCard, $opponent1);

        // 2e combat : la carte de Rival vient d'être combattue. L'anti-farm active la rotation vers un bot
        $opponent2 = $arena->findOpponent($user, $myCard);
        $this->assertSame('ghost', $opponent2['kind'], 'Le système doit proposer un fantôme pour empêcher le farm en boucle sur la seule carte de l\'autre compte');
    }

    public function test_reroll_opponent_excludes_current_candidate(): void
    {
        $user = User::factory()->create(['elo' => 1000]);
        $myCard = $this->card($user);
        $arena = app(ArenaService::class);

        $first = $arena->findOpponent($user, $myCard);
        $second = $arena->findOpponent($user, $myCard, excludeOpponentId: $first['id']);

        $this->assertNotSame($first['id'], $second['id']);
    }

    public function test_rerolling_with_few_real_cards_draws_ghost_bots_from_hybrid_pool(): void
    {
        $user = User::factory()->create(['elo' => 1000]);
        $rival = User::factory()->create(['elo' => 1000]);
        $this->card($rival, 60);
        $this->card($rival, 70);
        $myCard = $this->card($user, 65);
        $arena = app(ArenaService::class);

        // Proposition initiale : vrai joueur
        $initial = $arena->findOpponent($user, $myCard);
        $this->assertSame('player', $initial['kind']);

        // Reroll en boucle : le système ne doit pas rester coincé sur les 2 cartes et doit proposer des bots
        $sawGhost = false;
        $current = $initial;
        for ($i = 0; $i < 10; $i++) {
            $next = $arena->findOpponent($user, $myCard, excludeOpponentId: $current['id']);
            if ($next['kind'] === 'ghost') {
                $sawGhost = true;
                break;
            }
            $current = $next;
        }

        $this->assertTrue($sawGhost, 'Le joueur doit pouvoir rencontrer des bots fantômes lors des rerolls même si un rival possède 2 cartes');
    }
}
