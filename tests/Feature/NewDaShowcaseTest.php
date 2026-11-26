<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\GameConfig;
use App\Enums\League;
use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Models\WorkshopUpgrade;
use App\Support\NewDaIconRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class NewDaShowcaseTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const ALL_ROUTES = [
        '/newda',
        '/newda/grimoire',
        '/newda/arene',
        '/newda/rang',
        '/newda/atelier',
    ];

    public function test_all_five_newda_routes_return_ok_with_noindex_headers_and_minimal_layout(): void
    {
        foreach (self::ALL_ROUTES as $uri) {
            $response = $this->get($uri);

            $response->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
                ->assertSee('css/newda.css', false)
                ->assertSee('js/newda.js', false)
                ->assertSee('id="btn-sandbox-toggle"', false)
                ->assertSee('id="sandbox-drawer"', false)
                ->assertSee('Tirage')
                ->assertSee('Grimoire')
                ->assertSee('Arène')
                ->assertSee('Rang')
                ->assertSee('Atelier')
                ->assertDontSee('chapitre-concept', false)
                ->assertDontSee('bench-box', false)
                ->assertDontSee('buttons-showcase', false);
        }
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
        $this->assertStringNotContainsString('newda.', $navigation);
        $this->assertStringNotContainsString('/newda', $robots);
    }

    public function test_synergy_table_and_paper_doll_map_are_scoped_strictly_to_invocation_route(): void
    {
        $invocation = $this->get('/newda');
        $invocation->assertOk();

        /** @var array<string, mixed> $invPayload */
        $invPayload = $invocation->viewData('codexPayload');
        $synergies = $invPayload['synergies'] ?? [];
        $paperDollMap = $invPayload['paperDollMap'] ?? [];

        $this->assertArrayHasKey('human:sword:fire', $synergies);
        $this->assertSame("Maître d'Armes", $synergies['human:sword:fire'][0]['name']);
        $this->assertArrayHasKey('demon:hammer:ice', $synergies);
        $this->assertTrue($synergies['demon:hammer:ice'][0]['is_unstable']);
        $this->assertArrayNotHasKey('human:bow:fire', $synergies);

        $this->assertArrayHasKey('human', $paperDollMap['races'] ?? []);
        $this->assertArrayHasKey('elf', $paperDollMap['races'] ?? []);
        $this->assertArrayHasKey('vampire', $paperDollMap['races'] ?? []);
        $this->assertArrayNotHasKey('demon', $paperDollMap['races'] ?? []);
        $this->assertNull($paperDollMap['equipment']['sword']['invisibility']['weapon']);
        $this->assertStringContainsString('sword_invisibility.webp', (string) $paperDollMap['equipment']['sword']['invisibility']['aura']);

        foreach (['/newda/grimoire', '/newda/arene', '/newda/rang', '/newda/atelier'] as $otherUri) {
            $other = $this->get($otherUri);
            $other->assertOk();
            /** @var array<string, mixed> $payload */
            $payload = $other->viewData('codexPayload');
            $this->assertArrayNotHasKey('synergies', $payload, "synergies ne doit pas être calculé sur {$otherUri}");
            $this->assertArrayNotHasKey('paperDollMap', $payload, "paperDollMap ne doit pas être calculé sur {$otherUri}");
        }
    }

    public function test_non_human_folios_never_render_isolated_weapon_image_and_invisibility_hides_base_weapon(): void
    {
        $response = $this->get('/newda/grimoire');
        $response->assertOk();

        /** @var list<array<string, mixed>> $folios */
        $folios = $response->viewData('showcaseFolios');
        $byNumber = collect($folios)->keyBy('folio_number');

        $demonFolio = $byNumber->get('005');
        $this->assertNotNull($demonFolio);
        $this->assertSame('demon', $demonFolio['race_id']);
        $this->assertSame('hammer', $demonFolio['weapon_id']);
        $this->assertFalse($demonFolio['has_portrait']);
        $this->assertNull($demonFolio['layers']['race']);
        $this->assertNull($demonFolio['layers']['weapon']);
        $this->assertNull($demonFolio['layers']['aura']);
        $this->assertArrayNotHasKey('note', $demonFolio);

        $invisFolio = $byNumber->get('003');
        $this->assertNotNull($invisFolio);
        $this->assertTrue($invisFolio['has_portrait']);
        $this->assertStringContainsString('human.webp', (string) $invisFolio['layers']['race']);
        $this->assertNull($invisFolio['layers']['weapon']);
        $this->assertStringContainsString('sword_invisibility.webp', (string) $invisFolio['layers']['aura']);

        $html = (string) $response->getContent();
        preg_match('/<article class="folio-card" data-folio-id="showcase-005"[\s\S]*?<\/article>/', $html, $demonCardMatch);
        $this->assertNotEmpty($demonCardMatch);
        $this->assertStringContainsString('data-blason-race="demon"', $demonCardMatch[0]);
        $this->assertStringNotContainsString('<img', $demonCardMatch[0]);
        $this->assertStringNotContainsString('hammer.webp', $demonCardMatch[0]);

        preg_match('/<article class="folio-card" data-folio-id="showcase-003"[\s\S]*?<\/article>/', $html, $invisCardMatch);
        $this->assertNotEmpty($invisCardMatch);
        $this->assertStringContainsString('sword_invisibility.webp', $invisCardMatch[0]);
        $this->assertStringNotContainsString('weapons/sword.webp', $invisCardMatch[0]);
    }

    public function test_zero_database_writes_and_beginner_experience_across_all_routes(): void
    {
        $this->assertSame(0, User::count());
        $this->assertSame(0, Character::count());
        $this->assertSame(0, WorkshopUpgrade::count());

        foreach (self::ALL_ROUTES as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->assertSame(0, User::count());
        $this->assertSame(0, Character::count());
        $this->assertSame(0, WorkshopUpgrade::count());

        $beginner = User::factory()->create([
            'name' => 'DébutantTest',
            'email' => 'player@wheelborn.test',
            'coins' => 250,
            'spin_charges' => 3,
            'pity_counter' => 0,
            'elo' => 1000,
        ]);

        foreach (self::ALL_ROUTES as $uri) {
            $this->actingAs($beginner)->get($uri)->assertOk();
        }

        // Parcours débutant : Grimoire vide propre (0 héros, jamais mélangé aux 6 vitrine)
        $grimoireEmpty = $this->actingAs($beginner)->get('/newda/grimoire');
        $grimoireEmpty->assertOk()
            ->assertSee('Grimoire vierge')
            ->assertSee('Invoquer')
            ->assertDontSee('Valérian Brise-Fer');
        $this->assertSame([], $grimoireEmpty->viewData('initialFolios'));
        $this->assertSame(0, $grimoireEmpty->viewData('connectedSnapshot')['folios_count']);

        // Parcours débutant : Arène affiche le bandeau < 2 héros + bouton démo explicite
        $arenaEmpty = $this->actingAs($beginner)->get('/newda/arene');
        $arenaEmpty->assertOk()
            ->assertSee('2 héros requis pour le duel')
            ->assertSee('Essayer avec 2 champions de démo');

        $beginner->refresh();
        $this->assertSame(1, User::count());
        $this->assertSame(0, Character::count());
        $this->assertSame(0, WorkshopUpgrade::count());
        $this->assertSame(250, $beginner->coins);
        $this->assertSame(3, $beginner->spin_charges);
        $this->assertSame(0, $beginner->pity_counter);

        // Filtrage strict : 1 actif visible, 1 pending_reveal masqué, 1 recyclé masqué
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

        $grimoireWithHero = $this->actingAs($beginner)->get('/newda/grimoire');
        $grimoireWithHero->assertOk()
            ->assertSee('Grommash')
            ->assertDontSee('SecretEnCours')
            ->assertDontSee('RecycleAncien');

        /** @var array<string, mixed> $snapshot */
        $snapshot = $grimoireWithHero->viewData('connectedSnapshot');
        $this->assertSame(1, $snapshot['folios_count']);
        $this->assertSame('Grommash', $snapshot['folios'][0]['name']);
        $this->assertFalse($snapshot['folios'][0]['has_portrait']);
        $this->assertNull($snapshot['folios'][0]['layers']['weapon']);
        $this->assertSame(3, Character::count());
    }

    public function test_leaderboard_never_exposes_database_users_to_guests_or_other_players(): void
    {
        $realAlpha = User::factory()->create([
            'name' => 'SecretRealPlayerAlpha',
            'email' => 'alpha-secret@example.test',
            'elo' => 2450,
        ]);
        $realBeta = User::factory()->create([
            'name' => 'SecretRealPlayerBeta',
            'email' => 'beta-secret@example.test',
            'elo' => 1150,
        ]);

        // Invité : zéro utilisateur BDD exposé, 5 ligues présentes
        $guestRes = $this->get('/newda/rang');
        $guestRes->assertOk()
            ->assertDontSee($realAlpha->name)
            ->assertDontSee($realAlpha->email)
            ->assertDontSee($realBeta->name)
            ->assertDontSee($realBeta->email);

        $this->assertNull($guestRes->viewData('pinnedPlayer'));

        /** @var list<array<string, mixed>> $leagues */
        $leagues = $guestRes->viewData('leagues');
        $this->assertCount(count(League::cases()), $leagues);
        foreach (League::cases() as $league) {
            $guestRes->assertSee($league->label());
        }

        /** @var array<string, list<array<string, mixed>>> $standingsByLeague */
        $standingsByLeague = $guestRes->viewData('standingsByLeague');
        foreach ($standingsByLeague as $entries) {
            $this->assertCount(5, $entries);
            foreach ($entries as $entry) {
                $champ = $entry['champion'];
                if (! $champ['has_portrait']) {
                    $this->assertNull($champ['layers']['race']);
                    $this->assertNull($champ['layers']['weapon']);
                    $this->assertNull($champ['layers']['aura']);
                }
            }
        }

        // Connecté : voit uniquement sa propre ligne épinglée, jamais les autres comptes BDD
        $authRes = $this->actingAs($realBeta)->get('/newda/rang');
        $authRes->assertOk()
            ->assertSee($realBeta->name)
            ->assertDontSee($realAlpha->name)
            ->assertDontSee($realAlpha->email);

        /** @var array<string, mixed> $pinned */
        $pinned = $authRes->viewData('pinnedPlayer');
        $this->assertSame($realBeta->name, $pinned['name']);
        $this->assertSame(1150, $pinned['elo']);
        $this->assertSame(League::Silver->value, $pinned['league_value']);
    }

    public function test_workshop_mirrors_game_config_and_reads_existing_levels_readonly(): void
    {
        $user = User::factory()->create(['coins' => 900]);
        WorkshopUpgrade::create([
            'user_id' => $user->id,
            'rare_magnet_level' => 2,
            'hero_forge_level' => 1,
            'arena_bonus_level' => 0,
        ]);

        $response = $this->actingAs($user)->get('/newda/atelier');
        $response->assertOk();

        $workshopConfig = GameConfig::workshop();
        $response->assertSee($workshopConfig['rare_magnet']['name'])
            ->assertSee($workshopConfig['hero_forge']['name'])
            ->assertSee($workshopConfig['arena_bonus']['name']);

        /** @var array<string, mixed> $payload */
        $payload = $response->viewData('codexPayload');
        $this->assertSame([
            'rare_magnet' => 2,
            'hero_forge' => 1,
            'arena_bonus' => 0,
        ], $payload['workshopLevels']);
        $this->assertSame(1, WorkshopUpgrade::count());
    }

    public function test_icon_registry_provides_24_default_svgs_and_autodetects_custom_svg_webp_png(): void
    {
        $tempRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wheelborn_icons_test_'.bin2hex(random_bytes(6));
        mkdir($tempRoot.DIRECTORY_SEPARATOR.'races', 0777, true);
        mkdir($tempRoot.DIRECTORY_SEPARATOR.'weapons', 0777, true);
        mkdir($tempRoot.DIRECTORY_SEPARATOR.'powers', 0777, true);

        try {
            $registry = new NewDaIconRegistry($tempRoot);
            $all = $registry->allByCategory();

            $this->assertCount(8, $all['races']);
            $this->assertCount(8, $all['weapons']);
            $this->assertCount(8, $all['powers']);
            $this->assertSame('svg', $all['races']['human']['type']);
            $this->assertNull($all['races']['human']['url']);
            $this->assertNotEmpty($all['races']['human']['markup']);

            // PNG seul -> détecté en PNG
            file_put_contents($tempRoot.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.png', 'png');
            $pngResolved = $registry->resolve('races', 'human');
            $this->assertSame('image', $pngResolved['type']);
            $this->assertSame('png', $pngResolved['format']);
            $this->assertStringEndsWith('/images/newda/icons/races/human.png', (string) $pngResolved['url']);
            $this->assertStringContainsString('<img ', $registry->renderInlineIcon('races', 'human'));

            // WebP prioritaire sur PNG
            file_put_contents($tempRoot.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.webp', 'webp');
            $webpResolved = $registry->resolve('races', 'human');
            $this->assertSame('webp', $webpResolved['format']);

            // SVG prioritaire sur WebP et PNG
            file_put_contents($tempRoot.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.svg', '<svg></svg>');
            $svgResolved = $registry->resolve('races', 'human');
            $this->assertSame('svg', $svgResolved['format']);
            $this->assertSame('image', $svgResolved['type']);

            // Les autres IDs conservent le fallback SVG gravé
            $fallback = $registry->resolve('powers', 'gravity');
            $this->assertSame('svg', $fallback['type']);
            $this->assertNull($fallback['url']);
            $this->assertStringContainsString('<svg ', $registry->renderInlineIcon('powers', 'gravity'));

            $this->expectException(InvalidArgumentException::class);
            $registry->resolve('unknown', 'human');
        } finally {
            foreach (['races', 'weapons', 'powers'] as $sub) {
                $dir = $tempRoot.DIRECTORY_SEPARATOR.$sub;
                if (is_dir($dir)) {
                    foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
                        @unlink($file);
                    }
                    @rmdir($dir);
                }
            }
            @rmdir($tempRoot);
        }
    }

    public function test_accessibility_and_volvelle_custom_icon_clip_invariants_are_rendered(): void
    {
        $invocation = $this->get('/newda');
        $invocation->assertOk()
            ->assertSee('id="volvelle-clip-25"', false)
            ->assertSee('id="volvelle-clip-30"', false)
            ->assertSee('id="volvelle-clip-34"', false)
            ->assertSee('filter="url(#ringGlowFilter)"', false);

        $grimoire = $this->get('/newda/grimoire');
        $grimoire->assertOk()
            ->assertSee('folio-face--front" aria-hidden="false"', false)
            ->assertSee('folio-face--back" aria-hidden="true"', false)
            ->assertSee('data-flip-folio tabindex="-1"', false);

        $rang = $this->get('/newda/rang');
        $rang->assertOk()
            ->assertSee('id="rang-tabpanel" role="tabpanel"', false)
            ->assertSee('aria-controls="rang-tabpanel"', false)
            ->assertSee('tabindex="0"', false)
            ->assertSee('tabindex="-1"', false);
    }
}
