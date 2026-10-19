<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Character;
use App\Models\User;
use App\Services\CharacterGeneratorService;
use App\Services\EnergyManagerService;
use App\Services\RecycleService;
use Illuminate\Support\Facades\DB;

class CharacterWheel extends Component
{
    public int $spinCharges = 3;
    public int $maxCharges = 3;
    public int $pityCounter = 0;
    public int $pityMax = 10;
    public int $coins = 250;
    public int $elo = 1000;
    public int $nextRegenSeconds = 0;
    public bool $isAdmin = false;
    public bool $isSpinning = false;
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

        try {
            DB::transaction(function () use ($userId) {
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
            });
        } catch (\Throwable $e) {
            report($e);
            $this->notify('Une erreur est survenue lors du tirage. Vos charges ont été préservées.', 'error');
        } finally {
            $this->isSpinning = false;
        }

        $this->refreshState();
        $this->dispatchStatsUpdated();
    }

    /**
     * T6.1 / T6.2 : Validation atomique de conservation du personnage dans la collection.
     */
    public function keepCharacter(): void
    {
        $userId = auth()->id();
        if (!$userId || !$this->revealedCharacter) {
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
        if (!$user || !$this->revealedCharacter) {
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
        return view('livewire.character-wheel');
    }
}
