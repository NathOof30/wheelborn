<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\GameConfig;
use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Enums\League;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Services\PaperDollService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewDaShowcaseController extends Controller
{
    /**
     * Gloses manuscrites du copiste justifiant les entités WHEELBORN dans le Codex.
     *
     * @var array<string, array{gloss: string, origin: string, initial: string, glyph: string}>
     */
    private const RACE_GLOSSES = [
        'human' => [
            'gloss' => 'Lignée des Cartographes · Équilibre Universel',
            'origin' => 'Plan Médian',
            'initial' => 'H',
            'glyph' => 'compass',
        ],
        'elf' => [
            'gloss' => 'Scribe des Hauts-Bois · Grâce Sylvestre',
            'origin' => 'Plan Sylvestre',
            'initial' => 'E',
            'glyph' => 'leaf',
        ],
        'demon' => [
            'gloss' => 'Brûleur de Sceaux · Fureur Infernale',
            'origin' => 'Plan des Braises',
            'initial' => 'D',
            'glyph' => 'horns',
        ],
        'robot' => [
            'gloss' => 'Automate d\'Airain · Châssis Titane',
            'origin' => 'Plan de Fer',
            'initial' => 'R',
            'glyph' => 'gear',
        ],
        'mutant' => [
            'gloss' => 'Chimère Instable · Génome Chaotique',
            'origin' => 'Faille Alchimique',
            'initial' => 'M',
            'glyph' => 'helix',
        ],
        'golem' => [
            'gloss' => 'Colosse de Basalte · Écorce Rocheuse',
            'origin' => 'Plan Tellurique',
            'initial' => 'G',
            'glyph' => 'monolith',
        ],
        'vampire' => [
            'gloss' => 'Seigneur d\'Encre Rouge · Soif de Sang',
            'origin' => 'Plan Nocturne',
            'initial' => 'V',
            'glyph' => 'chalice',
        ],
        'alien' => [
            'gloss' => 'Visiteur d\'Outre-Sphère · Biologie Stellaire',
            'origin' => 'Sphères Extérieures',
            'initial' => 'A',
            'glyph' => 'astrolabe',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const WEAPON_GLOSSES = [
        'sword' => 'Lame Droite de l\'Ordre',
        'bow' => 'Arc Long en If Gravé',
        'hammer' => 'Masse de Sceau Tellurique',
        'spear' => 'Vouge d\'Allonge Rituelle',
        'dagger' => 'Stylet d\'Ombre Furtive',
        'staff' => 'Verge d\'Arcaniste Scellée',
        'gun' => 'Arquebuse Arcanique · Canon à Étincelle',
        'grimoire' => 'Codex Mineur Élémentaire',
    ];

    /**
     * Pigments historiques et motifs géométriques des 8 pouvoirs.
     *
     * @var array<string, array{pigment_name: string, pigment_hex: string, pigment_soft: string, sigil_label: string, gloss: string}>
     */
    private const POWER_PIGMENTS = [
        'fire' => [
            'pigment_name' => 'Cinabre & Braise',
            'pigment_hex' => '#8A1A14',
            'pigment_soft' => 'rgba(138, 26, 20, 0.16)',
            'sigil_label' => 'Étoile solaire à 8 pointes',
            'gloss' => 'Encre de Cinabre',
        ],
        'poison' => [
            'pigment_name' => 'Vert-de-gris',
            'pigment_hex' => '#3E5438',
            'pigment_soft' => 'rgba(62, 84, 56, 0.16)',
            'sigil_label' => 'Double anneau alchimique',
            'gloss' => 'Teinture de Vert-de-gris',
        ],
        'ice' => [
            'pigment_name' => 'Lapis-lazuli / Outremer',
            'pigment_hex' => '#2A3A7A',
            'pigment_soft' => 'rgba(42, 58, 122, 0.18)',
            'sigil_label' => 'Hexagramme cristallin',
            'gloss' => 'Poudre d\'Outremer',
        ],
        'lightning' => [
            'pigment_name' => 'Ocre d\'or brûlé',
            'pigment_hex' => '#A67C20',
            'pigment_soft' => 'rgba(166, 124, 32, 0.20)',
            'sigil_label' => 'Octogramme fendu',
            'gloss' => 'Or Brûlé de Foudre',
        ],
        'regeneration' => [
            'pigment_name' => 'Vert herbier',
            'pigment_hex' => '#5A6B47',
            'pigment_soft' => 'rgba(90, 107, 71, 0.16)',
            'sigil_label' => 'Vesica piscis botanique',
            'gloss' => 'Sève d\'Herbier',
        ],
        'gravity' => [
            'pigment_name' => 'Indigo de noix',
            'pigment_hex' => '#2F2A45',
            'pigment_soft' => 'rgba(47, 42, 69, 0.18)',
            'sigil_label' => 'Cercles orbitaux d\'astrolabe',
            'gloss' => 'Loi de Pesanteur',
        ],
        'teleportation' => [
            'pigment_name' => 'Pourpre de Tyr',
            'pigment_hex' => '#54274A',
            'pigment_soft' => 'rgba(84, 39, 74, 0.18)',
            'sigil_label' => 'Double portail entrelacé',
            'gloss' => 'Pliure d\'Éther',
        ],
        'invisibility' => [
            'pigment_name' => 'Encre fanée',
            'pigment_hex' => '#6B6258',
            'pigment_soft' => 'rgba(107, 98, 88, 0.16)',
            'sigil_label' => 'Cercle pointillé & œil voilé',
            'gloss' => 'Voile d\'Encre Fanée',
        ],
    ];

    /**
     * @var array<string, array{seal_hex: string, seal_name: string, seal_border: string}>
     */
    private const RARITY_SEALS = [
        'common' => [
            'seal_hex' => '#6B4A2A',
            'seal_name' => 'Cire Argile Sépia',
            'seal_border' => 'Filet simple',
        ],
        'rare' => [
            'seal_hex' => '#2A3A7A',
            'seal_name' => 'Cire Outremer',
            'seal_border' => 'Double filet d\'encre',
        ],
        'epic' => [
            'seal_hex' => '#54274A',
            'seal_name' => 'Cire Pourpre de Tyr',
            'seal_border' => 'Coins enluminés or',
        ],
        'legendary' => [
            'seal_hex' => '#B8923A',
            'seal_name' => 'Double Sceau d\'Or',
            'seal_border' => 'Filet doré & miroitement',
        ],
    ];

    public function __invoke(Request $request): Response
    {
        $races = $this->buildRaces();
        $weapons = $this->buildWeapons();
        $powers = $this->buildPowers();
        $rarities = $this->buildRarities();
        $leagues = $this->buildLeagues();
        $synergyTable = $this->buildSparseSynergyTable($races, $weapons, $powers);
        $paperDollMap = $this->buildHumanPaperDollMap($weapons, $powers);
        $showcaseFolios = $this->buildShowcaseFolios($races, $weapons, $powers);
        $workshop = GameConfig::workshop();
        $leveling = GameConfig::leveling();

        $user = $request->user();
        $connectedSnapshot = $user instanceof User
            ? $this->buildConnectedSnapshot($user, $races, $weapons, $powers)
            : null;

        $hud = $connectedSnapshot !== null
            ? $connectedSnapshot['hud']
            : [
                'mode' => 'sandbox',
                'player_name' => 'Copiste Invité',
                'spin_charges' => GameConfig::spinEnergy()['starter_charges'],
                'spin_max' => GameConfig::spinEnergy()['max'],
                'pity_counter' => 0,
                'pity_threshold' => GameConfig::pityThreshold(),
                'coins' => 250,
                'elo' => 1000,
                'league_label' => League::fromElo(1000)->label(),
                'level' => 1,
                'level_title' => $leveling['titles'][1] ?? 'Néophyte des Étoiles',
                'arena_tickets' => GameConfig::arenaEnergy()['starter_tickets'],
            ];

        $codexPayload = [
            'races' => $races,
            'weapons' => $weapons,
            'powers' => $powers,
            'rarities' => $rarities,
            'synergies' => $synergyTable,
            'paperDollMap' => $paperDollMap,
            'showcaseFolios' => $showcaseFolios,
            'connected' => $connectedSnapshot,
            'config' => [
                'baseStats' => GameConfig::baseStats(),
                'powerWeights' => GameConfig::powerWeights(),
                'rarityWeightsNormal' => GameConfig::rarityWeights(false),
                'rarityWeightsLucky' => GameConfig::rarityWeights(true),
                'pityThreshold' => GameConfig::pityThreshold(),
                'spinEnergy' => GameConfig::spinEnergy(),
                'workshop' => $workshop,
            ],
            'hud' => $hud,
        ];

        return response()
            ->view('newda.showcase', [
                'races' => $races,
                'weapons' => $weapons,
                'powers' => $powers,
                'rarities' => $rarities,
                'leagues' => $leagues,
                'showcaseFolios' => $showcaseFolios,
                'workshop' => $workshop,
                'hud' => $hud,
                'connectedSnapshot' => $connectedSnapshot,
                'codexPayload' => $codexPayload,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRaces(): array
    {
        return array_values(array_map(function (array $race): array {
            $id = (string) $race['id'];
            $meta = self::RACE_GLOSSES[$id] ?? [
                'gloss' => $race['passive'] ?? '',
                'origin' => 'Plan Inconnu',
                'initial' => mb_strtoupper(mb_substr((string) $race['name'], 0, 1)),
                'glyph' => 'compass',
            ];

            return array_merge($race, [
                'codex_gloss' => $meta['gloss'],
                'codex_origin' => $meta['origin'],
                'codex_initial' => $meta['initial'],
                'codex_glyph' => $meta['glyph'],
                'has_portrait' => PaperDollService::getRaceAsset($id) !== null,
            ]);
        }, RaceData::all()));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildWeapons(): array
    {
        return array_values(array_map(function (array $weapon): array {
            $id = (string) $weapon['id'];

            return array_merge($weapon, [
                'codex_gloss' => self::WEAPON_GLOSSES[$id] ?? (string) ($weapon['description'] ?? ''),
                'has_asset' => PaperDollService::getWeaponAsset($id) !== null,
            ]);
        }, WeaponData::all()));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildPowers(): array
    {
        return array_values(array_map(function (array $power): array {
            $id = (string) $power['id'];
            $pigment = self::POWER_PIGMENTS[$id] ?? [
                'pigment_name' => 'Encre Sépia',
                'pigment_hex' => '#6B4A2A',
                'pigment_soft' => 'rgba(107, 74, 42, 0.16)',
                'sigil_label' => 'Cercle arcanique',
                'gloss' => 'Encre Ancienne',
            ];

            return array_merge($power, [
                'pigment_name' => $pigment['pigment_name'],
                'pigment_hex' => $pigment['pigment_hex'],
                'pigment_soft' => $pigment['pigment_soft'],
                'sigil_label' => $pigment['sigil_label'],
                'codex_gloss' => $pigment['gloss'],
            ]);
        }, PowerData::all()));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRarities(): array
    {
        return array_values(array_map(function (Rarity $rarity): array {
            $seal = self::RARITY_SEALS[$rarity->value] ?? self::RARITY_SEALS['common'];

            return [
                'value' => $rarity->value,
                'label' => $rarity->label(),
                'recycle_multiplier' => $rarity->recycleMultiplier(),
                'seal_hex' => $seal['seal_hex'],
                'seal_name' => $seal['seal_name'],
                'seal_border' => $seal['seal_border'],
            ];
        }, Rarity::cases()));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildLeagues(): array
    {
        return array_values(array_map(fn (League $league): array => [
            'value' => $league->value,
            'label' => $league->label(),
            'min_elo' => $league->minElo(),
            'max_elo' => $league->maxElo(),
        ], League::cases()));
    }

    /**
     * Table creuse pré-calculée en PHP sur les 8 × 8 × 8 = 512 triplets.
     *
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array<string, list<array<string, mixed>>>
     */
    private function buildSparseSynergyTable(array $races, array $weapons, array $powers): array
    {
        $table = [];

        foreach ($races as $race) {
            foreach ($weapons as $weapon) {
                foreach ($powers as $power) {
                    $detected = SynergyData::detect(
                        (string) $race['id'],
                        (string) $weapon['id'],
                        (string) $power['id'],
                    );

                    if ($detected !== []) {
                        $key = "{$race['id']}:{$weapon['id']}:{$power['id']}";
                        $table[$key] = $detected;
                    }
                }
            }
        }

        return $table;
    }

    /**
     * Résout les calques Paper Doll réels pour l'Humain × 8 armes × 8 pouvoirs.
     *
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array<string, array<string, array{race: ?string, weapon: ?string, aura: ?string, is_invisibility: bool, is_pale_aura: bool}>>
     */
    private function buildHumanPaperDollMap(array $weapons, array $powers): array
    {
        $map = [];

        foreach ($weapons as $weapon) {
            $weaponId = (string) $weapon['id'];
            $map[$weaponId] = [];

            foreach ($powers as $power) {
                $powerId = (string) $power['id'];
                $layers = PaperDollService::resolveLayers('human', $weaponId, $powerId, 'rare');
                $aura = $layers['aura'];

                $map[$weaponId][$powerId] = [
                    'race' => $layers['race'] !== null ? asset($layers['race']) : null,
                    'weapon' => $layers['weapon'] !== null ? asset($layers['weapon']) : null,
                    'aura' => $aura !== null ? asset($aura) : null,
                    'is_invisibility' => $aura !== null && PaperDollService::isInvisibilityAura($powerId, $aura),
                    'is_pale_aura' => $aura !== null && in_array($powerId, ['ice', 'lightning'], true),
                ];
            }
        }

        return $map;
    }

    /**
     * Construit 6 Folios vitrine réalistes couvrant les 4 raretés, synergies, états et assets réels.
     *
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return list<array<string, mixed>>
     */
    private function buildShowcaseFolios(array $races, array $weapons, array $powers): array
    {
        $raceIndex = $this->indexById($races);
        $weaponIndex = $this->indexById($weapons);
        $powerIndex = $this->indexById($powers);

        $blueprints = [
            [
                'folio_number' => '001',
                'name' => 'Valérian Brise-Fer',
                'race_id' => 'human',
                'weapon_id' => 'sword',
                'power_id' => 'fire',
                'rarity' => Rarity::Rare,
                'hp' => 92,
                'strength' => 84,
                'speed' => 62,
                'defense' => 76,
                'is_locked' => false,
                'is_favorite' => true,
                'note' => 'Consigné au feu de cinabre : sa garde ne cède ni au fer ni à la braise.',
            ],
            [
                'folio_number' => '002',
                'name' => 'Syrus l\'Archiviste',
                'race_id' => 'human',
                'weapon_id' => 'staff',
                'power_id' => 'ice',
                'rarity' => Rarity::Epic,
                'hp' => 96,
                'strength' => 66,
                'speed' => 74,
                'defense' => 68,
                'is_locked' => true,
                'is_favorite' => false,
                'note' => 'Protégé par Sceau de Garde : gèle l\'encre même des sorts adverses.',
            ],
            [
                'folio_number' => '003',
                'name' => 'Kael le Voile-Lame',
                'race_id' => 'human',
                'weapon_id' => 'sword',
                'power_id' => 'invisibility',
                'rarity' => Rarity::Legendary,
                'hp' => 98,
                'strength' => 88,
                'speed' => 84,
                'defense' => 78,
                'is_locked' => true,
                'is_favorite' => true,
                'note' => 'Seul le contour d\'encre trahit son acier ; le premier coup frappe dans le vide.',
            ],
            [
                'folio_number' => '004',
                'name' => 'Isolde des Tomes',
                'race_id' => 'human',
                'weapon_id' => 'grimoire',
                'power_id' => 'lightning',
                'rarity' => Rarity::Common,
                'hp' => 74,
                'strength' => 78,
                'speed' => 58,
                'defense' => 54,
                'is_locked' => false,
                'is_favorite' => false,
                'note' => 'Feuillets crépitants d\'ocre d\'or : ses incantations doublent d\'ardeur.',
            ],
            [
                'folio_number' => '005',
                'name' => 'Malakor Givre-Cendre',
                'race_id' => 'demon',
                'weapon_id' => 'hammer',
                'power_id' => 'ice',
                'rarity' => Rarity::Epic,
                'hp' => 112,
                'strength' => 94,
                'speed' => 46,
                'defense' => 48,
                'is_locked' => false,
                'is_favorite' => false,
                'note' => 'Fissure arcanique relevée par l\'Ordre : la glace y craquelle sous la braise.',
            ],
            [
                'folio_number' => '006',
                'name' => 'Unit-42 l\'Automate',
                'race_id' => 'robot',
                'weapon_id' => 'gun',
                'power_id' => 'lightning',
                'rarity' => Rarity::Legendary,
                'hp' => 104,
                'strength' => 82,
                'speed' => 64,
                'defense' => 92,
                'is_locked' => false,
                'is_favorite' => true,
                'note' => 'Arraché au Plan de Fer : son arquebuse d\'éther recharge son châssis d\'airain.',
            ],
        ];

        $folios = [];

        foreach ($blueprints as $item) {
            $folios[] = $this->formatFolio(
                folioNumber: $item['folio_number'],
                id: 'showcase-'.$item['folio_number'],
                name: $item['name'],
                race: $raceIndex[$item['race_id']],
                weapon: $weaponIndex[$item['weapon_id']],
                power: $powerIndex[$item['power_id']],
                rarity: $item['rarity'],
                stats: [
                    'hp' => $item['hp'],
                    'strength' => $item['strength'],
                    'speed' => $item['speed'],
                    'defense' => $item['defense'],
                ],
                isLocked: $item['is_locked'],
                isFavorite: $item['is_favorite'],
                note: $item['note'],
            );
        }

        return $folios;
    }

    /**
     * Construit un instantané 100% lecture seule du compte connecté (zéro écriture BDD).
     *
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array<string, mixed>
     */
    private function buildConnectedSnapshot(User $user, array $races, array $weapons, array $powers): array
    {
        $raceIndex = $this->indexById($races);
        $weaponIndex = $this->indexById($weapons);
        $powerIndex = $this->indexById($powers);

        $characters = $user->characters()
            ->where('pending_reveal', false)
            ->latest()
            ->get();
        $folios = [];
        $index = 1;

        foreach ($characters as $character) {
            /** @var Character $character */
            $race = $raceIndex[$character->race_id] ?? $races[0];
            $weapon = $weaponIndex[$character->weapon_id] ?? $weapons[0];
            $power = $powerIndex[$character->power_id] ?? $powers[0];
            $rarity = $character->rarity instanceof Rarity ? $character->rarity : Rarity::Common;

            $folios[] = $this->formatFolio(
                folioNumber: str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                id: (string) $character->id,
                name: (string) $character->name,
                race: $race,
                weapon: $weapon,
                power: $power,
                rarity: $rarity,
                stats: [
                    'hp' => (int) $character->hp,
                    'strength' => (int) $character->strength,
                    'speed' => (int) $character->speed,
                    'defense' => (int) $character->defense,
                ],
                isLocked: (bool) $character->is_locked,
                isFavorite: (bool) $character->is_favorite,
                note: sprintf(
                    'Archivé dans votre Grimoire · %dV / %dD.',
                    (int) $character->wins,
                    (int) $character->losses,
                ),
                overridePowerScore: (int) $character->power_score,
                overrideRecycleValue: (int) $character->recycle_value,
            );
            $index++;
        }

        $elo = (int) ($user->elo ?? 1000);
        $league = League::fromElo($elo);

        return [
            'user_name' => (string) $user->name,
            'user_email' => (string) $user->email,
            'is_admin' => $user->isAdmin(),
            'folios_count' => count($folios),
            'folios' => $folios,
            'hud' => [
                'mode' => 'connected',
                'player_name' => (string) $user->name,
                'spin_charges' => (int) $user->spin_charges,
                'spin_max' => GameConfig::spinEnergy()['max'],
                'pity_counter' => (int) $user->pity_counter,
                'pity_threshold' => GameConfig::pityThreshold(),
                'coins' => (int) $user->coins,
                'elo' => $elo,
                'league_label' => $league->label(),
                'level' => max(1, (int) $user->level),
                'level_title' => $user->getLevelTitle(),
                'arena_tickets' => (int) $user->arena_tickets,
            ],
        ];
    }

    /**
     * Formate un Folio en appliquant strictement la règle Paper Doll Codex :
     * jamais d'arme flottante isolée si le calque race est absent.
     *
     * @param  array<string, mixed>  $race
     * @param  array<string, mixed>  $weapon
     * @param  array<string, mixed>  $power
     * @param  array{hp: int, strength: int, speed: int, defense: int}  $stats
     * @return array<string, mixed>
     */
    private function formatFolio(
        string $folioNumber,
        string $id,
        string $name,
        array $race,
        array $weapon,
        array $power,
        Rarity $rarity,
        array $stats,
        bool $isLocked,
        bool $isFavorite,
        string $note,
        ?int $overridePowerScore = null,
        ?int $overrideRecycleValue = null,
    ): array {
        $raceId = (string) $race['id'];
        $weaponId = (string) $weapon['id'];
        $powerId = (string) $power['id'];

        $synergies = SynergyData::detect($raceId, $weaponId, $powerId);
        $isUnstable = collect($synergies)->contains('is_unstable', true);

        $powerScore = $overridePowerScore ?? $this->calculatePowerScore($stats, $synergies, $isUnstable);
        $recycleValue = $overrideRecycleValue ?? $this->calculateRecycleValue($powerScore, count($synergies), $rarity);

        $resolved = PaperDollService::resolveLayers($raceId, $weaponId, $powerId, $rarity->value);
        $hasPortrait = ! empty($resolved['race']);

        // Règle critique Codex : conditionner tout le Paper Doll à !empty($resolved['race'])
        // afin qu'un non-Humain affiche un blason gravé héraldique et jamais une arme coupée flottante.
        $layers = $hasPortrait
            ? [
                'race' => asset((string) $resolved['race']),
                'weapon' => $resolved['weapon'] !== null ? asset($resolved['weapon']) : null,
                'aura' => $resolved['aura'] !== null ? asset($resolved['aura']) : null,
                'is_invisibility' => $resolved['aura'] !== null && PaperDollService::isInvisibilityAura($powerId, $resolved['aura']),
                'is_pale_aura' => $resolved['aura'] !== null && in_array($powerId, ['ice', 'lightning'], true),
            ]
            : [
                'race' => null,
                'weapon' => null,
                'aura' => null,
                'is_invisibility' => false,
                'is_pale_aura' => false,
            ];

        $seal = self::RARITY_SEALS[$rarity->value] ?? self::RARITY_SEALS['common'];

        return [
            'id' => $id,
            'folio_number' => $folioNumber,
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
            'race_id' => $raceId,
            'race_name' => (string) $race['name'],
            'race_gloss' => (string) $race['codex_gloss'],
            'race_initial' => (string) $race['codex_initial'],
            'race_glyph' => (string) $race['codex_glyph'],
            'race_passive' => (string) $race['passive'],
            'race_passive_desc' => (string) $race['passive_desc'],
            'weapon_id' => $weaponId,
            'weapon_name' => (string) $weapon['name'],
            'weapon_gloss' => (string) $weapon['codex_gloss'],
            'weapon_passive' => (string) $weapon['passive'],
            'weapon_passive_desc' => (string) $weapon['passive_desc'],
            'power_id' => $powerId,
            'power_name' => (string) $power['name'],
            'power_tier' => (string) $power['tier'],
            'power_tier_label' => (string) $power['tier_label'],
            'power_pigment_name' => (string) $power['pigment_name'],
            'power_pigment_hex' => (string) $power['pigment_hex'],
            'power_pigment_soft' => (string) $power['pigment_soft'],
            'rarity' => $rarity->value,
            'rarity_label' => $rarity->label(),
            'seal_hex' => $seal['seal_hex'],
            'seal_name' => $seal['seal_name'],
            'hp' => $stats['hp'],
            'strength' => $stats['strength'],
            'speed' => $stats['speed'],
            'defense' => $stats['defense'],
            'power_score' => $powerScore,
            'recycle_value' => $recycleValue,
            'is_locked' => $isLocked,
            'is_favorite' => $isFavorite,
            'is_unstable' => $isUnstable,
            'synergies' => $synergies,
            'note' => $note,
            'has_portrait' => $hasPortrait,
            'layers' => $layers,
        ];
    }

    /**
     * @param  array{hp: int, strength: int, speed: int, defense: int}  $stats
     * @param  list<array<string, mixed>>  $synergies
     */
    private function calculatePowerScore(array $stats, array $synergies, bool $isUnstable): int
    {
        $weights = GameConfig::powerWeights();
        $raw = ($stats['hp'] * $weights['hp'])
            + ($stats['strength'] * $weights['strength'])
            + ($stats['defense'] * $weights['defense'])
            + ($stats['speed'] * $weights['speed'])
            + (count($synergies) * $weights['synergy_bonus'])
            + ($isUnstable ? $weights['unstable_bonus'] : 0);

        return max(20, min(100, (int) round($raw)));
    }

    private function calculateRecycleValue(int $powerScore, int $synergyCount, Rarity $rarity): int
    {
        $raw = ($powerScore * 1.2) + ($synergyCount * 25);

        return max(35, (int) round($raw * $rarity->recycleMultiplier()));
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, array<string, mixed>>
     */
    private function indexById(array $items): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[(string) $item['id']] = $item;
        }

        return $indexed;
    }
}
