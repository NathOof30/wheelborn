<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PowerTier;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Support\CodexIconRegistry;
use App\View\Presenters\FolioCardPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CodexDesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_codex_icon_registry_resolves_primary_then_fallback_then_default_svg(): void
    {
        $primaryDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wheelborn_codex_primary_'.uniqid();
        $fallbackDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wheelborn_codex_fallback_'.uniqid();

        mkdir($primaryDir.DIRECTORY_SEPARATOR.'races', 0777, true);
        mkdir($fallbackDir.DIRECTORY_SEPARATOR.'races', 0777, true);
        mkdir($fallbackDir.DIRECTORY_SEPARATOR.'weapons', 0777, true);

        file_put_contents($primaryDir.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.svg', '<svg></svg>');
        file_put_contents($fallbackDir.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.png', 'png');
        file_put_contents($fallbackDir.DIRECTORY_SEPARATOR.'weapons'.DIRECTORY_SEPARATOR.'sword.webp', 'webp');

        try {
            $registry = new CodexIconRegistry(
                primaryDirectory: $primaryDir,
                primaryPrefix: 'images/codex/icons',
                fallbackDirectory: $fallbackDir,
                fallbackPrefix: 'images/newda/icons',
            );

            $human = $registry->resolve('races', 'human');
            $this->assertSame('image', $human['type']);
            $this->assertStringContainsString('images/codex/icons/races/human.svg', (string) $human['url']);

            $sword = $registry->resolve('weapons', 'sword');
            $this->assertSame('image', $sword['type']);
            $this->assertStringContainsString('images/newda/icons/weapons/sword.webp', (string) $sword['url']);

            $fire = $registry->resolve('powers', 'fire');
            $this->assertSame('svg', $fire['type']);
            $this->assertNull($fire['url']);
            $this->assertNotEmpty($fire['markup']);

            $unknownRace = $registry->resolve('races', 'orc');
            $this->assertSame('svg', $unknownRace['type']);
            $this->assertNotEmpty($unknownRace['markup']);

            $unknownPower = $registry->resolve('powers', 'wind');
            $this->assertSame('svg', $unknownPower['type']);
            $this->assertNotEmpty($unknownPower['markup']);

            $this->assertCount(8, $registry->powerSigils());
        } finally {
            @unlink($primaryDir.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.svg');
            @unlink($fallbackDir.DIRECTORY_SEPARATOR.'races'.DIRECTORY_SEPARATOR.'human.png');
            @unlink($fallbackDir.DIRECTORY_SEPARATOR.'weapons'.DIRECTORY_SEPARATOR.'sword.webp');
            @rmdir($primaryDir.DIRECTORY_SEPARATOR.'races');
            @rmdir($primaryDir);
            @rmdir($fallbackDir.DIRECTORY_SEPARATOR.'races');
            @rmdir($fallbackDir.DIRECTORY_SEPARATOR.'weapons');
            @rmdir($fallbackDir);
        }
    }

    public function test_folio_card_presenter_handles_character_invisibility_missing_race_and_high_hp(): void
    {
        $user = User::factory()->create();
        $presenter = app(FolioCardPresenter::class);

        $invisHero = Character::create([
            'user_id' => $user->id,
            'name' => 'Spectre Lame',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'invisibility',
            'power_tier' => PowerTier::Mythic,
            'hp' => 152,
            'max_hp' => 152,
            'strength' => 88,
            'speed' => 84,
            'defense' => 70,
            'power_score' => 94,
            'rarity' => Rarity::Legendary,
            'is_lucky_spin' => true,
            'recycle_value' => 280,
            'wins' => 4,
            'losses' => 1,
            'pending_reveal' => false,
        ]);

        $presented = $presenter->present($invisHero);
        $this->assertTrue($presented['has_portrait']);
        $this->assertNotNull($presented['layers']['race']);
        $this->assertNull($presented['layers']['weapon']);
        $this->assertNotNull($presented['layers']['aura']);
        $this->assertTrue($presented['layers']['is_invisibility']);
        $this->assertTrue($presented['is_lucky_spin']);
        $this->assertSame(80, $presented['winrate']);
        $this->assertGreaterThanOrEqual(3, count($presented['abilities']));

        $demonSnapshot = [
            'id' => 'ghost-1',
            'name' => 'Malakor',
            'race_id' => 'demon',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'rarity' => 'epic',
            'hp' => 145,
            'max_hp' => 145,
            'strength' => 92,
            'speed' => 50,
            'defense' => 55,
            'power_score' => 85,
        ];

        $presentedDemon = $presenter->present($demonSnapshot);
        $this->assertFalse($presentedDemon['has_portrait']);
        $this->assertNull($presentedDemon['layers']['race']);
        $this->assertNull($presentedDemon['layers']['weapon']);
        $this->assertNull($presentedDemon['layers']['aura']);
        $this->assertSame('epic', $presentedDemon['rarity']);
    }

    public function test_folio_card_component_renders_universal_contract_without_flip_button(): void
    {
        $user = User::factory()->create();
        $hero = Character::create([
            'user_id' => $user->id,
            'name' => 'Valérian',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => PowerTier::Common,
            'hp' => 105,
            'max_hp' => 105,
            'strength' => 75,
            'speed' => 60,
            'defense' => 65,
            'power_score' => 82,
            'rarity' => Rarity::Rare,
            'is_lucky_spin' => true,
            'is_locked' => true,
            'recycle_value' => 140,
            'wins' => 7,
            'losses' => 3,
            'pending_reveal' => false,
        ]);

        $html = Blade::render('<x-folio-card :card="$hero" :showOwnerActions="true" />', ['hero' => $hero]);

        $this->assertStringContainsString('folio-card', $html);
        $this->assertStringContainsString('Valérian', $html);
        $this->assertStringContainsString('data-lucky-mark', $html);
        $this->assertStringContainsString('verso-ability-row', $html);
        $this->assertStringContainsString('Points de Vie', $html);
        $this->assertStringContainsString('Protégé', $html);
        $this->assertStringContainsString('70%', $html);
        $this->assertStringNotContainsString('data-flip-folio', $html);
    }

    public function test_welcome_page_renders_codex_cover_with_two_universal_cards_and_newda_is_retired(): void
    {
        $guestResponse = $this->get('/');
        $guestResponse->assertOk()
            ->assertSee('Créez votre')
            ->assertSee('Valérian Brise-Fer')
            ->assertSee('Sylvaëlle Foudre-Arc')
            ->assertSee('folio-card')
            ->assertSee('codex-tooltip-portal')
            ->assertSee('codex-colophon')
            ->assertDontSee('codex-footer')
            ->assertSee('Forger mon destin')
            ->assertDontSee('Laravel 11');

        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/');
        $authResponse->assertOk()
            ->assertSee('Ouvrir le Jeu')
            ->assertSee('folio-card');

        $dashboardHtml = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();
        $this->assertSame(1, substr_count((string) $dashboardHtml, 'aria-label="Ressources du joueur"'));

        $this->get('/newda')->assertNotFound();
        $this->get('/newda/grimoire')->assertNotFound();
    }
}
