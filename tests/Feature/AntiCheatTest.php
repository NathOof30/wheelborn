<?php

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Livewire\BattleArena;
use App\Livewire\CharacterWheel;
use App\Livewire\CollectionGrid;
use App\Models\Battle;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Audit, failles 1 à 5 : le navigateur ne peut plus modifier l'état sensible des composants
 * ni appeler les méthodes qui contournent les règles du jeu.
 */
class AntiCheatTest extends TestCase
{
    use RefreshDatabase;

    private function card(User $user): Character
    {
        return Character::create([
            'user_id' => $user->id, 'name' => 'Carte', 'race_id' => 'human', 'weapon_id' => 'sword', 'power_id' => 'fire',
            'power_tier' => PowerTier::Common, 'hp' => 80, 'max_hp' => 80, 'strength' => 50, 'speed' => 50, 'defense' => 50,
            'power_score' => 60, 'rarity' => Rarity::Common, 'synergies_data' => [], 'recycle_value' => 40,
            'pending_reveal' => false, 'is_active_fighter' => false, 'is_locked' => false,
        ]);
    }

    // --- Faille 1 : se déclarer admin ---

    public function test_player_cannot_declare_himself_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($user)->test(CharacterWheel::class)->set('isAdmin', true);
    }

    public function test_admin_tools_check_the_real_account(): void
    {
        $user = User::factory()->create(['coins' => 250, 'pity_counter' => 0, 'is_admin' => false]);

        // Même appelés directement, les outils admin ne font rien pour un joueur
        Livewire::actingAs($user)->test(CharacterWheel::class)
            ->call('adminAddCoins', 5000000)
            ->call('adminTriggerSuperSpin')
            ->call('adminSpawnCustom', false);

        $user->refresh();
        $this->assertSame(250, $user->coins);
        $this->assertSame(0, $user->pity_counter);
        $this->assertDatabaseCount('characters', 0);
    }

    public function test_real_admin_still_has_his_tools(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(CharacterWheel::class)->call('adminAddCoins', 1000);

        $this->assertSame(999999 + 1000, $admin->fresh()->coins);
    }

    // --- Faille 2 : abaisser le seuil du Super Spin ---

    public function test_player_cannot_lower_the_super_spin_threshold(): void
    {
        $user = User::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($user)->test(CharacterWheel::class)->set('pityMax', -1);
    }

    public function test_normal_spin_is_not_a_super_spin(): void
    {
        $user = User::factory()->create(['spin_charges' => 3, 'pity_counter' => 0]);

        Livewire::actingAs($user)->test(CharacterWheel::class)->call('spin');

        $this->assertFalse((bool) Character::where('user_id', $user->id)->first()->is_lucky_spin);
    }

    // --- Faille 3 : changer d'adversaire sans payer ---

    public function test_free_new_opponent_method_is_not_callable(): void
    {
        $user = User::factory()->create();
        $this->card($user);

        $this->expectException(MethodNotFoundException::class); // Méthode privée : Livewire refuse l'appel

        Livewire::actingAs($user)->test(BattleArena::class)->call('newOpponent');
    }

    public function test_reselecting_the_same_fighter_does_not_change_the_opponent(): void
    {
        $user = User::factory()->create();
        $card = $this->card($user);

        $component = Livewire::actingAs($user)->test(BattleArena::class);
        $opponentId = $component->get('opponent')['id'];

        $component->call('selectFighter', $card->id);

        $this->assertSame($opponentId, $component->get('opponent')['id']);
    }

    public function test_after_a_fight_another_opponent_uses_the_free_reroll_then_costs_coins(): void
    {
        $user = User::factory()->create(['coins' => 100, 'arena_tickets' => 5]);
        $this->card($user);

        $component = Livewire::actingAs($user)->test(BattleArena::class)->call('fight');
        $coinsAfterFight = $user->fresh()->coins; // Gains du duel (et éventuel succès) déjà crédités

        // Bouton « Autre adversaire » : gratuit juste après un combat
        $component->call('rerollOpponent')->assertSet('result', null);
        $this->assertSame($coinsAfterFight, $user->fresh()->coins);
        $this->assertFalse($user->fresh()->has_free_arena_reroll);

        // Le changement suivant est payant
        $component->call('rerollOpponent');
        $this->assertSame($coinsAfterFight - 10, $user->fresh()->coins);
    }

    // --- Faille 4 : combattre l'adversaire d'un replay ---

    public function test_cannot_fight_during_a_replay(): void
    {
        $user = User::factory()->create(['arena_tickets' => 5]);
        $this->card($user);

        $component = Livewire::actingAs($user)->test(BattleArena::class)->call('fight');
        $battle = Battle::first();

        $component->call('replayBattle', $battle->id)
            ->call('fight')
            ->assertSet('error', 'Quitte le replay avant de lancer un vrai combat.');

        $this->assertSame(1, Battle::count());
    }

    public function test_leaving_a_replay_restores_the_real_opponent(): void
    {
        $user = User::factory()->create(['arena_tickets' => 5]);
        $this->card($user);

        $component = Livewire::actingAs($user)->test(BattleArena::class)->call('fight');
        $realOpponent = $component->get('opponent');
        $battle = Battle::first();

        // On enchaîne deux replays puis on quitte par « Revanche »
        $component->call('replayBattle', $battle->id)
            ->call('replayBattle', $battle->id)
            ->call('rematch')
            ->assertSet('replayFighter', null)
            ->assertSet('opponentBeforeReplay', null);

        $this->assertSame($realOpponent, $component->get('opponent'));
    }

    // --- Faille 5 : charger toute la collection d'un coup ---

    public function test_player_cannot_change_page_size(): void
    {
        $user = User::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($user)->test(CollectionGrid::class)->set('perPage', 100000);
    }
}
