<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BattleEngineService;
use App\Services\ArenaService;
use App\Models\Battle;
use App\Models\User;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BattleEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private BattleEngineService $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new BattleEngineService();
    }

    /**
     * Test 1 : Déterminisme absolu.
     * Avec la même seed, deux simulations doivent produire exactement le même résultat et le même journal.
     */
    public function test_simulation_is_strictly_deterministic_with_same_seed(): void
    {
        $challenger = [
            'id' => 'c1',
            'name' => 'Kaelen',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'hp' => 80,
            'max_hp' => 80,
            'strength' => 55,
            'speed' => 50,
            'defense' => 45,
            'luck' => 15,
        ];

        $opponent = [
            'id' => 'o1',
            'name' => 'Grimjaw',
            'race_id' => 'orc',
            'weapon_id' => 'hammer',
            'power_id' => 'poison',
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 60,
            'speed' => 40,
            'defense' => 40,
            'luck' => 12,
        ];

        $seed = 777123;

        $result1 = $this->engine->simulate($challenger, $opponent, $seed);
        $result2 = $this->engine->simulate($challenger, $opponent, $seed);

        $this->assertSame($result1['winner'], $result2['winner']);
        $this->assertSame($result1['winner_id'], $result2['winner_id']);
        $this->assertSame($result1['turns'], $result2['turns']);
        $this->assertSame($result1['reason'], $result2['reason']);
        $this->assertSame($result1['seed'], $result2['seed']);
        $this->assertSame($result1['log'], $result2['log']);
    }

    /**
     * Test 2 : Initiative.
     * Le combattant le plus rapide prend l'initiative en Phase 1.
     */
    public function test_faster_fighter_takes_initiative(): void
    {
        $fast = [
            'id' => 'fast',
            'name' => 'Vif',
            'race_id' => 'elf',
            'weapon_id' => 'bow',
            'power_id' => 'lightning',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 95,
            'defense' => 40,
        ];

        $slow = [
            'id' => 'slow',
            'name' => 'Lourd',
            'race_id' => 'golem',
            'weapon_id' => 'hammer',
            'power_id' => 'earth',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 20,
            'defense' => 40,
        ];

        $outcome = $this->engine->simulate($fast, $slow, 12345);

        // Premier log d'initiative au Tour 1
        $firstInit = collect($outcome['log'])->firstWhere('type', 'initiative');
        $this->assertNotNull($firstInit);
        $this->assertSame('challenger', $firstInit['actor']);
        $this->assertStringContainsString('Vif prend l\'initiative', $firstInit['message']);
    }

    /**
     * Test 3 : Égalité d'initiative.
     * Quand les vitesses sont identiques, un tirage au sort (coin flip) est effectué.
     */
    public function test_initiative_tie_uses_coin_flip(): void
    {
        $f1 = [
            'id' => 'f1',
            'name' => 'Combattant 1',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
        ];

        $f2 = [
            'id' => 'f2',
            'name' => 'Combattant 2',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 50,
            'speed' => 50,
            'defense' => 50,
        ];

        $outcome = $this->engine->simulate($f1, $f2, 999);

        $initLog = collect($outcome['log'])->firstWhere('type', 'initiative');
        $this->assertNotNull($initLog);
        $this->assertStringContainsString('égalité, tirage au sort', $initLog['message']);
    }

    /**
     * Test 4 : Limite anti-boucle de 25 tours.
     * Deux sacs de PV avec défense max s'arrêtent à 25 tours et sont départagés aux points.
     */
    public function test_max_turns_limit_25_stops_and_decides_on_points(): void
    {
        $tank1 = [
            'id' => 't1',
            'name' => 'Tank Alpha',
            'race_id' => 'golem',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 800,
            'max_hp' => 800,
            'strength' => 20,
            'speed' => 30,
            'defense' => 95,
        ];

        $tank2 = [
            'id' => 't2',
            'name' => 'Tank Bêta',
            'race_id' => 'golem',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 800,
            'max_hp' => 800,
            'strength' => 20,
            'speed' => 30,
            'defense' => 95,
        ];

        $outcome = $this->engine->simulate($tank1, $tank2, 10101);

        $this->assertSame(BattleEngineService::MAX_TURNS, $outcome['turns']);
        $this->assertSame('turn_limit', $outcome['reason']);

        $endLog = collect($outcome['log'])->firstWhere('type', 'end');
        $this->assertNotNull($endLog);
        $this->assertStringContainsString('Limite de 25 tours atteinte', $endLog['message']);
        $this->assertStringContainsString('aux points', $endLog['message']);
    }

    /**
     * Test 5 : Double K.O. et Match Nul (DRAW).
     * Quand deux combattants tombent simultanément à 0 PV (ex: corrosion interne de début de tour),
     * le résultat est self::DRAW avec winner_id null et reason 'double_ko'.
     */
    public function test_double_ko_results_in_draw(): void
    {
        // La synergie robot + poison inflige 4 dégâts de corrosion interne en début de tour
        $suicide1 = [
            'id' => 's1',
            'name' => 'Robot A',
            'race_id' => 'robot',
            'weapon_id' => 'dagger',
            'power_id' => 'poison',
            'hp' => 4,
            'max_hp' => 4,
            'strength' => 30,
            'speed' => 50,
            'defense' => 30,
        ];

        $suicide2 = [
            'id' => 's2',
            'name' => 'Robot B',
            'race_id' => 'robot',
            'weapon_id' => 'dagger',
            'power_id' => 'poison',
            'hp' => 4,
            'max_hp' => 4,
            'strength' => 30,
            'speed' => 50,
            'defense' => 30,
        ];

        $outcome = $this->engine->simulate($suicide1, $suicide2, 42);

        $this->assertSame(BattleEngineService::DRAW, $outcome['winner']);
        $this->assertNull($outcome['winner_id']);
        $this->assertSame('double_ko', $outcome['reason']);

        $endLog = collect($outcome['log'])->firstWhere('type', 'end');
        $this->assertNotNull($endLog);
        $this->assertStringContainsString('Égalité sanglante', $endLog['message']);
    }

    /**
     * Test 6 : Atténuation par la Défense.
     * Une cible avec une défense élevée doit encaisser significativement moins de dégâts bruts.
     */
    public function test_defense_reduces_damage(): void
    {
        $attacker = [
            'id' => 'atk',
            'name' => 'Frappeur',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 200,
            'max_hp' => 200,
            'strength' => 80,
            'speed' => 90, // Frappe en premier
            'defense' => 30,
            'luck' => 5, // Pas de critique aléatoire
        ];

        $fragileTarget = [
            'id' => 'fragile',
            'name' => 'Cible Fragile',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 200,
            'max_hp' => 200,
            'strength' => 10,
            'speed' => 10,
            'defense' => 10,
            'luck' => 5,
        ];

        $armoredTarget = [
            'id' => 'armored',
            'name' => 'Cible Blindée',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 200,
            'max_hp' => 200,
            'strength' => 10,
            'speed' => 10,
            'defense' => 90,
            'luck' => 5,
        ];

        $seed = 12345;
        $resFragile = $this->engine->simulate($attacker, $fragileTarget, $seed);
        $resArmored = $this->engine->simulate($attacker, $armoredTarget, $seed);

        $firstHitFragile = collect($resFragile['log'])->firstWhere('type', 'hit')['value'] ?? 0;
        $firstHitArmored = collect($resArmored['log'])->firstWhere('type', 'hit')['value'] ?? 0;

        $this->assertGreaterThan($firstHitArmored, $firstHitFragile);
    }

    /**
     * Test 7 : Bouclier Arcanique (Synergie Bâton + Régénération).
     * Le bouclier est généré au Tour 0 et absorbe les premiers dégâts avant les PV.
     */
    public function test_shield_synergy_absorbs_damage_first(): void
    {
        $shielded = [
            'id' => 'sh',
            'name' => 'Mage Sylvestre',
            'race_id' => 'elf',
            'weapon_id' => 'staff',
            'power_id' => 'regeneration',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 40,
            'speed' => 30,
            'defense' => 30,
        ];

        $attacker = [
            'id' => 'atk',
            'name' => 'Attaquant',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 100,
            'max_hp' => 100,
            'strength' => 60,
            'speed' => 80,
            'defense' => 30,
        ];

        $outcome = $this->engine->simulate($shielded, $attacker, 9876);

        // Vérifie qu'un log de bouclier a été posé
        $shieldLog = collect($outcome['log'])->firstWhere('type', 'shield');
        $this->assertNotNull($shieldLog);
        $this->assertGreaterThan(0, $shieldLog['value']);
    }

    /**
     * Test 8 : Vol de vie du Vampire.
     * Le vampire draine des PV lorsqu'il inflige des dégâts et a des PV manquants.
     */
    public function test_vampire_heals_via_lifesteal(): void
    {
        $vampire = [
            'id' => 'vamp',
            'name' => 'Comte Dracula',
            'race_id' => 'vampire',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 50,
            'max_hp' => 120, // PV manquants
            'strength' => 80,
            'speed' => 80,
            'defense' => 30,
        ];

        $victim = [
            'id' => 'vic',
            'name' => 'Victime',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 150,
            'max_hp' => 150,
            'strength' => 20,
            'speed' => 20,
            'defense' => 20,
        ];

        $outcome = $this->engine->simulate($vampire, $victim, 555);

        $lifestealLog = collect($outcome['log'])->firstWhere('type', 'lifesteal');
        $this->assertNotNull($lifestealLog, "Le vol de vie du vampire doit être enregistré dans les logs");
        $this->assertGreaterThan(0, $lifestealLog['value']);
        $this->assertStringContainsString('draine', $lifestealLog['message']);
    }

    /**
     * Test 9 : Effet d'étourdissement du Marteau (Coup contondant).
     */
    public function test_hammer_can_stun_opponent(): void
    {
        $crusher = [
            'id' => 'crush',
            'name' => 'Broyeur',
            'race_id' => 'golem',
            'weapon_id' => 'hammer', // 25% stun chance + synergie golem
            'power_id' => 'earth',
            'hp' => 200,
            'max_hp' => 200,
            'strength' => 60,
            'speed' => 80,
            'defense' => 50,
        ];

        $target = [
            'id' => 'tgt',
            'name' => 'Cible',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 250,
            'max_hp' => 250,
            'strength' => 20,
            'speed' => 30,
            'defense' => 40,
        ];

        // On fait quelques simulations avec des seeds variées pour trouver un stun
        $foundStun = false;
        for ($s = 1; $s <= 10; $s++) {
            $outcome = $this->engine->simulate($crusher, $target, $s * 100);
            if (collect($outcome['log'])->contains('type', 'stun')) {
                $foundStun = true;
                break;
            }
        }

        $this->assertTrue($foundStun, "Le marteau doit déclencher au moins un étourdissement");
    }

    /**
     * Test 10 : Pouvoir de Feu et DoT de Brûlure.
     */
    public function test_fire_power_applies_burn_dot(): void
    {
        $fireMage = [
            'id' => 'pyro',
            'name' => 'Pyromancien',
            'race_id' => 'demon',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'hp' => 150,
            'max_hp' => 150,
            'strength' => 60,
            'speed' => 80,
            'defense' => 30,
        ];

        $target = [
            'id' => 'tgt',
            'name' => 'Cible Résistante',
            'race_id' => 'golem',
            'weapon_id' => 'sword',
            'power_id' => 'earth',
            'hp' => 200,
            'max_hp' => 200,
            'strength' => 20,
            'speed' => 20,
            'defense' => 50,
        ];

        $outcome = $this->engine->simulate($fireMage, $target, 1234);

        $applied = collect($outcome['log'])->firstWhere('type', 'burn_applied');
        $this->assertNotNull($applied, "La brûlure doit être appliquée lors d'un coup réussi");
        $this->assertStringContainsString('s\'embrase', $applied['message']);

        $burnDamage = collect($outcome['log'])->firstWhere('type', 'burn');
        $this->assertNotNull($burnDamage, "Des dégâts de brûlure doivent être subis en début de tour");
    }

    /**
     * Test 11 : Replay et Audit Anti-Triche via ArenaService.
     * Un combat sauvegardé doit être 100% reproductible à partir de sa seed et de ses snapshots.
     * Une altération de turns_log ou de winner_id doit invalider son authenticité.
     */
    public function test_arena_replay_and_authenticity_verification(): void
    {
        $user = User::factory()->create();
        $arena = app(ArenaService::class);

        $fighter = Character::create([
            'user_id' => $user->id,
            'name' => 'Héros Test',
            'race_id' => 'human',
            'weapon_id' => 'sword',
            'power_id' => 'fire',
            'power_tier' => \App\Enums\PowerTier::Common,
            'hp' => 90,
            'max_hp' => 90,
            'strength' => 60,
            'speed' => 55,
            'defense' => 45,
            'power_score' => 250,
            'rarity' => \App\Enums\Rarity::Common,
            'is_lucky_spin' => false,
            'is_unstable' => false,
            'synergies_data' => [],
            'recycle_value' => 50,
            'is_active_fighter' => true,
            'pending_reveal' => false,
        ]);

        $bot = $arena->generateBotOpponent($user);

        // Exécute le combat contre le bot
        $arena->fightBot($user, $fighter, $bot);

        $battle = Battle::latest()->first();
        $this->assertNotNull($battle);

        // 1. Le combat doit être authentique
        $this->assertTrue($arena->isAuthentic($battle), "Le combat original doit être certifié authentique");

        // 2. Si on modifie le journal, l'authenticité échoue
        $tamperedLog = $battle->turns_log;
        $tamperedLog[0]['message'] = 'Journal falsifié !';
        $battle->turns_log = $tamperedLog;
        $this->assertFalse($arena->isAuthentic($battle), "Un combat avec journal altéré doit échouer l'audit anti-triche");

        // 3. Si on modifie le winner_id, l'authenticité échoue
        $battle->refresh();
        $battle->winner_id = 'faux-vainqueur-id';
        $this->assertFalse($arena->isAuthentic($battle), "Un combat avec vainqueur falsifié doit échouer l'audit anti-triche");
    }
}
