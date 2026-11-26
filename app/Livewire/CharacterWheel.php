<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\SynergyData;
use App\Data\WeaponData;
use App\Enums\Rarity;
use App\Models\Character;
use App\Models\User;
use App\Services\CharacterGeneratorService;
use App\Services\EnergyManagerService;
use App\Services\PaperDollService;
use App\Services\RecycleService;
use App\Services\WorkshopService;
use App\Support\CodexIconRegistry;
use App\View\Presenters\FolioCardPresenter;
use DomainException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class CharacterWheel extends Component
{
    private const WHEEL_DURATIONS = ['race' => 1100, 'weapon' => 2500, 'power' => 4500];

    private const WHEEL_EXTRA_TURNS = 5;

    private const WHEEL_FIELDS = ['race' => 'race_id', 'weapon' => 'weapon_id', 'power' => 'power_id'];

    #[Locked]
    public int $spinCharges = 3;

    #[Locked]
    public int $maxCharges = 3;

    #[Locked]
    public int $pityCounter = 0;

    #[Locked]
    public int $pityMax = 10;

    #[Locked]
    public int $coins = 250;

    #[Locked]
    public int $elo = 1000;

    #[Locked]
    public int $nextRegenSeconds = 0;

    #[Locked]
    public bool $isAdmin = false;

    #[Locked]
    public bool $showAdminForge = false;

    public string $adminSelectedRace = 'human';

    public string $adminSelectedWeapon = 'sword';

    public string $adminSelectedPower = 'fire';

    public string $adminSelectedRarity = 'rare';

    #[Locked]
    public ?int $emergencyCost = null;

    #[Locked]
    public int $emergencyRemaining = 0;

    #[Locked]
    public bool $isSpinning = false;

    #[Locked]
    public array $wheelRotations = ['race' => 0, 'weapon' => 0, 'power' => 0];

    #[Locked]
    public array $previousRotations = ['race' => 0, 'weapon' => 0, 'power' => 0];

    #[Locked]
    public ?array $revealedCharacter = null;

    public ?string $notification = null;

    public ?string $notificationType = null;

    public function mount(): void
    {
        $this->refreshState();
        $this->checkPendingReveal();
    }

    public function checkPendingReveal(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        if ($this->revealedCharacter) {
            $stillValid = Character::where('id', $this->revealedCharacter['id'])
                ->where('user_id', $user->id)
                ->where('pending_reveal', true)
                ->whereNull('recycled_at')
                ->exists();

            if (! $stillValid) {
                $this->revealedCharacter = null;
            }

            return;
        }

        $pendings = Character::where('user_id', $user->id)
            ->where('pending_reveal', true)
            ->whereNull('recycled_at')
            ->orderByDesc('created_at')
            ->get();

        if ($pendings->isEmpty()) {
            return;
        }

        if ($pendings->count() > 1) {
            $latest = $pendings->first();
            Character::where('user_id', $user->id)
                ->where('pending_reveal', true)
                ->where('id', '!=', $latest->id)
                ->whereNull('recycled_at')
                ->update(['pending_reveal' => false]);

            $this->revealedCharacter = $latest->toArray();
            $this->notify('Tirage en attente récupéré ! (Vos tirages orphelins antérieurs ont rejoint votre collection).', 'info');
        } else {
            $this->revealedCharacter = $pendings->first()->toArray();
            $this->notify('Tirage en attente récupéré ! Veuillez choisir de le garder ou de le recycler.', 'info');
        }

        $this->aimWheels($this->revealedCharacter, 0);
    }

    public function refreshState(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->isAdmin = $user->isAdmin();

        if ($this->isAdmin) {
            if ($user->coins < 999999 || $user->spin_charges < 999) {
                $user->update([
                    'coins' => max(999999, $user->coins),
                    'spin_charges' => 999,
                    'arena_tickets' => 999,
                ]);
            }
        } else {
            app(EnergyManagerService::class)->regenSpins($user);
        }

        $user->refresh();

        $this->spinCharges = (int) $user->spin_charges;
        $this->maxCharges = $this->isAdmin ? 999 : 3;
        $this->pityCounter = (int) $user->pity_counter;
        $this->coins = (int) $user->coins;
        $this->elo = (int) $user->elo;
        $this->nextRegenSeconds = app(EnergyManagerService::class)->getNextSpinRegenSeconds($user);

        $emergency = app(WorkshopService::class)->emergencySpinStatus($user);
        $this->emergencyCost = $emergency['next_cost'];
        $this->emergencyRemaining = $emergency['remaining'];
    }

    public function buyEmergencySpin(): void
    {
        $user = auth()->user();
        if (! $user || $this->isAdmin || $this->isSpinning) {
            return;
        }

        try {
            $purchase = app(WorkshopService::class)->buyEmergencySpin($user);
            $this->notify("🎟️ Ticket d'Urgence utilisé : +1 charge de tirage (-{$purchase['cost']} 🪙) !", 'success');
        } catch (DomainException $e) {
            $this->notify($e->getMessage(), 'error');
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    public function spin(): void
    {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        if ($this->isSpinning) {
            return;
        }

        if ($this->revealedCharacter) {
            $this->notify("Veuillez d'abord finaliser votre tirage en cours.", 'warning');

            return;
        }

        $this->isSpinning = true;
        $launched = false;

        try {
            DB::transaction(function () use ($userId, &$launched): void {
                $user = User::where('id', $userId)->lockForUpdate()->firstOrFail();

                $pendings = Character::where('user_id', $user->id)
                    ->where('pending_reveal', true)
                    ->whereNull('recycled_at')
                    ->orderByDesc('created_at')
                    ->get();

                if ($pendings->isNotEmpty()) {
                    $latest = $pendings->first();
                    if ($pendings->count() > 1) {
                        Character::where('user_id', $user->id)
                            ->where('pending_reveal', true)
                            ->where('id', '!=', $latest->id)
                            ->whereNull('recycled_at')
                            ->update(['pending_reveal' => false]);
                    }
                    $this->revealedCharacter = $latest->toArray();
                    $this->aimWheels($this->revealedCharacter, 0);
                    $this->notify('Vous avez un tirage non finalisé en attente ! Choisissez de le garder ou de le recycler.', 'warning');

                    return;
                }

                $energy = app(EnergyManagerService::class);
                if (! $user->isAdmin() && ! $energy->consumeSpin($user)) {
                    $this->notify('Aucune charge de tirage disponible ! Attends la recharge.', 'error');

                    return;
                }

                $isLucky = $user->pity_counter >= $this->pityMax;
                $character = app(CharacterGeneratorService::class)->generate($user, $isLucky);

                if ($isLucky) {
                    $user->update(['pity_counter' => 0]);
                    $this->notify('🌟 SUPER SPIN DÉCLENCHÉ ! Tirage exceptionnel garanti !', 'success');
                } else {
                    $user->increment('pity_counter');
                }

                $this->revealedCharacter = $character->fresh()->toArray();
                $launched = true;
            });
        } catch (Throwable $e) {
            report($e);
            $this->notify('Une erreur est survenue lors du tirage. Vos charges ont été préservées.', 'error');
            $launched = false;
        }

        if ($launched) {
            $this->previousRotations = $this->wheelRotations;
            $this->aimWheels($this->revealedCharacter, self::WHEEL_EXTRA_TURNS);
            $this->dispatch('codex-volvelle-spin',
                wheels: $this->wheels,
                rotations: $this->wheelRotations,
                reveal: $this->volvelleRevealPayload($this->revealedCharacter),
            );
        } else {
            $this->isSpinning = false;
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    public function finishSpin(): void
    {
        $this->isSpinning = false;
    }

    #[Computed]
    public function wheels(): array
    {
        $palettes = [
            'race' => ['label' => 'Race', 'colors' => ['#E9DBBE', '#D9C8A2']],
            'weapon' => ['label' => 'Arme', 'colors' => ['#F3E8D0', '#E0D0AE']],
            'power' => ['label' => 'Pouvoir', 'colors' => ['#8A1A14', '#2A3A7A']],
        ];

        $wheels = [];
        foreach ($palettes as $wheel => $palette) {
            $resultId = $this->revealedCharacter[self::WHEEL_FIELDS[$wheel]] ?? null;

            $stops = [];
            $segments = [];
            foreach ($this->wheelLayout($wheel) as $i => $part) {
                $color = $wheel === 'power'
                    ? FolioCardPresenter::powerPigment((string) $part['id'])['pigment_hex']
                    : $palette['colors'][$i % count($palette['colors'])];
                $stops[] = sprintf('%s %sdeg %sdeg', $color, round($part['start'], 2), round($part['end'], 2));
                $segments[] = [
                    'id' => $part['id'],
                    'name' => $part['name'],
                    'icon' => $part['icon'],
                    'start' => round($part['start'], 2),
                    'end' => round($part['end'], 2),
                    'angle' => round($part['center'], 2),
                    'chance' => $part['chance'],
                    'small' => $part['end'] - $part['start'] < 20,
                ];
            }

            $wheels[$wheel] = [
                'label' => $palette['label'],
                'segments' => $segments,
                'gradient' => 'conic-gradient('.implode(', ', $stops).')',
                'rotation' => $this->wheelRotations[$wheel],
                'duration' => self::WHEEL_DURATIONS[$wheel],
                'result' => collect($segments)->firstWhere('id', $resultId),
            ];
        }

        return $wheels;
    }

    #[Computed]
    public function volvelleIcons(): array
    {
        return app(CodexIconRegistry::class)->allByCategory();
    }

    #[Computed]
    public function volvellePigments(): array
    {
        return FolioCardPresenter::POWER_PIGMENTS;
    }

    private function wheelItems(string $wheel): array
    {
        return array_values(match ($wheel) {
            'race' => RaceData::all(),
            'weapon' => WeaponData::all(),
            'power' => auth()->check()
                ? app(CharacterGeneratorService::class)->powerPool(auth()->user())
                : PowerData::all(),
        });
    }

    private function wheelLayout(string $wheel): array
    {
        $items = $this->wheelItems($wheel);
        $totalWeight = array_sum(array_column($items, 'weight'));

        $layout = [];
        $start = 0.0;
        foreach ($items as $item) {
            $width = $item['weight'] / $totalWeight * 360;
            $layout[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'icon' => $item['icon'],
                'start' => $start,
                'end' => $start + $width,
                'center' => $start + $width / 2,
                'chance' => round($item['weight'] / $totalWeight * 100, 1),
            ];
            $start += $width;
        }

        return $layout;
    }

    private function aimWheels(array $character, int $extraTurns): void
    {
        foreach (self::WHEEL_FIELDS as $wheel => $field) {
            $this->wheelRotations[$wheel] = $this->targetRotation($wheel, (string) ($character[$field] ?? ''), $extraTurns);
        }
    }

    private function targetRotation(string $wheel, string $itemId, int $extraTurns): float
    {
        $part = collect($this->wheelLayout($wheel))->firstWhere('id', $itemId);
        $current = (float) $this->wheelRotations[$wheel];

        if (! $part) {
            return $current;
        }

        $width = $part['end'] - $part['start'];
        $jitter = $extraTurns > 0 ? mt_rand(-35, 35) / 100 * $width : 0;
        $landing = fmod(360 - $part['center'] + $jitter + 360, 360);
        $forward = fmod($landing - fmod($current, 360) + 360, 360);

        return round($current + $extraTurns * 360 + $forward, 2);
    }

    /**
     * @param  array<string, mixed>  $character
     * @return array<string, mixed>
     */
    private function volvelleRevealPayload(array $character): array
    {
        $raceId = (string) ($character['race_id'] ?? 'human');
        $weaponId = (string) ($character['weapon_id'] ?? 'sword');
        $powerId = (string) ($character['power_id'] ?? 'fire');

        $race = RaceData::find($raceId);
        $weapon = WeaponData::find($weaponId);
        $power = PowerData::find($powerId);

        return [
            'race' => ['id' => $raceId, 'name' => (string) ($race['name'] ?? ucfirst($raceId))],
            'weapon' => ['id' => $weaponId, 'name' => (string) ($weapon['name'] ?? ucfirst($weaponId))],
            'power' => [
                'id' => $powerId,
                'name' => (string) ($power['name'] ?? ucfirst($powerId)),
                'pigment_hex' => FolioCardPresenter::powerPigment($powerId)['pigment_hex'],
            ],
            'power_tier' => (string) ($power['tier'] ?? 'common'),
        ];
    }

    public function keepCharacter(): void
    {
        $userId = auth()->id();
        if (! $userId || ! $this->revealedCharacter || $this->isSpinning) {
            return;
        }

        $charId = (string) $this->revealedCharacter['id'];
        $charName = (string) $this->revealedCharacter['name'];

        DB::transaction(function () use ($userId, $charId): void {
            $character = Character::where('id', $charId)
                ->where('user_id', $userId)
                ->whereNull('recycled_at')
                ->lockForUpdate()
                ->first();

            if ($character) {
                $character->update(['pending_reveal' => false]);
            }
        });

        $this->revealedCharacter = null;
        $this->notify("{$charName} a rejoint votre collection !", 'success');
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->dispatch('codex-volvelle-reset');
    }

    public function recycleCharacter(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->revealedCharacter || $this->isSpinning) {
            return;
        }

        $charId = (string) $this->revealedCharacter['id'];

        try {
            $coins = app(RecycleService::class)->recycle($user, $charId);
            $this->notify("+{$coins} 🪙 récupérées par recyclage !", 'success');
        } catch (Throwable $e) {
            $this->notify("Impossible de recycler ce combattant : {$e->getMessage()}", 'error');
        }

        $this->revealedCharacter = null;
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->dispatch('codex-volvelle-reset');
    }

    private function isCurrentUserAdmin(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function adminAddCoins(int $amount = 50000): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }

        auth()->user()->increment('coins', $amount);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify("+{$amount} 🪙 ajoutées pour les tests !", 'success');
    }

    public function adminTriggerSuperSpin(): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }

        auth()->user()->update(['pity_counter' => 10]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('✨ Jauge de chance maximisée à 10/10 ! Le prochain spin est un Super Spin !', 'success');
    }

    public function adminResetPity(): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }

        auth()->user()->update(['pity_counter' => 0]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('Jauge de chance réinitialisée à 0.', 'info');
    }

    public function toggleAdminForge(): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }
        $this->showAdminForge = ! $this->showAdminForge;
    }

    public function adminSpawnCustom(bool $withAnimation = false): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }

        $user = auth()->user();
        if ($this->isSpinning || $this->revealedCharacter) {
            $this->notify("Veuillez d'abord finaliser votre tirage en cours.", 'warning');

            return;
        }

        $rarityEnum = Rarity::tryFrom($this->adminSelectedRarity) ?? Rarity::Rare;

        $character = app(CharacterGeneratorService::class)->generateCustom(
            $user,
            $this->adminSelectedRace,
            $this->adminSelectedWeapon,
            $this->adminSelectedPower,
            $rarityEnum
        );

        $this->revealedCharacter = $character->toArray();
        $this->showAdminForge = false;

        if ($withAnimation) {
            $this->isSpinning = true;
            $this->previousRotations = $this->wheelRotations;
            $this->aimWheels($this->revealedCharacter, self::WHEEL_EXTRA_TURNS);
            $this->dispatch('codex-volvelle-spin',
                wheels: $this->wheels,
                rotations: $this->wheelRotations,
                reveal: $this->volvelleRevealPayload($this->revealedCharacter),
            );
            $this->notify("🎡 Tirage personnalisé lancé sur la Roue : {$character->name} !", 'info');
        } else {
            $this->aimWheels($this->revealedCharacter, 0);
            $this->dispatch('codex-volvelle-sync-steps',
                steps: $this->volvelleRevealPayload($this->revealedCharacter),
                rotations: $this->wheelRotations,
            );
            $this->notify("🎯 Combattant personnalisé forgé : {$character->name} !", 'success');
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    public function adminForceTestSpin(string $weaponId = 'sword', string $powerId = 'fire'): void
    {
        if (! $this->isCurrentUserAdmin()) {
            return;
        }

        $this->adminSelectedRace = 'human';
        $this->adminSelectedWeapon = $weaponId;
        $this->adminSelectedPower = $powerId;
        $this->adminSelectedRarity = 'rare';
        $this->adminSpawnCustom(false);
    }

    #[Computed]
    public function adminAllRaces(): array
    {
        return RaceData::all();
    }

    #[Computed]
    public function adminAllWeapons(): array
    {
        return WeaponData::all();
    }

    #[Computed]
    public function adminAllPowers(): array
    {
        return PowerData::all();
    }

    #[Computed]
    public function adminDetectedSynergies(): array
    {
        return SynergyData::detect(
            $this->adminSelectedRace,
            $this->adminSelectedWeapon,
            $this->adminSelectedPower
        );
    }

    #[Computed]
    public function adminAssetStatus(): array
    {
        return [
            'race' => PaperDollService::getRaceAsset($this->adminSelectedRace),
            'weapon' => PaperDollService::getWeaponAsset($this->adminSelectedWeapon),
            'aura' => PaperDollService::getAuraAsset($this->adminSelectedWeapon, $this->adminSelectedPower),
        ];
    }

    #[Computed]
    public function adminPreviewCard(): array
    {
        $race = RaceData::find($this->adminSelectedRace);
        $weapon = WeaponData::find($this->adminSelectedWeapon);
        $synergies = $this->adminDetectedSynergies;

        $hp = max(30, min(140, 70 + (int) ($race['mods']['hp'] ?? 0) + (int) ($weapon['mods']['hp'] ?? 0)));
        $str = max(15, min(100, 60 + (int) ($race['mods']['strength'] ?? 0) + (int) ($weapon['mods']['strength'] ?? 0)));
        $spd = max(10, min(100, 55 + (int) ($race['mods']['speed'] ?? 0) + (int) ($weapon['mods']['speed'] ?? 0)));
        $def = max(10, min(100, 55 + (int) ($race['mods']['defense'] ?? 0) + (int) ($weapon['mods']['defense'] ?? 0)));

        return app(FolioCardPresenter::class)->present([
            'id' => 'admin-preview-'.$this->adminSelectedRace.'-'.$this->adminSelectedWeapon.'-'.$this->adminSelectedPower.'-'.$this->adminSelectedRarity,
            'name' => 'Aperçu · '.($race['name'] ?? ucfirst($this->adminSelectedRace)),
            'race_id' => $this->adminSelectedRace,
            'weapon_id' => $this->adminSelectedWeapon,
            'power_id' => $this->adminSelectedPower,
            'rarity' => $this->adminSelectedRarity,
            'hp' => $hp,
            'max_hp' => $hp,
            'strength' => $str,
            'speed' => $spd,
            'defense' => $def,
            'power_score' => min(100, max(20, (int) round(($hp * 0.24) + ($str * 0.32) + ($def * 0.24) + ($spd * 0.20) + (count($synergies) * 3)))),
            'synergies_data' => $synergies,
        ]);
    }

    public function dispatchStatsUpdated(): void
    {
        $this->dispatch('stats-updated',
            coins: $this->coins,
            spinCharges: $this->spinCharges,
            elo: $this->elo
        );
    }

    public function notify(string $message, string $type = 'info'): void
    {
        $this->notification = $message;
        $this->notificationType = $type;
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
        $this->notificationType = null;
    }

    public function render()
    {
        $initialSteps = ($this->revealedCharacter && ! $this->isSpinning)
            ? $this->volvelleRevealPayload($this->revealedCharacter)
            : ['race' => null, 'weapon' => null, 'power' => null];

        return view('livewire.character-wheel', [
            'lastWheel' => array_search(max(self::WHEEL_DURATIONS), self::WHEEL_DURATIONS, true),
            'maxDuration' => max(self::WHEEL_DURATIONS),
            'previousRotations' => $this->previousRotations,
            'initialSteps' => $initialSteps,
        ]);
    }
}
