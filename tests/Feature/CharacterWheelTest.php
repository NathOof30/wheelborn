<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\CharacterWheel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CharacterWheelTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_wheel_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->assertStatus(200)
            ->assertSee('La Roue du Destin')
            ->assertSee('Lancer la Roue');
    }

    public function test_character_wheel_can_spin_and_generate_character(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 3,
            'pity_counter' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->assertSet('spinCharges', 2)
            ->assertSet('pityCounter', 1)
            ->assertSee('Points de Vie')
            ->assertSee('Force')
            ->assertSee('Vitesse')
            ->assertSee('Défense');

        $this->assertDatabaseCount('characters', 1);
    }

    public function test_character_can_be_kept_in_collection(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 3,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->call('keepCharacter')
            ->assertSet('revealedCharacter', null);

        $this->assertDatabaseHas('characters', [
            'user_id' => $user->id,
            'pending_reveal' => false,
            'recycled_at' => null,
        ]);
    }

    public function test_character_can_be_recycled(): void
    {
        $user = User::factory()->create([
            'coins' => 250,
            'spin_charges' => 3,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->call('recycleCharacter')
            ->assertSet('revealedCharacter', null);

        $user->refresh();
        $this->assertGreaterThan(250, $user->coins);
    }

    /**
     * T6.1 : Vérifie qu'un tirage non finalisé (pending_reveal = true) est restauré dès le mount().
     */
    public function test_pending_reveal_is_automatically_restored_on_mount(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 2,
        ]);

        $pendingChar = \App\Models\Character::create([
            'user_id'           => $user->id,
            'name'              => 'Gladiateur Perdu',
            'race_id'           => 'human',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => \App\Enums\PowerTier::Common,
            'hp'                => 85,
            'max_hp'            => 85,
            'strength'          => 45,
            'speed'             => 40,
            'defense'           => 35,
            'power_score'       => 70,
            'rarity'            => \App\Enums\Rarity::Rare,
            'recycle_value'     => 120,
            'pending_reveal'    => true,
            'is_active_fighter' => false,
            'is_locked'         => false,
        ]);

        $this->actingAs($user);

        Livewire::test(CharacterWheel::class)
            ->assertSet('revealedCharacter.id', $pendingChar->id)
            ->assertSet('revealedCharacter.name', 'Gladiateur Perdu')
            ->assertSee('Gladiateur Perdu')
            ->assertSee('Tirage en attente récupéré');
    }

    /**
     * T6.1 : Vérifie qu'un utilisateur ne peut pas lancer un nouveau spin s'il a déjà un tirage en attente.
     */
    public function test_user_cannot_spin_again_while_pending_reveal_exists(): void
    {
        $user = User::factory()->create([
            'spin_charges' => 3,
            'pity_counter' => 2,
        ]);

        $pendingChar = \App\Models\Character::create([
            'user_id'           => $user->id,
            'name'              => 'Héros en Attente',
            'race_id'           => 'elf',
            'weapon_id'         => 'bow',
            'power_id'          => 'lightning',
            'power_tier'        => \App\Enums\PowerTier::Rare,
            'hp'                => 75,
            'max_hp'            => 75,
            'strength'          => 35,
            'speed'             => 60,
            'defense'           => 25,
            'power_score'       => 65,
            'rarity'            => \App\Enums\Rarity::Common,
            'recycle_value'     => 50,
            'pending_reveal'    => true,
            'is_active_fighter' => false,
            'is_locked'         => false,
        ]);

        $this->actingAs($user);

        // Tentative de spin avec un pending déjà présent
        Livewire::test(CharacterWheel::class)
            ->call('spin')
            ->assertSet('spinCharges', 3) // La charge n'est pas débitée
            ->assertSet('pityCounter', 2) // Le pity counter n'a pas bougé
            ->assertSet('revealedCharacter.id', $pendingChar->id)
            ->assertSee('Finalisez le tirage ci-dessous');

        // Toujours un seul personnage en BDD
        $this->assertDatabaseCount('characters', 1);
    }

    /**
     * T6.2 : Sécurité RecycleService — Impossible de recycler un personnage déployé en arène.
     */
    public function test_recycle_service_blocks_active_fighter(): void
    {
        $user = User::factory()->create();

        $fighter = \App\Models\Character::create([
            'user_id'           => $user->id,
            'name'              => 'Champion Déployé',
            'race_id'           => 'orc',
            'weapon_id'         => 'axe',
            'power_id'          => 'earth',
            'power_tier'        => \App\Enums\PowerTier::Common,
            'hp'                => 100,
            'max_hp'            => 100,
            'strength'          => 60,
            'speed'             => 30,
            'defense'           => 45,
            'power_score'       => 85,
            'rarity'            => \App\Enums\Rarity::Epic,
            'recycle_value'     => 250,
            'pending_reveal'    => false,
            'is_active_fighter' => true, // Actif dans l'arène
            'is_locked'         => false,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("actuellement déployé dans l'arène");

        app(\App\Services\RecycleService::class)->recycle($user, $fighter->id);
    }

    /**
     * T6.2 : Sécurité RecycleService — Impossible de recycler le personnage d'un autre joueur.
     */
    public function test_recycle_service_cannot_recycle_character_of_another_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $charA = \App\Models\Character::create([
            'user_id'           => $userA->id,
            'name'              => 'Carte Joueur A',
            'race_id'           => 'human',
            'weapon_id'         => 'sword',
            'power_id'          => 'fire',
            'power_tier'        => \App\Enums\PowerTier::Common,
            'hp'                => 80,
            'max_hp'            => 80,
            'strength'          => 40,
            'speed'             => 40,
            'defense'           => 30,
            'power_score'       => 60,
            'rarity'            => \App\Enums\Rarity::Common,
            'recycle_value'     => 40,
            'pending_reveal'    => false,
            'is_active_fighter' => false,
            'is_locked'         => false,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        app(\App\Services\RecycleService::class)->recycle($userB, $charA->id);
    }
}
