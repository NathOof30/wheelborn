<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\WeaponData;
use App\Models\Character;
use App\Models\User;
use App\Services\CharacterGeneratorService;
use App\Services\EnergyManagerService;
use App\Services\RecycleService;
use App\Services\WorkshopService;
use Illuminate\Support\Facades\DB;

class CharacterWheel extends Component
{
    // Roues de tirage : durée de rotation (ms) de chaque roue, décalées pour un arrêt l'une après l'autre
    private const WHEEL_DURATIONS = ['race' => 3800, 'weapon' => 4600, 'power' => 5400];

    // Tours complets ajoutés avant l'arrêt sur la part gagnante
    private const WHEEL_EXTRA_TURNS = 5;

    // Champ du personnage lu par chaque roue
    private const WHEEL_FIELDS = ['race' => 'race_id', 'weapon' => 'weapon_id', 'power' => 'power_id'];

    public int $spinCharges = 3;
    public int $maxCharges = 3;
    public int $pityCounter = 0;
    public int $pityMax = 10;
    public int $coins = 250;
    public int $elo = 1000;
    public int $nextRegenSeconds = 0;
    public bool $isAdmin = false;

    // Ticket d'Urgence (affichage uniquement, l'achat est vérifié par WorkshopService)
    public ?int $emergencyCost = null;
    public int $emergencyRemaining = 0;

    // Vrai du lancement jusqu'à l'arrêt de la dernière roue : bloque un nouveau tirage et cache le résultat
    #[Locked]
    public bool $isSpinning = false;

    // Angle cumulé de chaque roue (degrés). Il ne fait que croître pour que la transition CSS tourne toujours dans le même sens
    #[Locked]
    public array $wheelRotations = ['race' => 0, 'weapon' => 0, 'power' => 0];

    // Angle avant le tirage courant pour initialiser l'animation CSS sans saut
    #[Locked]
    public array $previousRotations = ['race' => 0, 'weapon' => 0, 'power' => 0];

    public ?array $revealedCharacter = null;
    public ?string $notification = null;
    public ?string $notificationType = null;

    public function mount(): void
    {
        $this->refreshState();
        $this->checkPendingReveal();
    }

    /**
     * T6.1 : Reprise automatique d'un tirage non finalisé (pending_reveal = true).
     * Si l'utilisateur a quitté la page avant de choisir "Garder" ou "Recycler",
     * le tirage est restauré immédiatement à l'écran.
     */
    public function checkPendingReveal(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Si déjà affiché, vérifier qu'il est toujours valide
        if ($this->revealedCharacter) {
            $stillValid = Character::where('id', $this->revealedCharacter['id'])
                ->where('user_id', $user->id)
                ->where('pending_reveal', true)
                ->whereNull('recycled_at')
                ->exists();

            if (!$stillValid) {
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

        // S'il existe plusieurs tirages orphelins (ex: sessions antérieures au patch),
        // on conserve le plus récent à afficher, et on valide automatiquement les anciens
        // dans la collection pour éviter de bloquer le joueur dans une boucle de tirages en attente.
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

        // Les roues affichent directement le tirage récupéré, sans animation
        $this->aimWheels($this->revealedCharacter, 0);
    }

    public function refreshState(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $this->isAdmin = $user->isAdmin();

        if ($this->isAdmin) {
            // Pour l'admin : argent et charges illimités
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

        $this->spinCharges = $user->spin_charges;
        $this->maxCharges = $this->isAdmin ? 999 : 3;
        $this->pityCounter = $user->pity_counter;
        $this->coins = $user->coins;
        $this->elo = $user->elo;
        $this->nextRegenSeconds = app(EnergyManagerService::class)->getNextSpinRegenSeconds($user);

        $emergency = app(WorkshopService::class)->emergencySpinStatus($user);
        $this->emergencyCost = $emergency['next_cost'];
        $this->emergencyRemaining = $emergency['remaining'];
    }

    /** T4.3 : Ticket d'Urgence, achat d'une charge de spin quand la réserve est vide. */
    public function buyEmergencySpin(): void
    {
        $user = auth()->user();
        if (!$user || $this->isAdmin || $this->isSpinning) {
            return;
        }

        try {
            $purchase = app(WorkshopService::class)->buyEmergencySpin($user);
            $this->notify("🎟️ Ticket d'Urgence utilisé : +1 charge de tirage (-{$purchase['cost']} 🪙) !", 'success');
        } catch (\DomainException $e) {
            $this->notify($e->getMessage(), 'error');
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    /**
     * T6.2 : Tirage atomique avec lock pessimiste de l'utilisateur (lockForUpdate)
     * et protection contre les tirages simultanés ou non résolus (T6.1).
     */
    public function spin(): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        if ($this->isSpinning) {
            return;
        }

        // Si un tirage est déjà ouvert dans le composant, bloquer immédiatement
        if ($this->revealedCharacter) {
            $this->notify('Veuillez d\'abord finaliser votre tirage en cours.', 'warning');
            return;
        }

        $this->isSpinning = true;
        $launched = false;

        try {
            DB::transaction(function () use ($userId, &$launched) {
                // 1. Verrouillage pessimiste de l'utilisateur pour bloquer toute concurrence
                $user = User::where('id', $userId)->lockForUpdate()->firstOrFail();

                // 2. Sécurité T6.1 : Vérifier s'il existe déjà un tirage en attente en base
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

                // 3. Consommation sécurisée de l'énergie de tirage
                $energy = app(EnergyManagerService::class);
                if (!$user->isAdmin()) {
                    if (!$energy->consumeSpin($user)) {
                        $this->notify('Aucune charge de tirage disponible ! Attends la recharge.', 'error');
                        return;
                    }
                }

                // 4. Génération du combattant
                $isLucky = $user->pity_counter >= $this->pityMax;
                $character = app(CharacterGeneratorService::class)->generate($user, $isLucky);

                // 5. Gestion atomique de la jauge de pitié
                if ($isLucky) {
                    $user->update(['pity_counter' => 0]);
                    $this->notify('🌟 SUPER SPIN DÉCLENCHÉ ! Tirage exceptionnel garanti !', 'success');
                } else {
                    $user->increment('pity_counter');
                }

                $this->revealedCharacter = $character->fresh()->toArray();
                $launched = true;
            });
        } catch (\Throwable $e) {
            report($e);
            $this->notify('Une erreur est survenue lors du tirage. Vos charges ont été préservées.', 'error');
            $launched = false;
        }

        if ($launched) {
            // Le résultat est déjà enregistré (pending_reveal) : les roues ne font que le mettre en scène.
            // isSpinning reste vrai jusqu'à finishSpin(), appelé par la vue à l'arrêt de la dernière roue.
            $this->previousRotations = $this->wheelRotations;
            $this->aimWheels($this->revealedCharacter, self::WHEEL_EXTRA_TURNS);
        } else {
            $this->isSpinning = false;
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    /** Appelé par la vue quand la dernière roue s'est arrêtée : débloque l'interface et révèle le combattant. */
    public function finishSpin(): void
    {
        $this->isSpinning = false;
    }

    /** Les 3 roues affichées : parts proportionnelles aux chances de tirage, couleurs, angle courant et résultat. */
    #[Computed]
    public function wheels(): array
    {
        $palettes = [
            'race'   => ['label' => 'Race',    'colors' => ['#312e81', '#1e1b4b']],
            'weapon' => ['label' => 'Arme',    'colors' => ['#78350f', '#451a03']],
            'power'  => ['label' => 'Pouvoir', 'colors' => ['#581c87', '#3b0764']],
        ];

        $wheels = [];
        foreach ($palettes as $wheel => $palette) {
            $resultId = $this->revealedCharacter[self::WHEEL_FIELDS[$wheel]] ?? null;

            $stops = [];
            $segments = [];
            foreach ($this->wheelLayout($wheel) as $i => $part) {
                $color = $palette['colors'][$i % count($palette['colors'])];
                $stops[] = sprintf('%s %sdeg %sdeg', $color, round($part['start'], 2), round($part['end'], 2));
                $segments[] = [
                    'id'     => $part['id'],
                    'name'   => $part['name'],
                    'icon'   => $part['icon'],
                    'angle'  => round($part['center'], 2), // Centre de la part, depuis le haut, sens horaire
                    'chance' => $part['chance'],
                    'small'  => $part['end'] - $part['start'] < 20, // Part étroite : icône réduite pour ne pas déborder
                ];
            }

            $wheels[$wheel] = [
                'label'    => $palette['label'],
                'segments' => $segments,
                'gradient' => 'conic-gradient(' . implode(', ', $stops) . ')',
                'rotation' => $this->wheelRotations[$wheel],
                'duration' => self::WHEEL_DURATIONS[$wheel],
                'result'   => collect($segments)->firstWhere('id', $resultId),
            ];
        }

        return $wheels;
    }

    private function wheelItems(string $wheel): array
    {
        return array_values(match ($wheel) {
            'race'   => RaceData::all(),
            'weapon' => WeaponData::all(),
            // Chances du joueur, Aimant à Pouvoirs Rares compris : la taille des parts suit les vraies probabilités
            'power'  => auth()->check()
                ? app(CharacterGeneratorService::class)->powerPool(auth()->user())
                : PowerData::all(),
        });
    }

    /**
     * Découpage d'une roue : chaque part occupe une portion de 360° proportionnelle à son poids de tirage (`weight`),
     * la taille d'une part correspond donc à sa vraie chance de sortir.
     */
    private function wheelLayout(string $wheel): array
    {
        $items = $this->wheelItems($wheel);
        $totalWeight = array_sum(array_column($items, 'weight'));

        $layout = [];
        $start = 0.0;
        foreach ($items as $item) {
            $width = $item['weight'] / $totalWeight * 360;
            $layout[] = [
                'id'     => $item['id'],
                'name'   => $item['name'],
                'icon'   => $item['icon'],
                'start'  => $start,
                'end'    => $start + $width,
                'center' => $start + $width / 2,
                'chance' => round($item['weight'] / $totalWeight * 100, 1),
            ];
            $start += $width;
        }

        return $layout;
    }

    /** Oriente les 3 roues vers la race, l'arme et le pouvoir du personnage tiré. */
    private function aimWheels(array $character, int $extraTurns): void
    {
        foreach (self::WHEEL_FIELDS as $wheel => $field) {
            $this->wheelRotations[$wheel] = $this->targetRotation($wheel, $character[$field] ?? '', $extraTurns);
        }
    }

    /**
     * Angle final d'une roue pour que la part gagnante s'arrête sous le pointeur (en haut).
     * Pour amener le centre de la part sous le pointeur, la roue doit tourner de 360 - ce centre (modulo 360).
     * On repart de l'angle courant, on ajoute des tours complets et un léger décalage aléatoire
     * (±35 % de la largeur de la part) pour que l'arrêt ne tombe pas toujours pile au centre.
     */
    private function targetRotation(string $wheel, string $itemId, int $extraTurns): float
    {
        $part = collect($this->wheelLayout($wheel))->firstWhere('id', $itemId);
        $current = (float) $this->wheelRotations[$wheel];

        if (!$part) {
            return $current;
        }

        $width = $part['end'] - $part['start'];
        $jitter = $extraTurns > 0 ? mt_rand(-35, 35) / 100 * $width : 0;
        $landing = fmod(360 - $part['center'] + $jitter + 360, 360);
        $forward = fmod($landing - fmod($current, 360) + 360, 360);

        return round($current + $extraTurns * 360 + $forward, 2);
    }

    /**
     * T6.1 / T6.2 : Validation atomique de conservation du personnage dans la collection.
     */
    public function keepCharacter(): void
    {
        $userId = auth()->id();
        if (!$userId || !$this->revealedCharacter || $this->isSpinning) {
            return;
        }

        $charId = $this->revealedCharacter['id'];
        $charName = $this->revealedCharacter['name'];

        DB::transaction(function () use ($userId, $charId) {
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
    }

    /**
     * T6.1 / T6.2 : Recyclage immédiat du personnage tiré avec transaction et lock.
     */
    public function recycleCharacter(): void
    {
        $user = auth()->user();
        if (!$user || !$this->revealedCharacter || $this->isSpinning) {
            return;
        }

        $charId = $this->revealedCharacter['id'];

        try {
            $coins = app(RecycleService::class)->recycle($user, $charId);
            $this->notify("+{$coins} 🪙 récupérées par recyclage !", 'success');
        } catch (\Throwable $e) {
            $this->notify("Impossible de recycler ce combattant : {$e->getMessage()}", 'error');
        }

        $this->revealedCharacter = null;
        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    // --- OUTILS ADMIN POUR LES TESTS ---

    public function adminAddCoins(int $amount = 50000): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->increment('coins', $amount);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify("+{$amount} 🪙 ajoutées pour les tests !", 'success');
    }

    public function adminTriggerSuperSpin(): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->update(['pity_counter' => 10]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('✨ Jauge de chance maximisée à 10/10 ! Le prochain spin est un Super Spin !', 'success');
    }

    public function adminResetPity(): void
    {
        if (!$this->isAdmin) return;

        auth()->user()->update(['pity_counter' => 0]);
        $this->refreshState();
        $this->dispatchStatsUpdated();
        $this->notify('Jauge de chance réinitialisée à 0.', 'info');
    }

    /**
     * Outil Admin / Test pour injecter directement un tirage de test Paper Doll
     * (Humain + Épée ou Humain + Bâton).
     */
    public function adminForceTestSpin(string $weaponId = 'sword'): void
    {
        if (!$this->isAdmin) return;

        $user = auth()->user();
        if ($this->revealedCharacter) {
            $this->notify('Veuillez d\'abord finaliser votre tirage en cours.', 'warning');
            return;
        }

        $weaponData = \App\Data\WeaponData::find($weaponId);
        $weaponName = $weaponData['name'] ?? ucfirst($weaponId);

        $character = Character::create([
            'user_id'           => $user->id,
            'name'              => \App\Data\NameGenerator::generate('human'),
            'race_id'           => 'human',
            'weapon_id'         => $weaponId,
            'power_id'          => 'fire',
            'power_tier'        => \App\Enums\PowerTier::Common,
            'hp'                => 105,
            'max_hp'            => 105,
            'strength'          => 55,
            'speed'             => 48,
            'defense'           => 42,
            'power_score'       => 250,
            'rarity'            => \App\Enums\Rarity::Rare,
            'is_lucky_spin'     => false,
            'is_unstable'       => false,
            'synergies_data'    => \App\Data\SynergyData::detect('human', $weaponId, 'fire'),
            'recycle_value'     => 150,
            'is_active_fighter' => false,
            'pending_reveal'    => true,
        ]);

        $this->revealedCharacter = $character->toArray();
        $this->notify("🎯 Tirage de test Paper Doll généré : Humain + {$weaponName} !", 'success');
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
        return view('livewire.character-wheel', [
            // Roue qui s'arrête en dernier : c'est elle qui prévient le serveur de la fin du tirage
            'lastWheel' => array_search(max(self::WHEEL_DURATIONS), self::WHEEL_DURATIONS, true),
            'maxDuration' => max(self::WHEEL_DURATIONS),
            'previousRotations' => $this->previousRotations,
        ]);
    }
}
