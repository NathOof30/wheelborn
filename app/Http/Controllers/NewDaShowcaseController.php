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
use App\Models\WorkshopUpgrade;
use App\Services\PaperDollService;
use App\Support\NewDaIconRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewDaShowcaseController extends Controller
{
    /** @var array<string, array{pigment_hex: string, pigment_soft: string}> */
    private const POWER_PIGMENTS = [
        'fire' => [
            'pigment_hex' => '#8A1A14',
            'pigment_soft' => 'rgba(138, 26, 20, 0.16)',
        ],
        'poison' => [
            'pigment_hex' => '#3E5438',
            'pigment_soft' => 'rgba(62, 84, 56, 0.16)',
        ],
        'ice' => [
            'pigment_hex' => '#2A3A7A',
            'pigment_soft' => 'rgba(42, 58, 122, 0.18)',
        ],
        'lightning' => [
            'pigment_hex' => '#75500A',
            'pigment_soft' => 'rgba(154, 111, 24, 0.22)',
        ],
        'regeneration' => [
            'pigment_hex' => '#456037',
            'pigment_soft' => 'rgba(69, 96, 55, 0.16)',
        ],
        'gravity' => [
            'pigment_hex' => '#2F2A45',
            'pigment_soft' => 'rgba(47, 42, 69, 0.18)',
        ],
        'teleportation' => [
            'pigment_hex' => '#54274A',
            'pigment_soft' => 'rgba(84, 39, 74, 0.18)',
        ],
        'invisibility' => [
            'pigment_hex' => '#5C5349',
            'pigment_soft' => 'rgba(92, 83, 73, 0.16)',
        ],
    ];

    /** @var array<string, array{seal_hex: string, seal_name: string}> */
    private const RARITY_SEALS = [
        'common' => [
            'seal_hex' => '#6B4A2A',
            'seal_name' => 'Argile',
        ],
        'rare' => [
            'seal_hex' => '#2A3A7A',
            'seal_name' => 'Outremer',
        ],
        'epic' => [
            'seal_hex' => '#54274A',
            'seal_name' => 'Pourpre',
        ],
        'legendary' => [
            'seal_hex' => '#74520A',
            'seal_name' => 'Or',
        ],
    ];

    /** @var array<string, string> */
    private const LEAGUE_INK_COLORS = [
        'bronze' => '#8A5226',
        'silver' => '#536270',
        'gold' => '#75500A',
        'platinum' => '#1F5C7A',
        'master' => '#5C2B6E',
    ];

    public function __construct(
        private readonly NewDaIconRegistry $iconRegistry,
    ) {}

    public function invocation(Request $request): Response
    {
        $races = $this->buildRaces();
        $weapons = $this->buildWeapons();
        $powers = $this->buildPowers();
        $rarities = $this->buildRarities();
        $workshop = GameConfig::workshop();
        $icons = $this->iconRegistry->allByCategory();
        $powerSigils = $this->iconRegistry->powerSigils();

        $user = $request->user();
        $connectedMeta = $user instanceof User ? $this->buildConnectedMeta($user) : null;
        $hud = $this->buildHud($user);
        $workshopLevels = $this->readWorkshopLevels($user);

        $synergies = $this->buildSparseSynergyTable($races, $weapons, $powers);
        $paperDollMap = $this->buildPaperDollMap($races, $weapons, $powers);

        $codexPayload = $this->basePayload('invocation', $user, $hud, $icons, $powerSigils, $workshopLevels, $connectedMeta) + [
            'races' => $races,
            'weapons' => $weapons,
            'powers' => $powers,
            'rarities' => $rarities,
            'synergies' => $synergies,
            'paperDollMap' => $paperDollMap,
            'config' => [
                'baseStats' => GameConfig::baseStats(),
                'powerWeights' => GameConfig::powerWeights(),
                'rarityWeightsNormal' => GameConfig::rarityWeights(false),
                'rarityWeightsLucky' => GameConfig::rarityWeights(true),
                'pityThreshold' => GameConfig::pityThreshold(),
                'spinEnergy' => GameConfig::spinEnergy(),
                'workshop' => $workshop,
            ],
        ];

        return $this->codexResponse('newda.invocation', [
            'pageId' => 'invocation',
            'pageTitle' => 'Tirage — Wheelborn',
            'hud' => $hud,
            'connectedMeta' => $connectedMeta,
            'workshop' => $workshop,
            'iconRegistry' => $this->iconRegistry,
            'codexPayload' => $codexPayload,
        ]);
    }

    public function grimoire(Request $request): Response
    {
        $races = $this->buildRaces();
        $weapons = $this->buildWeapons();
        $powers = $this->buildPowers();
        $rarities = $this->buildRarities();
        $icons = $this->iconRegistry->allByCategory();
        $powerSigils = $this->iconRegistry->powerSigils();
        $showcaseFolios = $this->buildShowcaseFolios($races, $weapons, $powers);

        $user = $request->user();
        $connectedSnapshot = $user instanceof User
            ? $this->buildConnectedSnapshot($user, $races, $weapons, $powers)
            : null;
        $hud = $this->buildHud($user);
        $workshopLevels = $this->readWorkshopLevels($user);
        $initialFolios = $connectedSnapshot !== null ? $connectedSnapshot['folios'] : $showcaseFolios;

        $codexPayload = $this->basePayload('grimoire', $user, $hud, $icons, $powerSigils, $workshopLevels, $connectedSnapshot) + [
            'rarities' => $rarities,
            'showcaseFolios' => $showcaseFolios,
        ];

        return $this->codexResponse('newda.grimoire', [
            'pageId' => 'grimoire',
            'pageTitle' => 'Grimoire — Wheelborn',
            'hud' => $hud,
            'rarities' => $rarities,
            'showcaseFolios' => $showcaseFolios,
            'initialFolios' => $initialFolios,
            'connectedSnapshot' => $connectedSnapshot,
            'connectedMeta' => $connectedSnapshot,
            'iconRegistry' => $this->iconRegistry,
            'codexPayload' => $codexPayload,
        ]);
    }

    public function arena(Request $request): Response
    {
        $races = $this->buildRaces();
        $weapons = $this->buildWeapons();
        $powers = $this->buildPowers();
        $icons = $this->iconRegistry->allByCategory();
        $powerSigils = $this->iconRegistry->powerSigils();
        $showcaseFolios = $this->buildShowcaseFolios($races, $weapons, $powers);

        $user = $request->user();
        $connectedSnapshot = $user instanceof User
            ? $this->buildConnectedSnapshot($user, $races, $weapons, $powers)
            : null;
        $hud = $this->buildHud($user);
        $workshopLevels = $this->readWorkshopLevels($user);

        $initialPool = $connectedSnapshot !== null ? $connectedSnapshot['folios'] : $showcaseFolios;
        $initialLeftFolio = $initialPool[0] ?? null;
        $initialRightIndex = count($initialPool) > 4 ? 4 : (count($initialPool) > 1 ? 1 : 0);
        $initialRightFolio = count($initialPool) >= 2 ? ($initialPool[$initialRightIndex] ?? null) : null;

        $codexPayload = $this->basePayload('arena', $user, $hud, $icons, $powerSigils, $workshopLevels, $connectedSnapshot) + [
            'showcaseFolios' => $showcaseFolios,
            'config' => [
                'rewards' => GameConfig::rewards(),
                'workshop' => GameConfig::workshop(),
            ],
        ];

        return $this->codexResponse('newda.arena', [
            'pageId' => 'arena',
            'pageTitle' => 'Arène — Wheelborn',
            'hud' => $hud,
            'showcaseFolios' => $showcaseFolios,
            'initialLeftFolio' => $initialLeftFolio,
            'initialRightFolio' => $initialRightFolio,
            'connectedSnapshot' => $connectedSnapshot,
            'connectedMeta' => $connectedSnapshot,
            'iconRegistry' => $this->iconRegistry,
            'codexPayload' => $codexPayload,
        ]);
    }

    public function leaderboard(Request $request): Response
    {
        $races = $this->buildRaces();
        $weapons = $this->buildWeapons();
        $powers = $this->buildPowers();
        $leagues = $this->buildLeagues();
        $icons = $this->iconRegistry->allByCategory();
        $powerSigils = $this->iconRegistry->powerSigils();
        $standingsByLeague = $this->buildShowcaseStandings($races, $weapons, $powers);

        $user = $request->user();
        $connectedMeta = $user instanceof User ? $this->buildConnectedMeta($user) : null;
        $hud = $this->buildHud($user);
        $workshopLevels = $this->readWorkshopLevels($user);
        $pinnedPlayer = $user instanceof User
            ? $this->buildPinnedLeaderboardRow($user, $standingsByLeague, $races, $weapons, $powers)
            : null;

        $activeLeague = $pinnedPlayer !== null ? $pinnedPlayer['league_value'] : League::Silver->value;

        $codexPayload = $this->basePayload('leaderboard', $user, $hud, $icons, $powerSigils, $workshopLevels, $connectedMeta) + [
            'leagues' => $leagues,
            'activeLeague' => $activeLeague,
            'standingsByLeague' => $standingsByLeague,
            'pinnedPlayer' => $pinnedPlayer,
        ];

        return $this->codexResponse('newda.leaderboard', [
            'pageId' => 'leaderboard',
            'pageTitle' => 'Rang — Wheelborn',
            'hud' => $hud,
            'leagues' => $leagues,
            'activeLeague' => $activeLeague,
            'standingsByLeague' => $standingsByLeague,
            'pinnedPlayer' => $pinnedPlayer,
            'connectedMeta' => $connectedMeta,
            'iconRegistry' => $this->iconRegistry,
            'codexPayload' => $codexPayload,
        ]);
    }

    public function workshop(Request $request): Response
    {
        $workshop = GameConfig::workshop();
        $icons = $this->iconRegistry->allByCategory();
        $powerSigils = $this->iconRegistry->powerSigils();

        $user = $request->user();
        $connectedMeta = $user instanceof User ? $this->buildConnectedMeta($user) : null;
        $hud = $this->buildHud($user);
        $workshopLevels = $this->readWorkshopLevels($user);
        $workshopCards = $this->buildWorkshopCards($workshop, $workshopLevels);

        $codexPayload = $this->basePayload('workshop', $user, $hud, $icons, $powerSigils, $workshopLevels, $connectedMeta) + [
            'workshopCards' => $workshopCards,
            'config' => [
                'workshop' => $workshop,
            ],
        ];

        return $this->codexResponse('newda.workshop', [
            'pageId' => 'workshop',
            'pageTitle' => 'Atelier — Wheelborn',
            'hud' => $hud,
            'workshopCards' => $workshopCards,
            'connectedMeta' => $connectedMeta,
            'iconRegistry' => $this->iconRegistry,
            'codexPayload' => $codexPayload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function codexResponse(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * @param  array<string, mixed>  $hud
     * @param  array<string, mixed>  $icons
     * @param  array<string, string>  $powerSigils
     * @param  array{rare_magnet: int, hero_forge: int, arena_bonus: int}  $workshopLevels
     * @param  ?array<string, mixed>  $connected
     * @return array<string, mixed>
     */
    private function basePayload(
        string $pageId,
        ?User $user,
        array $hud,
        array $icons,
        array $powerSigils,
        array $workshopLevels,
        ?array $connected,
    ): array {
        return [
            'page' => $pageId,
            'routes' => [
                'invocation' => route('newda.invocation'),
                'grimoire' => route('newda.grimoire'),
                'arena' => route('newda.arena'),
                'leaderboard' => route('newda.leaderboard'),
                'workshop' => route('newda.workshop'),
            ],
            'storageScope' => $user instanceof User ? 'user:'.$user->id : 'guest',
            'isAuthenticated' => $user instanceof User,
            'showcaseCount' => 6,
            'hud' => $hud,
            'icons' => $icons,
            'powerSigils' => $powerSigils,
            'workshopLevels' => $workshopLevels,
            'connected' => $connected,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHud(?User $user): array
    {
        $leveling = GameConfig::leveling();

        if ($user instanceof User) {
            $elo = (int) ($user->elo ?? 1000);
            $league = League::fromElo($elo);

            return [
                'mode' => 'connected',
                'player_name' => (string) $user->name,
                'spin_charges' => (int) $user->spin_charges,
                'spin_max' => GameConfig::spinEnergy()['max'],
                'pity_counter' => (int) $user->pity_counter,
                'pity_threshold' => GameConfig::pityThreshold(),
                'coins' => (int) $user->coins,
                'elo' => $elo,
                'league_value' => $league->value,
                'league_label' => $league->label(),
                'league_color' => self::LEAGUE_INK_COLORS[$league->value] ?? '#536270',
                'level' => max(1, (int) $user->level),
                'level_title' => $user->getLevelTitle(),
                'arena_tickets' => (int) $user->arena_tickets,
            ];
        }

        $guestLeague = League::fromElo(1000);

        return [
            'mode' => 'sandbox',
            'player_name' => 'Invité',
            'spin_charges' => GameConfig::spinEnergy()['starter_charges'],
            'spin_max' => GameConfig::spinEnergy()['max'],
            'pity_counter' => 0,
            'pity_threshold' => GameConfig::pityThreshold(),
            'coins' => 250,
            'elo' => 1000,
            'league_value' => $guestLeague->value,
            'league_label' => $guestLeague->label(),
            'league_color' => self::LEAGUE_INK_COLORS[$guestLeague->value] ?? '#536270',
            'level' => 1,
            'level_title' => $leveling['titles'][1] ?? 'Néophyte des Étoiles',
            'arena_tickets' => GameConfig::arenaEnergy()['starter_tickets'],
        ];
    }

    /**
     * @return array{rare_magnet: int, hero_forge: int, arena_bonus: int}
     */
    private function readWorkshopLevels(?User $user): array
    {
        if (! $user instanceof User) {
            return [
                'rare_magnet' => 0,
                'hero_forge' => 0,
                'arena_bonus' => 0,
            ];
        }

        $record = $user->workshopUpgrades()->first();
        if (! $record instanceof WorkshopUpgrade) {
            return [
                'rare_magnet' => 0,
                'hero_forge' => 0,
                'arena_bonus' => 0,
            ];
        }

        return [
            'rare_magnet' => $record->levelOf('rare_magnet'),
            'hero_forge' => $record->levelOf('hero_forge'),
            'arena_bonus' => $record->levelOf('arena_bonus'),
        ];
    }

    /**
     * @param  array<string, mixed>  $workshop
     * @param  array{rare_magnet: int, hero_forge: int, arena_bonus: int}  $levels
     * @return list<array<string, mixed>>
     */
    private function buildWorkshopCards(array $workshop, array $levels): array
    {
        return [
            [
                'key' => 'rare_magnet',
                'name' => (string) $workshop['rare_magnet']['name'],
                'icon_key' => 'magnet',
                'level' => $levels['rare_magnet'],
                'max_level' => (int) $workshop['rare_magnet']['max_level'],
                'costs' => array_values($workshop['rare_magnet']['costs']),
                'bonus_per_level' => (int) $workshop['rare_magnet']['bonus_per_level'],
                'unit_label' => 'Mythique',
            ],
            [
                'key' => 'hero_forge',
                'name' => (string) $workshop['hero_forge']['name'],
                'icon_key' => 'anvil',
                'level' => $levels['hero_forge'],
                'max_level' => (int) $workshop['hero_forge']['max_level'],
                'costs' => array_values($workshop['hero_forge']['costs']),
                'bonus_hp' => (int) $workshop['hero_forge']['bonus_hp'],
                'bonus_str' => (int) $workshop['hero_forge']['bonus_str'],
            ],
            [
                'key' => 'arena_bonus',
                'name' => (string) $workshop['arena_bonus']['name'],
                'icon_key' => 'chest',
                'level' => $levels['arena_bonus'],
                'max_level' => (int) $workshop['arena_bonus']['max_level'],
                'costs' => array_values($workshop['arena_bonus']['costs']),
                'multipliers' => array_values($workshop['arena_bonus']['multipliers']),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildConnectedMeta(User $user): array
    {
        $foliosCount = $user->characters()
            ->where('pending_reveal', false)
            ->count();

        return [
            'user_id' => $user->id,
            'user_name' => (string) $user->name,
            'is_admin' => $user->isAdmin(),
            'folios_count' => $foliosCount,
        ];
    }

    /**
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
                isLuckySpin: (bool) $character->is_lucky_spin,
                wins: (int) ($character->wins ?? 0),
                losses: (int) ($character->losses ?? 0),
                overridePowerScore: (int) $character->power_score,
                overrideRecycleValue: (int) $character->recycle_value,
            );
            $index++;
        }

        return [
            'user_id' => $user->id,
            'user_name' => (string) $user->name,
            'is_admin' => $user->isAdmin(),
            'folios_count' => count($folios),
            'folios' => $folios,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRaces(): array
    {
        return array_values(array_map(function (array $race): array {
            $id = (string) $race['id'];

            return array_merge($race, [
                'initial' => mb_strtoupper(mb_substr((string) $race['name'], 0, 1)),
                'has_portrait' => PaperDollService::getRaceAsset($id) !== null,
                'icon_asset' => $this->iconRegistry->resolve('races', $id),
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
                'has_asset' => PaperDollService::getWeaponAsset($id) !== null,
                'icon_asset' => $this->iconRegistry->resolve('weapons', $id),
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
                'pigment_hex' => '#6B4A2A',
                'pigment_soft' => 'rgba(107, 74, 42, 0.16)',
            ];

            return array_merge($power, [
                'pigment_hex' => $pigment['pigment_hex'],
                'pigment_soft' => $pigment['pigment_soft'],
                'icon_asset' => $this->iconRegistry->resolve('powers', $id),
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
            'color' => self::LEAGUE_INK_COLORS[$league->value] ?? '#536270',
        ], League::cases()));
    }

    /**
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
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array{
     *     races: array<string, string>,
     *     equipment: array<string, array<string, array{weapon: ?string, aura: ?string, is_invisibility: bool, is_pale_aura: bool, is_teleportation_aura: bool}>>
     * }
     */
    private function buildPaperDollMap(array $races, array $weapons, array $powers): array
    {
        $raceAssets = [];
        foreach ($races as $race) {
            $raceId = (string) $race['id'];
            $assetPath = PaperDollService::getRaceAsset($raceId);
            if ($assetPath !== null) {
                $raceAssets[$raceId] = asset($assetPath);
            }
        }

        $equipment = [];
        foreach ($weapons as $weapon) {
            $weaponId = (string) $weapon['id'];
            $equipment[$weaponId] = [];

            foreach ($powers as $power) {
                $powerId = (string) $power['id'];
                $layers = PaperDollService::resolveLayers('human', $weaponId, $powerId, 'rare');
                $aura = $layers['aura'];

                $equipment[$weaponId][$powerId] = [
                    'weapon' => $layers['weapon'] !== null ? asset($layers['weapon']) : null,
                    'aura' => $aura !== null ? asset($aura) : null,
                    'is_invisibility' => $aura !== null && PaperDollService::isInvisibilityAura($powerId, $aura),
                    'is_pale_aura' => $aura !== null && in_array($powerId, ['ice', 'lightning'], true),
                    'is_teleportation_aura' => $aura !== null && $powerId === 'teleportation',
                ];
            }
        }

        return [
            'races' => $raceAssets,
            'equipment' => $equipment,
        ];
    }

    /**
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
                'is_lucky_spin' => false,
                'wins' => 7,
                'losses' => 2,
            ],
            [
                'folio_number' => '002',
                'name' => 'Sylvae des Hauts-Bois',
                'race_id' => 'elf',
                'weapon_id' => 'bow',
                'power_id' => 'ice',
                'rarity' => Rarity::Epic,
                'hp' => 84,
                'strength' => 74,
                'speed' => 92,
                'defense' => 58,
                'is_locked' => true,
                'is_favorite' => false,
                'is_lucky_spin' => false,
                'wins' => 5,
                'losses' => 1,
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
                'is_lucky_spin' => true,
                'wins' => 12,
                'losses' => 3,
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
                'is_lucky_spin' => false,
                'wins' => 2,
                'losses' => 2,
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
                'is_lucky_spin' => false,
                'wins' => 9,
                'losses' => 4,
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
                'is_lucky_spin' => true,
                'wins' => 14,
                'losses' => 2,
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
                isLuckySpin: $item['is_lucky_spin'],
                wins: $item['wins'],
                losses: $item['losses'],
            );
        }

        return $folios;
    }

    /**
     * Classement vitrine autonome par ligue, sans aucune requête sur la table users.
     *
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array<string, list<array<string, mixed>>>
     */
    private function buildShowcaseStandings(array $races, array $weapons, array $powers): array
    {
        $raceIndex = $this->indexById($races);
        $weaponIndex = $this->indexById($weapons);
        $powerIndex = $this->indexById($powers);

        $roster = [
            League::Master->value => [
                ['name' => 'Aurelius', 'elo' => 2480, 'wins' => 64, 'losses' => 12, 'hero' => ['Valérian', 'human', 'sword', 'invisibility', Rarity::Legendary, 108, 94, 86, 84]],
                ['name' => 'Vespera', 'elo' => 2395, 'wins' => 58, 'losses' => 15, 'hero' => ['Nyxis', 'vampire', 'dagger', 'teleportation', Rarity::Legendary, 96, 92, 95, 70]],
                ['name' => 'Thalor', 'elo' => 2310, 'wins' => 52, 'losses' => 18, 'hero' => ['Kron-09', 'robot', 'gun', 'gravity', Rarity::Epic, 110, 88, 66, 96]],
                ['name' => 'Seraphine', 'elo' => 2260, 'wins' => 46, 'losses' => 19, 'hero' => ['Solène', 'human', 'staff', 'lightning', Rarity::Epic, 98, 82, 80, 76]],
                ['name' => 'Draven', 'elo' => 2215, 'wins' => 41, 'losses' => 20, 'hero' => ['Pyrrhos', 'demon', 'hammer', 'fire', Rarity::Epic, 114, 96, 52, 58]],
            ],
            League::Platinum->value => [
                ['name' => 'Kaelen', 'elo' => 2140, 'wins' => 44, 'losses' => 17, 'hero' => ['Arden', 'human', 'sword', 'fire', Rarity::Epic, 98, 88, 74, 78]],
                ['name' => 'Lyra', 'elo' => 2050, 'wins' => 39, 'losses' => 16, 'hero' => ['Sylvae', 'elf', 'bow', 'ice', Rarity::Epic, 84, 78, 94, 62]],
                ['name' => 'Balthazar', 'elo' => 1965, 'wins' => 37, 'losses' => 19, 'hero' => ['Basalt', 'golem', 'hammer', 'regeneration', Rarity::Rare, 128, 90, 36, 94]],
                ['name' => 'Mireille', 'elo' => 1890, 'wins' => 33, 'losses' => 18, 'hero' => ['Céleste', 'human', 'staff', 'ice', Rarity::Rare, 92, 74, 78, 70]],
                ['name' => 'Zorath', 'elo' => 1825, 'wins' => 29, 'losses' => 17, 'hero' => ['Astryx', 'alien', 'spear', 'gravity', Rarity::Epic, 96, 84, 82, 74]],
            ],
            League::Gold->value => [
                ['name' => 'Cassian', 'elo' => 1745, 'wins' => 31, 'losses' => 14, 'hero' => ['Syrus', 'human', 'staff', 'lightning', Rarity::Rare, 90, 76, 74, 68]],
                ['name' => 'Elowen', 'elo' => 1660, 'wins' => 28, 'losses' => 15, 'hero' => ['Faelan', 'elf', 'dagger', 'poison', Rarity::Rare, 78, 76, 92, 54]],
                ['name' => 'Garrick', 'elo' => 1580, 'wins' => 25, 'losses' => 15, 'hero' => ['Bram', 'human', 'sword', 'regeneration', Rarity::Rare, 96, 80, 64, 76]],
                ['name' => 'Nyxara', 'elo' => 1510, 'wins' => 22, 'losses' => 16, 'hero' => ['Vex-12', 'mutant', 'grimoire', 'teleportation', Rarity::Rare, 88, 84, 76, 62]],
                ['name' => 'Ronan', 'elo' => 1435, 'wins' => 20, 'losses' => 15, 'hero' => ['Orin', 'human', 'bow', 'fire', Rarity::Common, 82, 74, 82, 60]],
            ],
            League::Silver->value => [
                ['name' => 'Aldric', 'elo' => 1340, 'wins' => 19, 'losses' => 11, 'hero' => ['Alaric', 'human', 'sword', 'fire', Rarity::Rare, 88, 80, 64, 72]],
                ['name' => 'Soline', 'elo' => 1265, 'wins' => 17, 'losses' => 12, 'hero' => ['Isolde', 'human', 'grimoire', 'lightning', Rarity::Rare, 80, 82, 62, 58]],
                ['name' => 'Torin', 'elo' => 1190, 'wins' => 15, 'losses' => 11, 'hero' => ['Korg', 'golem', 'hammer', 'fire', Rarity::Common, 116, 84, 34, 86]],
                ['name' => 'Maelis', 'elo' => 1110, 'wins' => 13, 'losses' => 12, 'hero' => ['Nym', 'elf', 'bow', 'poison', Rarity::Common, 74, 70, 88, 52]],
                ['name' => 'Dorian', 'elo' => 1040, 'wins' => 10, 'losses' => 11, 'hero' => ['Lukas', 'human', 'spear', 'ice', Rarity::Common, 84, 74, 70, 66]],
            ],
            League::Bronze->value => [
                ['name' => 'Cedric', 'elo' => 950, 'wins' => 9, 'losses' => 9, 'hero' => ['Rowan', 'human', 'sword', 'poison', Rarity::Common, 80, 74, 62, 66]],
                ['name' => 'Talia', 'elo' => 870, 'wins' => 7, 'losses' => 8, 'hero' => ['Yara', 'human', 'staff', 'fire', Rarity::Common, 78, 66, 68, 62]],
                ['name' => 'Bramwell', 'elo' => 790, 'wins' => 6, 'losses' => 9, 'hero' => ['Unit-07', 'robot', 'gun', 'fire', Rarity::Common, 88, 72, 48, 82]],
                ['name' => 'Orla', 'elo' => 710, 'wins' => 5, 'losses' => 10, 'hero' => ['Mira', 'elf', 'dagger', 'poison', Rarity::Common, 68, 66, 84, 48]],
                ['name' => 'Fenris', 'elo' => 630, 'wins' => 3, 'losses' => 9, 'hero' => ['Vark', 'demon', 'hammer', 'fire', Rarity::Common, 92, 86, 44, 46]],
            ],
        ];

        $standings = [];
        $counter = 1;

        foreach (League::cases() as $league) {
            $entries = [];
            $rank = 1;

            foreach ($roster[$league->value] ?? [] as $item) {
                [$heroName, $raceId, $weaponId, $powerId, $rarity, $hp, $str, $spd, $def] = $item['hero'];
                $battles = $item['wins'] + $item['losses'];
                $winrate = $battles > 0 ? (int) round(($item['wins'] / $battles) * 100) : 0;

                $entries[] = [
                    'id' => 'rival-'.$league->value.'-'.$rank,
                    'rank' => $rank,
                    'name' => $item['name'],
                    'elo' => $item['elo'],
                    'league_value' => $league->value,
                    'league_label' => $league->label(),
                    'league_color' => self::LEAGUE_INK_COLORS[$league->value] ?? '#536270',
                    'wins' => $item['wins'],
                    'losses' => $item['losses'],
                    'battles' => $battles,
                    'winrate' => $winrate,
                    'champion' => $this->formatFolio(
                        folioNumber: str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
                        id: 'rival-folio-'.$counter,
                        name: $heroName,
                        race: $raceIndex[$raceId],
                        weapon: $weaponIndex[$weaponId],
                        power: $powerIndex[$powerId],
                        rarity: $rarity,
                        stats: [
                            'hp' => $hp,
                            'strength' => $str,
                            'speed' => $spd,
                            'defense' => $def,
                        ],
                        isLocked: false,
                        isFavorite: true,
                        isLuckySpin: $rarity === Rarity::Legendary,
                        wins: $item['wins'],
                        losses: $item['losses'],
                    ),
                ];

                $rank++;
                $counter++;
            }

            $standings[$league->value] = $entries;
        }

        return $standings;
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $standingsByLeague
     * @param  list<array<string, mixed>>  $races
     * @param  list<array<string, mixed>>  $weapons
     * @param  list<array<string, mixed>>  $powers
     * @return array<string, mixed>
     */
    private function buildPinnedLeaderboardRow(
        User $user,
        array $standingsByLeague,
        array $races,
        array $weapons,
        array $powers,
    ): array {
        $elo = (int) ($user->elo ?? 1000);
        $league = League::fromElo($elo);
        $leagueEntries = $standingsByLeague[$league->value] ?? [];
        $rank = 1;

        foreach ($leagueEntries as $entry) {
            if ($entry['elo'] > $elo) {
                $rank++;
            }
        }

        $stats = $user->battleStats();
        $topCharacter = $user->characters()
            ->where('pending_reveal', false)
            ->orderByDesc('power_score')
            ->first();

        $champion = null;
        if ($topCharacter instanceof Character) {
            $raceIndex = $this->indexById($races);
            $weaponIndex = $this->indexById($weapons);
            $powerIndex = $this->indexById($powers);
            $rarity = $topCharacter->rarity instanceof Rarity ? $topCharacter->rarity : Rarity::Common;

            $champion = $this->formatFolio(
                folioNumber: '001',
                id: (string) $topCharacter->id,
                name: (string) $topCharacter->name,
                race: $raceIndex[$topCharacter->race_id] ?? $races[0],
                weapon: $weaponIndex[$topCharacter->weapon_id] ?? $weapons[0],
                power: $powerIndex[$topCharacter->power_id] ?? $powers[0],
                rarity: $rarity,
                stats: [
                    'hp' => (int) $topCharacter->hp,
                    'strength' => (int) $topCharacter->strength,
                    'speed' => (int) $topCharacter->speed,
                    'defense' => (int) $topCharacter->defense,
                ],
                isLocked: (bool) $topCharacter->is_locked,
                isFavorite: (bool) $topCharacter->is_favorite,
                isLuckySpin: (bool) $topCharacter->is_lucky_spin,
                wins: (int) ($topCharacter->wins ?? 0),
                losses: (int) ($topCharacter->losses ?? 0),
                overridePowerScore: (int) $topCharacter->power_score,
                overrideRecycleValue: (int) $topCharacter->recycle_value,
            );
        }

        return [
            'id' => 'self-'.$user->id,
            'rank' => $rank,
            'name' => (string) $user->name,
            'elo' => $elo,
            'league_value' => $league->value,
            'league_label' => $league->label(),
            'league_color' => self::LEAGUE_INK_COLORS[$league->value] ?? '#536270',
            'wins' => $stats['wins'],
            'losses' => $stats['losses'],
            'battles' => $stats['total'],
            'winrate' => $stats['winrate'],
            'champion' => $champion,
        ];
    }

    /**
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
        bool $isLuckySpin = false,
        int $wins = 0,
        int $losses = 0,
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

        // Une race sans silhouette WebP affiche un blason gravé ; aucun calque d'arme isolé sans corps.
        $layers = $hasPortrait
            ? [
                'race' => asset((string) $resolved['race']),
                'weapon' => $resolved['weapon'] !== null ? asset($resolved['weapon']) : null,
                'aura' => $resolved['aura'] !== null ? asset($resolved['aura']) : null,
                'is_invisibility' => $resolved['aura'] !== null && PaperDollService::isInvisibilityAura($powerId, $resolved['aura']),
                'is_pale_aura' => $resolved['aura'] !== null && in_array($powerId, ['ice', 'lightning'], true),
                'is_teleportation_aura' => $resolved['aura'] !== null && $powerId === 'teleportation',
            ]
            : [
                'race' => null,
                'weapon' => null,
                'aura' => null,
                'is_invisibility' => false,
                'is_pale_aura' => false,
                'is_teleportation_aura' => false,
            ];

        $seal = self::RARITY_SEALS[$rarity->value] ?? self::RARITY_SEALS['common'];

        $raceName = (string) $race['name'];
        $racePassive = (string) ($race['passive'] ?? '');
        $racePassiveDesc = (string) ($race['passive_desc'] ?? '');
        $weaponName = (string) $weapon['name'];
        $weaponPassive = (string) ($weapon['passive'] ?? '');
        $weaponPassiveDesc = (string) ($weapon['passive_desc'] ?? '');
        $powerName = (string) $power['name'];
        $powerTier = (string) ($power['tier'] ?? 'common');
        $powerTierLabel = (string) ($power['tier_label'] ?? 'Commun');
        $powerEffectLabel = (string) ($power['effect']['label'] ?? $powerName);
        $powerDescription = (string) ($power['description'] ?? '');

        $abilities = $this->buildAbilitiesList(
            raceId: $raceId,
            raceName: $raceName,
            racePassive: $racePassive,
            racePassiveDesc: $racePassiveDesc,
            weaponId: $weaponId,
            weaponName: $weaponName,
            weaponPassive: $weaponPassive,
            weaponPassiveDesc: $weaponPassiveDesc,
            powerId: $powerId,
            powerName: $powerName,
            powerTier: $powerTier,
            powerTierLabel: $powerTierLabel,
            powerEffectLabel: $powerEffectLabel,
            powerDescription: $powerDescription,
            powerPigmentHex: (string) $power['pigment_hex'],
            synergies: $synergies,
        );

        $battles = $wins + $losses;
        $winrate = $battles > 0 ? (int) round(($wins / $battles) * 100) : null;

        return [
            'id' => $id,
            'folio_number' => $folioNumber,
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
            'race_id' => $raceId,
            'race_name' => $raceName,
            'race_initial' => (string) $race['initial'],
            'race_passive' => $racePassive,
            'race_passive_desc' => $racePassiveDesc,
            'weapon_id' => $weaponId,
            'weapon_name' => $weaponName,
            'weapon_passive' => $weaponPassive,
            'weapon_passive_desc' => $weaponPassiveDesc,
            'power_id' => $powerId,
            'power_name' => $powerName,
            'power_tier' => $powerTier,
            'power_tier_label' => $powerTierLabel,
            'power_effect_label' => $powerEffectLabel,
            'power_description' => $powerDescription,
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
            'is_lucky_spin' => $isLuckySpin,
            'is_unstable' => $isUnstable,
            'wins' => $wins,
            'losses' => $losses,
            'winrate' => $winrate,
            'synergies' => $synergies,
            'abilities' => $abilities,
            'has_portrait' => $hasPortrait,
            'layers' => $layers,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $synergies
     * @return list<array<string, mixed>>
     */
    private function buildAbilitiesList(
        string $raceId,
        string $raceName,
        string $racePassive,
        string $racePassiveDesc,
        string $weaponId,
        string $weaponName,
        string $weaponPassive,
        string $weaponPassiveDesc,
        string $powerId,
        string $powerName,
        string $powerTier,
        string $powerTierLabel,
        string $powerEffectLabel,
        string $powerDescription,
        string $powerPigmentHex,
        array $synergies,
    ): array {
        $abilities = [];

        if ($racePassive !== '') {
            $abilities[] = [
                'kind' => 'race',
                'category' => 'races',
                'icon_id' => $raceId,
                'label' => $racePassive,
                'meta' => 'Race',
                'tooltip_eyebrow' => 'RACE · '.mb_strtoupper($raceName),
                'tooltip_title' => $racePassive,
                'tooltip_desc' => $racePassiveDesc,
                'tooltip_tone' => 'default',
                'accent_hex' => null,
                'is_mythic' => false,
                'is_unstable' => false,
            ];
        }

        if ($weaponPassive !== '') {
            $abilities[] = [
                'kind' => 'weapon',
                'category' => 'weapons',
                'icon_id' => $weaponId,
                'label' => $weaponPassive,
                'meta' => 'Arme',
                'tooltip_eyebrow' => 'ARME · '.mb_strtoupper($weaponName),
                'tooltip_title' => $weaponPassive,
                'tooltip_desc' => $weaponPassiveDesc,
                'tooltip_tone' => 'default',
                'accent_hex' => null,
                'is_mythic' => false,
                'is_unstable' => false,
            ];
        }

        if ($powerEffectLabel !== '') {
            $abilities[] = [
                'kind' => 'power',
                'category' => 'powers',
                'icon_id' => $powerId,
                'label' => $powerEffectLabel,
                'meta' => $powerTier === 'mythic' ? 'Mythique' : 'Pouvoir',
                'tooltip_eyebrow' => 'POUVOIR · '.mb_strtoupper($powerTierLabel),
                'tooltip_title' => $powerName.' — '.$powerEffectLabel,
                'tooltip_desc' => $powerDescription,
                'tooltip_tone' => 'power',
                'accent_hex' => $powerPigmentHex,
                'is_mythic' => $powerTier === 'mythic',
                'is_unstable' => false,
            ];
        }

        foreach ($synergies as $syn) {
            $unstable = ! empty($syn['is_unstable']);
            $abilities[] = [
                'kind' => 'synergy',
                'category' => null,
                'icon_id' => null,
                'label' => (string) ($syn['name'] ?? ''),
                'meta' => $unstable ? 'Instable' : 'Synergie',
                'tooltip_eyebrow' => $unstable ? 'SYNERGIE · INSTABLE' : 'SYNERGIE · ACCORD',
                'tooltip_title' => (string) ($syn['name'] ?? ''),
                'tooltip_desc' => (string) ($syn['description'] ?? ''),
                'tooltip_tone' => $unstable ? 'unstable' : 'synergy',
                'accent_hex' => $unstable ? '#8A1A14' : '#74520A',
                'is_mythic' => false,
                'is_unstable' => $unstable,
            ];
        }

        return $abilities;
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
