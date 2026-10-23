<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Events\ArenaBattleFinished;
use App\Events\CharacterGenerated;
use App\Events\CharactersRecycled;
use App\Livewire\BattleArena;
use App\Models\Achievement;
use App\Models\Character;
use App\Models\User;
use App\Services\CharacterGeneratorService;
use App\Services\RecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T4.5 : succès débloqués par les événements de jeu, avec une bourse de pièces versée une seule fois.
 */
class AchievementTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $user, array $overrides = []): Character
    {
        return Character::create($overrides + [
            'user_id'           => $user->id,
            'name'              => 'Héros de test',
            'race_id'           => 'human',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => PowerTier::Common,
            'hp'                => 80,
            'max_hp'            => 80,
            'strength'          => 50,
            'speed'             => 50,
            'defense'           => 50,
            'power_score'       => 60,
            'rarity'            => Rarity::Common,
            'is_unstable'       => false,
            'synergies_data'    => [],
            'recycle_value'     => 40,
            'pending_reveal'    => false,
            'is_active_fighter' => false,
            'is_locked'         => false,
        ]);
    }

    private function assertUnlocked(User $user, string $key): void
    {
        $this->assertDatabaseHas('achievements', ['user_id' => $user->id, 'achievement_key' => $key]);
    }

    private function assertNotUnlocked(User $user, string $key): void
    {
        $this->assertDatabaseMissing('achievements', ['user_id' => $user->id, 'achievement_key' => $key]);
    }

    // --- Premier Sang ---

    public function test_first_arena_win_unlocks_first_blood_with_its_reward(): void
    {
        $user = User::factory()->create(['coins' => 500]);

        ArenaBattleFinished::dispatch($user, false, false); // Défaite : rien
        ArenaBattleFinished::dispatch($user, false, true);  // Match nul : rien
        $this->assertNotUnlocked($user, 'first_blood');

        ArenaBattleFinished::dispatch($user, true, false);

        $this->assertUnlocked($user, 'first_blood');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 600]);
        $this->assertSame(600, $user->coins); // Le modèle de l'appelant inclut la bourse
    }

    public function test_reward_is_paid_only_once(): void
    {
        $user = User::factory()->create(['coins' => 500]);

        ArenaBattleFinished::dispatch($user, true, false);
        ArenaBattleFinished::dispatch($user, true, false);

        $this->assertSame(1, Achievement::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 600]);
        $this->assertFalse(Achievement::unlock($user, 'first_blood'));
    }

    public function test_arena_fight_dispatches_the_battle_event(): void
    {
        Event::fake([ArenaBattleFinished::class]);

        $user = User::factory()->create();
        $this->card($user, ['name' => 'Conan']);

        Livewire::actingAs($user)->test(BattleArena::class)->call('fight');

        Event::assertDispatched(ArenaBattleFinished::class, fn ($event) => $event->user->is($user));
    }

    // --- Maître Alchimiste ---

    public function test_tenth_recycled_card_unlocks_master_alchemist(): void
    {
        $user = User::factory()->create(['coins' => 0]);
        $cards = collect(range(1, 10))->map(fn () => $this->card($user));
        $recycler = app(RecycleService::class);

        $cards->take(9)->each(fn ($card) => $recycler->recycle($user, $card->id));
        $this->assertNotUnlocked($user, 'master_alchemist');

        $recycler->recycle($user, $cards->last()->id);

        $this->assertUnlocked($user, 'master_alchemist');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 10 * 40 + 150]);
    }

    public function test_batch_recycling_counts_towards_master_alchemist(): void
    {
        Event::fake([CharactersRecycled::class]);
        $user = User::factory()->create();
        collect(range(1, 10))->each(fn () => $this->card($user));

        app(RecycleService::class)->recycleBatch($user, ['rarities' => ['common']]);

        Event::assertDispatched(CharactersRecycled::class, fn ($event) => $event->count === 10);
    }

    public function test_batch_of_ten_unlocks_master_alchemist(): void
    {
        $user = User::factory()->create(['coins' => 0]);
        collect(range(1, 10))->each(fn () => $this->card($user));

        $result = app(RecycleService::class)->recycleBatch($user, ['rarities' => ['common']]);

        $this->assertSame(10, $result['count']);
        $this->assertUnlocked($user, 'master_alchemist');
    }

    // --- Bénédiction Divine & Combinaison Instable ---

    public function test_legendary_draw_unlocks_divine_blessing(): void
    {
        $user = User::factory()->create(['coins' => 0]);

        CharacterGenerated::dispatch($user, $this->card($user, ['rarity' => Rarity::Epic]));
        $this->assertNotUnlocked($user, 'divine_blessing');

        CharacterGenerated::dispatch($user, $this->card($user, ['rarity' => Rarity::Legendary]));

        $this->assertUnlocked($user, 'divine_blessing');
        $this->assertNotUnlocked($user, 'unstable_combination');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 250]);
    }

    public function test_unstable_synergy_draw_unlocks_unstable_combination(): void
    {
        $user = User::factory()->create(['coins' => 0]);

        CharacterGenerated::dispatch($user, $this->card($user, ['is_unstable' => true]));

        $this->assertUnlocked($user, 'unstable_combination');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coins' => 150]);
    }

    public function test_wheel_draw_dispatches_the_generation_event(): void
    {
        Event::fake([CharacterGenerated::class]);
        $user = User::factory()->create();

        $character = app(CharacterGeneratorService::class)->generate($user);

        Event::assertDispatched(CharacterGenerated::class, fn ($event) => $event->character->is($character));
    }

    public function test_arena_bots_do_not_trigger_achievements(): void
    {
        Event::fake([CharacterGenerated::class]);

        app(CharacterGeneratorService::class)->generateBot();

        Event::assertNotDispatched(CharacterGenerated::class);
    }

    public function test_catalogue_defines_the_four_achievements_with_title_and_reward(): void
    {
        $this->assertSame(
            ['first_blood', 'master_alchemist', 'divine_blessing', 'unstable_combination'],
            array_keys(Achievement::DEFINITIONS),
        );

        foreach (Achievement::DEFINITIONS as $definition) {
            $this->assertNotEmpty($definition['title']);
            $this->assertGreaterThan(0, $definition['reward']);
        }
    }
}
