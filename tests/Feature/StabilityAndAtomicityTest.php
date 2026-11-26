<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Livewire\BattleArena;
use App\Livewire\CharacterWheel;
use App\Livewire\CollectionGrid;
use App\Livewire\PlayerHud;
use App\Livewire\PublicPlayerProfile;
use App\Livewire\UserProfile;
use App\Models\Achievement;
use App\Models\Character;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use App\Services\ArenaService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class StabilityAndAtomicityTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(User $user, array $overrides = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Valérian',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 60,
            'speed' => 55,
            'defense' => 50,
            'power_score' => 70,
            'rarity' => Rarity::Rare,
            'recycle_value' => 95,
            'is_locked' => false,
            'is_favorite' => false,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ], $overrides));
    }

    public function test_user_profile_recycle_character_credits_coins_and_is_idempotent(): void
    {
        $user = User::factory()->create(['coins' => 200]);
        $hero = $this->createCharacter($user, ['name' => 'Cible Recyclage', 'recycle_value' => 85]);

        $component = Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->call('recycleCharacter', (string) $hero->id)
            ->assertDispatched('stats-updated');

        $this->assertNotNull($hero->fresh()->recycled_at);
        $this->assertSame(285, $user->fresh()->coins);

        $component->call('recycleCharacter', (string) $hero->id);
        $this->assertSame(285, $user->fresh()->coins);
    }

    public function test_public_player_profile_reads_real_achievements_workshop_and_excludes_pending_reveal(): void
    {
        $viewer = User::factory()->create(['name' => 'Visiteur']);
        $target = User::factory()->create(['name' => 'Champion', 'elo' => 1450]);

        $this->createCharacter($target, ['name' => 'Héros Révélé', 'rarity' => Rarity::Legendary, 'is_favorite' => true]);
        $this->createCharacter($target, ['name' => 'Héros Orphelin', 'rarity' => Rarity::Legendary, 'pending_reveal' => true]);

        WorkshopUpgrade::create([
            'user_id' => $target->id,
            'rare_magnet_level' => 2,
            'hero_forge_level' => 3,
            'arena_bonus_level' => 1,
        ]);

        Achievement::create([
            'user_id' => $target->id,
            'achievement_key' => 'first_blood',
            'unlocked_at' => now(),
        ]);

        Livewire::actingAs($viewer)
            ->test(PublicPlayerProfile::class, ['user' => $target])
            ->assertSee('Héros Révélé')
            ->assertDontSee('Héros Orphelin')
            ->assertSee('Premier Sang')
            ->assertSee('1 / 4 accomplis')
            ->assertSee('Niveau 6')
            ->assertSee('Or')
            ->assertDontSee('Gold')
            ->assertSee('Humain')
            ->assertSee('Épée')
            ->assertSee('Feu');
    }

    public function test_character_label_accessors_resolve_french_names(): void
    {
        $user = User::factory()->create();
        $hero = $this->createCharacter($user, [
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'ice',
        ]);

        $this->assertSame('Elfe', $hero->race_label);
        $this->assertSame('Arc', $hero->weapon_label);
        $this->assertSame('Glace', $hero->power_label);
    }

    public function test_arena_battle_snapshot_preserves_rarity_for_replays(): void
    {
        $user = User::factory()->create();
        $hero = $this->createCharacter($user, ['rarity' => Rarity::Legendary]);

        $arena = app(ArenaService::class);
        $opponent = $arena->findOpponent($user, $hero);
        $arena->fight($user, $hero, $opponent);

        $battle = $user->battles()->latest('id')->first();
        $this->assertNotNull($battle);
        $this->assertSame('legendary', $battle->fighters_snapshot['challenger']['rarity'] ?? null);
        $this->assertNotEmpty($battle->fighters_snapshot['opponent']['rarity'] ?? null);
    }

    public function test_player_hud_regenerates_arena_tickets_and_exposes_max(): void
    {
        $user = User::factory()->create([
            'arena_tickets' => 2,
            'last_arena_regen_at' => now()->subMinutes(15),
            'is_admin' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PlayerHud::class)
            ->assertSet('arenaTickets', 5)
            ->assertSet('maxTickets', 5);

        $this->assertSame(5, $user->fresh()->arena_tickets);
    }

    public function test_user_profile_excludes_pending_reveal_from_collection_counts(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, ['name' => 'Révélé', 'rarity' => Rarity::Legendary]);
        $this->createCharacter($user, ['name' => 'En Attente', 'rarity' => Rarity::Legendary, 'pending_reveal' => true]);

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->assertViewHas('heroesCount', 1)
            ->assertViewHas('legendariesCount', 1);
    }

    public function test_battle_arena_reroll_with_insufficient_coins_twice_never_goes_negative(): void
    {
        $user = User::factory()->create(['coins' => 5, 'has_free_arena_reroll' => false]);
        $this->createCharacter($user);

        $component = Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->call('rerollOpponent')
            ->call('rerollOpponent');

        $this->assertSame(5, $user->fresh()->coins);
        $component->assertSee('Pas assez de pièces');
    }

    public function test_non_admin_cannot_escalate_privileges_or_invoke_admin_wheel_actions(): void
    {
        $player = User::factory()->create([
            'coins' => 250,
            'pity_counter' => 0,
            'is_admin' => false,
        ]);

        Livewire::actingAs($player)
            ->test(CharacterWheel::class)
            ->call('adminAddCoins', 50000)
            ->call('adminTriggerSuperSpin')
            ->call('toggleAdminForge')
            ->call('adminSpawnCustom', false)
            ->call('adminForceTestSpin', 'sword', 'fire')
            ->assertSet('isAdmin', false)
            ->assertSet('showAdminForge', false)
            ->assertSet('revealedCharacter', null);

        $player->refresh();
        $this->assertSame(250, $player->coins);
        $this->assertSame(0, $player->pity_counter);
        $this->assertDatabaseCount('characters', 0);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($player)
            ->test(CharacterWheel::class)
            ->set('isAdmin', true);
    }

    public function test_battle_arena_fight_blocks_duplicate_execution_before_rematch(): void
    {
        $user = User::factory()->create(['arena_tickets' => 5, 'is_admin' => false]);
        $this->createCharacter($user, ['name' => 'Duelliste']);

        Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->call('fight')
            ->call('fight');

        $this->assertSame(1, $user->battles()->count());
        $this->assertSame(4, $user->fresh()->arena_tickets);
    }

    public function test_arena_fight_with_ticket_rejects_recycled_fighter_without_consuming_ticket(): void
    {
        $user = User::factory()->create(['arena_tickets' => 5, 'is_admin' => false]);
        $hero = $this->createCharacter($user, ['name' => 'Recyclé']);
        $arena = app(ArenaService::class);
        $opponent = $arena->findOpponent($user, $hero);

        $hero->update(['recycled_at' => now()]);

        try {
            $arena->fightWithTicket($user, $hero, $opponent);
            $this->fail('Un personnage recyclé ne devrait pas pouvoir combattre.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('plus disponible', $e->getMessage());
        }

        $this->assertSame(5, $user->fresh()->arena_tickets);
        $this->assertSame(0, $user->battles()->count());
    }

    public function test_arena_player_opponent_preserves_combat_record_and_lucky_spin_for_card(): void
    {
        $player = User::factory()->create(['elo' => 1200]);
        $rival = User::factory()->create(['elo' => 1200, 'name' => 'RivalPalmares']);

        $hero = $this->createCharacter($player, ['power_score' => 80]);
        $this->createCharacter($rival, [
            'name' => 'Champion Rival',
            'power_score' => 80,
            'wins' => 11,
            'losses' => 4,
            'is_lucky_spin' => true,
        ]);

        $opponent = app(ArenaService::class)->findOpponent($player, $hero);

        $this->assertSame('player', $opponent['kind']);
        $this->assertSame(11, $opponent['wins']);
        $this->assertSame(4, $opponent['losses']);
        $this->assertTrue((bool) $opponent['is_lucky_spin']);
    }

    public function test_collection_grid_renders_french_rarity_label_in_active_filter_badge(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, ['rarity' => Rarity::Legendary]);

        Livewire::actingAs($user)
            ->test(CollectionGrid::class)
            ->call('setRarity', 'legendary')
            ->assertSee('Rareté : Légendaire')
            ->assertDontSee('Rareté : Legendary');
    }
}
