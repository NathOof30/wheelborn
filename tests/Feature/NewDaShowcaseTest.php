<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewDaShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_newda_with_noindex_headers_and_five_chapters(): void
    {
        $response = $this->get('/newda');

        $response->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('css/newda.css', false)
            ->assertSee('js/newda.js', false)
            ->assertSee('id="chapitre-concept"', false)
            ->assertSee('id="chapitre-volvelle"', false)
            ->assertSee('id="chapitre-folios"', false)
            ->assertSee('id="chapitre-duel"', false)
            ->assertSee('id="chapitre-atelier"', false)
            ->assertSee('images/characters/races/human.webp', false)
            ->assertSee('images/characters/weapons/sword_auras/sword_fire.webp', false)
            ->assertSee('images/characters/weapons/sword_auras/sword_invisibility.webp', false)
            ->assertSee('images/characters/weapons/staff_auras/staff_ice.webp', false)
            ->assertSee('images/characters/weapons/grimoire.webp', false);
    }

    public function test_newda_is_not_referenced_in_welcome_navigation_or_robots(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('/newda');

        $navigation = file_get_contents(resource_path('views/layouts/navigation.blade.php'));
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($navigation);
        $this->assertIsString($robots);
        $this->assertStringNotContainsString('/newda', $navigation);
        $this->assertStringNotContainsString('newda.showcase', $navigation);
        $this->assertStringNotContainsString('/newda', $robots);
    }

    public function test_non_human_folios_never_render_isolated_weapon_image_and_invisibility_hides_base_weapon(): void
    {
        $response = $this->get('/newda');
        $response->assertOk();

        /** @var list<array<string, mixed>> $folios */
        $folios = $response->viewData('showcaseFolios');
        $byNumber = collect($folios)->keyBy('folio_number');

        // Folio 005 : Démon + Marteau + Glace (pas de race WebP => blason gravé, aucune arme isolée)
        $demonFolio = $byNumber->get('005');
        $this->assertNotNull($demonFolio);
        $this->assertSame('demon', $demonFolio['race_id']);
        $this->assertSame('hammer', $demonFolio['weapon_id']);
        $this->assertFalse($demonFolio['has_portrait']);
        $this->assertNull($demonFolio['layers']['race']);
        $this->assertNull($demonFolio['layers']['weapon']);
        $this->assertNull($demonFolio['layers']['aura']);

        // Folio 003 : Humain + Épée + Invisibilité (arme de base strictement masquée)
        $invisFolio = $byNumber->get('003');
        $this->assertNotNull($invisFolio);
        $this->assertTrue($invisFolio['has_portrait']);
        $this->assertStringContainsString('human.webp', (string) $invisFolio['layers']['race']);
        $this->assertNull($invisFolio['layers']['weapon']);
        $this->assertStringContainsString('sword_invisibility.webp', (string) $invisFolio['layers']['aura']);

        // Vérification DOM réelle sur la carte du Démon (aucune balise <img> dans l'article)
        $html = (string) $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<article class="folio-card" data-folio-id="showcase-005"[\s\S]*?<div class="heraldic-blason" data-blason-race="demon">/',
            $html,
        );
        preg_match('/<article class="folio-card" data-folio-id="showcase-005"[\s\S]*?<\/article>/', $html, $demonCardMatch);
        $this->assertNotEmpty($demonCardMatch);
        $this->assertStringNotContainsString('<img', $demonCardMatch[0]);
        $this->assertStringNotContainsString('hammer.webp', $demonCardMatch[0]);

        // Vérification DOM réelle sur la carte Invisibilité (pas de sword.webp)
        preg_match('/<article class="folio-card" data-folio-id="showcase-003"[\s\S]*?<\/article>/', $html, $invisCardMatch);
        $this->assertNotEmpty($invisCardMatch);
        $this->assertStringContainsString('sword_invisibility.webp', $invisCardMatch[0]);
        $this->assertStringNotContainsString('weapons/sword.webp', $invisCardMatch[0]);
    }

    public function test_sparse_synergy_table_is_precomputed_in_php_without_empty_triplets(): void
    {
        $response = $this->get('/newda');
        $response->assertOk();

        /** @var array<string, mixed> $payload */
        $payload = $response->viewData('codexPayload');
        $synergies = $payload['synergies'] ?? [];

        $this->assertArrayHasKey('human:sword:fire', $synergies);
        $this->assertSame("Maître d'Armes", $synergies['human:sword:fire'][0]['name']);
        $this->assertArrayHasKey('demon:hammer:ice', $synergies);
        $this->assertTrue($synergies['demon:hammer:ice'][0]['is_unstable']);
        $this->assertArrayNotHasKey('human:bow:fire', $synergies);
    }

    public function test_newda_performs_zero_database_writes_for_guest_and_authenticated_users(): void
    {
        $this->assertSame(0, User::count());
        $this->assertSame(0, Character::count());

        $this->get('/newda')->assertOk();

        $this->assertSame(0, User::count());
        $this->assertSame(0, Character::count());

        // Compte débutant de référence (0 héros)
        $beginner = User::factory()->create([
            'email' => 'player@wheelborn.test',
            'coins' => 250,
            'spin_charges' => 3,
            'pity_counter' => 0,
            'elo' => 1000,
        ]);

        $responseBeginner = $this->actingAs($beginner)->get('/newda');
        $responseBeginner->assertOk()
            ->assertSee('Mon Grimoire (0)');

        $beginner->refresh();
        $this->assertSame(1, User::count());
        $this->assertSame(0, Character::count());
        $this->assertSame(250, $beginner->coins);
        $this->assertSame(3, $beginner->spin_charges);
        $this->assertSame(0, $beginner->pity_counter);

        // Compte avec héros existants : 1 actif + 1 pending_reveal (doit être masqué) + 1 recyclé (doit être masqué)
        Character::create([
            'user_id' => $beginner->id,
            'name' => 'Grommash',
            'race_id' => 'demon',
            'weapon_id' => 'hammer',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 80,
            'speed' => 45,
            'defense' => 40,
            'power_score' => 75,
            'rarity' => Rarity::Epic,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 120,
            'is_active_fighter' => false,
            'pending_reveal' => false,
        ]);

        Character::create([
            'user_id' => $beginner->id,
            'name' => 'SecretEnCours',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 70,
            'speed' => 60,
            'defense' => 60,
            'power_score' => 68,
            'rarity' => Rarity::Rare,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 90,
            'is_active_fighter' => false,
            'pending_reveal' => true,
        ]);

        Character::create([
            'user_id' => $beginner->id,
            'name' => 'RecycleAncien',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'poison',
            'power_tier' => PowerTier::Common,
            'hp' => 70,
            'max_hp' => 70,
            'strength' => 60,
            'speed' => 80,
            'defense' => 50,
            'power_score' => 65,
            'rarity' => Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 80,
            'is_active_fighter' => false,
            'pending_reveal' => false,
            'recycled_at' => now(),
        ]);

        $responseWithHero = $this->actingAs($beginner)->get('/newda');
        $responseWithHero->assertOk()
            ->assertSee('Mon Grimoire (1)')
            ->assertDontSee('SecretEnCours')
            ->assertDontSee('RecycleAncien');

        /** @var array<string, mixed> $snapshot */
        $snapshot = $responseWithHero->viewData('connectedSnapshot');
        $this->assertSame(1, $snapshot['folios_count']);
        $this->assertSame('Grommash', $snapshot['folios'][0]['name']);
        $this->assertFalse($snapshot['folios'][0]['has_portrait']);
        $this->assertNull($snapshot['folios'][0]['layers']['weapon']);
        $this->assertSame(3, Character::count());
    }
}
