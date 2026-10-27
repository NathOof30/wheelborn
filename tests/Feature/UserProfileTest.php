<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Livewire\BattleArena;
use App\Livewire\Leaderboard;
use App\Livewire\PublicPlayerProfile;
use App\Livewire\UserProfile;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(User $user, array $overrides = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Héros Test',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 40,
            'speed' => 50,
            'defense' => 30,
            'power_score' => 65,
            'rarity' => Rarity::Common,
            'recycle_value' => 50,
            'pending_reveal' => false,
            'recycled_at' => null,
            'is_favorite' => false,
        ], $overrides));
    }

    public function test_profile_page_is_displayed_with_user_profile_component(): void
    {
        $user = User::factory()->create([
            'name' => 'Astraeus',
            'xp' => 120,
            'level' => 2,
            'elo' => 1050,
            'coins' => 350,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSeeLivewire(UserProfile::class);
        $response->assertSee('Astraeus');
        $response->assertSee('Niveau 2');
        $response->assertSee('1050 Élo');
    }

    public function test_user_profile_displays_top_three_favorites_in_showcase(): void
    {
        $user = User::factory()->create();

        $this->createCharacter($user, [
            'name' => 'Solaris le Brave',
            'is_favorite' => true,
        ]);
        $this->createCharacter($user, [
            'name' => 'Lunara Mystique',
            'is_favorite' => true,
        ]);
        $this->createCharacter($user, [
            'name' => 'Guerrier Oublié',
            'is_favorite' => false,
        ]);

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->assertSee('Solaris le Brave')
            ->assertSee('Lunara Mystique')
            ->assertSee('Emplacement Libre #3');
    }

    public function test_user_profile_can_switch_tabs(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->assertSet('activeTab', 'overview')
            ->call('setTab', 'settings')
            ->assertSet('activeTab', 'settings')
            ->call('setTab', 'overview')
            ->assertSet('activeTab', 'overview');
    }

    public function test_user_can_unfavorite_card_from_showcase(): void
    {
        $user = User::factory()->create();

        $fav = $this->createCharacter($user, [
            'name' => 'Solaris',
            'is_favorite' => true,
        ]);

        $this->assertTrue($fav->fresh()->is_favorite);

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->call('removeFavorite', (string) $fav->id);

        $this->assertFalse($fav->fresh()->is_favorite);
    }

    public function test_user_can_inspect_card_and_close_inspector(): void
    {
        $user = User::factory()->create();

        $card = $this->createCharacter($user, [
            'name' => 'Cosmo Guardian',
            'is_favorite' => true,
        ]);

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->call('inspectCharacter', (string) $card->id)
            ->assertSet('inspectedCharacterId', (string) $card->id)
            ->call('closeInspector')
            ->assertSet('inspectedCharacterId', null);
    }

    public function test_public_player_profile_is_accessible_and_shows_player_info(): void
    {
        $currentUser = User::factory()->create(['name' => 'HeroOne']);
        $rival = User::factory()->create([
            'name' => 'ChampionRiv',
            'level' => 3,
            'elo' => 1250,
            'xp' => 350,
        ]);

        $this->createCharacter($rival, [
            'name' => 'Excalibur Divine',
            'is_favorite' => true,
        ]);

        $response = $this
            ->actingAs($currentUser)
            ->get('/players/' . $rival->name);

        $response->assertOk();
        $response->assertSeeLivewire(PublicPlayerProfile::class);
        $response->assertSee('ChampionRiv');
        $response->assertSee('Niveau 3');
        $response->assertSee('1250 Élo');
        $response->assertSee('Excalibur Divine');
        // Do not display Breeze account management forms
        $response->assertDontSee('Mettre à jour le mot de passe');
        $response->assertDontSee('Supprimer le compte');
    }

    public function test_battle_arena_rival_inspection_modal_for_real_player(): void
    {
        $user = User::factory()->create(['name' => 'Duelist']);
        $this->createCharacter($user, ['name' => 'Mon Combattant']);

        $rival = User::factory()->create([
            'name' => 'RivalKing',
            'level' => 4,
            'elo' => 1300,
        ]);
        $rivalCard = $this->createCharacter($rival, [
            'name' => 'Titan Protecteur',
            'is_favorite' => true,
        ]);

        session(['arena_current_opponent' => [
            'id' => $rivalCard->id,
            'name' => $rivalCard->name,
            'owner' => $rival->name,
            'kind' => 'player',
            'elo' => 1300,
            'power_score' => 85,
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'rarity' => 'epic',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
        ]]);

        Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->call('inspectOpponent')
            ->assertSet('showOpponentModal', true)
            ->assertSee('RivalKing')
            ->assertSee('Titan Protecteur')
            ->call('closeOpponentModal')
            ->assertSet('showOpponentModal', false)
            ->assertSet('inspectedRival', null);
    }

    public function test_battle_arena_rival_inspection_modal_for_ghost_bot(): void
    {
        $user = User::factory()->create(['name' => 'DuelistBot']);
        $this->createCharacter($user, ['name' => 'Mon Combattant']);

        session(['arena_current_opponent' => [
            'id' => 'ghost_123',
            'name' => 'Gladiateur du Néant',
            'kind' => 'ghost',
            'elo' => 1020,
            'power_score' => 70,
            'race_id' => 'orc',
            'weapon_id' => 'axe',
            'power_id' => 'earth',
            'rarity' => 'common',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 45,
            'speed' => 40,
            'defense' => 40,
        ]]);

        Livewire::actingAs($user)
            ->test(BattleArena::class)
            ->call('inspectOpponent')
            ->assertSet('showOpponentModal', true)
            ->assertSee('Gladiateur du Néant')
            ->assertSee('Entité Astrale')
            ->call('closeOpponentModal')
            ->assertSet('showOpponentModal', false);
    }

    public function test_leaderboard_player_inspection_modal(): void
    {
        $user = User::factory()->create(['name' => 'RankHunter', 'is_admin' => false]);
        $rival = User::factory()->create([
            'name' => 'TopLeader',
            'elo' => 1400,
            'is_admin' => false,
        ]);
        $this->createCharacter($rival, [
            'name' => 'Faucheur Spectral',
            'is_favorite' => true,
        ]);

        Livewire::actingAs($user)
            ->test(Leaderboard::class)
            ->call('inspectPlayer', $rival->id)
            ->assertSet('showInspectorModal', true)
            ->assertSee('TopLeader')
            ->assertSee('Faucheur Spectral')
            ->call('closeInspector')
            ->assertSet('showInspectorModal', false)
            ->assertSet('inspectedPlayer', null);
    }

    public function test_user_profile_accurately_calculates_and_displays_duels_wins_losses_and_winrate(): void
    {
        $user = User::factory()->create(['name' => 'GladiatorX']);
        $myHero = $this->createCharacter($user, ['name' => 'Mon Champion']);

        $rival = User::factory()->create(['name' => 'Nemesis']);
        $rivalHero = $this->createCharacter($rival, ['name' => 'Ennemi Létal']);

        // 1. Victoire du joueur
        Battle::create([
            'player_id' => $user->id,
            'challenger_id' => $myHero->id,
            'opponent_id' => $rivalHero->id,
            'opponent_name' => $rival->name,
            'opponent_elo' => 1000,
            'winner_id' => $myHero->id,
            'seed' => 12345,
            'turns_count' => 3,
            'player_old_elo' => 1000,
            'player_new_elo' => 1020,
            'elo_delta' => 20,
        ]);

        // 2. Défaite du joueur
        Battle::create([
            'player_id' => $user->id,
            'challenger_id' => $myHero->id,
            'opponent_id' => $rivalHero->id,
            'opponent_name' => $rival->name,
            'opponent_elo' => 1000,
            'winner_id' => $rivalHero->id,
            'seed' => 12346,
            'turns_count' => 4,
            'player_old_elo' => 1020,
            'player_new_elo' => 1005,
            'elo_delta' => -15,
        ]);

        // 3. Match nul (winner_id = null)
        Battle::create([
            'player_id' => $user->id,
            'challenger_id' => $myHero->id,
            'opponent_id' => $rivalHero->id,
            'opponent_name' => $rival->name,
            'opponent_elo' => 1000,
            'winner_id' => null,
            'seed' => 12347,
            'turns_count' => 5,
            'player_old_elo' => 1005,
            'player_new_elo' => 1005,
            'elo_delta' => 0,
        ]);

        $stats = $user->battleStats();
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['wins']);
        $this->assertEquals(1, $stats['losses']);
        $this->assertEquals(1, $stats['draws']);
        $this->assertEquals(33, $stats['winrate']); // 1/3 = 33%

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->assertSee('Performances Arène 1v1')
            ->assertSee('33%');
    }

    public function test_user_profile_modal_opens_full_detailed_sheet_with_passives_and_synergies(): void
    {
        $user = User::factory()->create();

        $hero = Character::create([
            'user_id' => $user->id,
            'name' => 'Aurelius Solaire',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Rare,
            'hp' => 110,
            'max_hp' => 110,
            'strength' => 50,
            'speed' => 50,
            'defense' => 40,
            'power_score' => 75,
            'rarity' => Rarity::Rare,
            'recycle_value' => 150,
            'is_favorite' => true,
            'is_locked' => false,
            'wins' => 5,
            'losses' => 1,
            'synergies_data' => [
                [
                    'name' => 'Lame Embrasée',
                    'icon' => '🗡️🔥',
                    'description' => 'Brûle la cible au combat.',
                    'is_unstable' => false,
                ]
            ],
            'pending_reveal' => false,
        ]);

        Livewire::actingAs($user)
            ->test(UserProfile::class)
            ->call('inspectCharacter', $hero->id)
            ->assertSet('inspectedCharacterId', $hero->id)
            ->assertSee('Aurelius Solaire')
            ->assertSee('Passifs Innés')
            ->assertSee('Synergies Actives')
            ->assertSee('Lame Embrasée')
            ->assertSee('Brûle la cible au combat')
            ->assertSee('83%') // 5/6 = 83%
            ->assertSee('Fermer la fiche')
            ->call('closeInspector')
            ->assertSet('inspectedCharacterId', null);
    }
}

