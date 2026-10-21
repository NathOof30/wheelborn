<?php

namespace App\Livewire;

use App\Data\GameConfig;
use App\Models\Character;
use App\Services\ArenaService;
use App\Services\EnergyManagerService;
use Illuminate\Support\Facades\DB;
use App\Services\BattleHistoryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class BattleArena extends Component
{
    public ?string $fighterId = null;
    public ?string $error = null;

    // Verrouillés : le client ne doit pas pouvoir affaiblir l'adversaire ou falsifier le résultat
    #[Locked]
    public ?array $opponent = null;

    // Combattant du joueur dans le combat revu depuis l'historique (null hors replay)
    #[Locked]
    public ?array $replayFighter = null;

    #[Locked]
    public ?array $result = null;

    // Tickets d'arène (affichage uniquement, la vérification se fait côté serveur au lancement du duel)
    public int $arenaTickets = 0;
    public int $maxTickets = 0;
    public int $nextTicketSeconds = 0;
    public bool $isAdmin = false;
    // Modale de sélection de combattant
    public bool $showFighterModal = false;
    public string $search = '';
    public string $rarityFilter = '';
    public string $sortBy = 'power_desc';

    public function mount(): void
    {
        $this->refreshTickets();
        $this->fighterId = $this->fighters->first()?->id;

        if ($this->fighterId) {
            // Anti-triche F5 : restaurer l'adversaire en cours depuis la session s'il existe
            $savedOpponent = session('arena_current_opponent');
            if ($savedOpponent && !empty($savedOpponent['name'])) {
                $this->opponent = $savedOpponent;
            } else {
                $this->newOpponent();
            }
        }
    }

    #[Computed]
    public function fighters()
    {
        return app(ArenaService::class)->availableFighters(auth()->user());
    }

    #[Computed]
    public function modalFighters()
    {
        $collection = $this->fighters;

        if (!empty($this->search)) {
            $term = mb_strtolower(trim($this->search));
            $collection = $collection->filter(function ($char) use ($term) {
                return str_contains(mb_strtolower($char->name), $term)
                    || str_contains(mb_strtolower($char->race_id), $term)
                    || str_contains(mb_strtolower($char->weapon_id), $term)
                    || str_contains(mb_strtolower($char->power_id), $term);
            });
        }

        if (!empty($this->rarityFilter)) {
            $collection = $collection->filter(function ($char) {
                $val = $char->rarity instanceof \App\Enums\Rarity ? $char->rarity->value : (string) $char->rarity;
                return $val === $this->rarityFilter;
            });
        }

        return match ($this->sortBy) {
            'power_asc' => $collection->sortBy('power_score'),
            'wins_desc' => $collection->sortByDesc('wins'),
            'date_desc' => $collection->sortByDesc('created_at'),
            default     => $collection->sortByDesc('power_score'),
        };
    }

    public function openFighterModal(): void
    {
        $this->showFighterModal = true;
    }

    public function closeFighterModal(): void
    {
        $this->showFighterModal = false;
    }

    public function resetModalFilters(): void
    {
        $this->search = '';
        $this->rarityFilter = '';
        $this->sortBy = 'power_desc';
    }

    #[Computed]
    public function fighter(): ?Character
    {
        return $this->fighters->firstWhere('id', $this->fighterId);
    }

    #[Computed]
    public function cards(): array
    {
        $arena = app(ArenaService::class);

        return array_filter([
            'challenger' => match (true) {
                $this->replayFighter !== null => ['label' => 'Ton combattant (replay)'] + $arena->describeFighter($this->replayFighter),
                $this->fighter !== null       => ['label' => 'Ton combattant'] + $arena->describeFighter($this->fighter),
                default                       => null,
            },
            'opponent'   => $this->opponent ? ['label' => $this->opponentLabel()] + $arena->describeFighter($this->opponent) : null,
        ]);
    }

    /** Étiquette de la carte adverse : vrai joueur ou Fantôme d'Arène. */
    private function opponentLabel(): string
    {
        return $this->opponent['kind'] === 'player'
            ? "⚔️ Joueur : {$this->opponent['owner']}"
            : "🤖 Bot d'arène · Fantôme";
    }

    #[Computed]
    public function comparison(): ?array
    {
        $challenger = $this->cards['challenger'] ?? null;
        $opponent = $this->cards['opponent'] ?? null;

        if (!$challenger || !$opponent) {
            return null;
        }

        $stats = [
            'hp' => [
                'label' => 'Points de Vie',
                'short' => 'PV',
                'icon'  => '❤️',
                'c_val' => (int) $challenger['hp'],
                'o_val' => (int) $opponent['hp'],
            ],
            'strength' => [
                'label' => 'Force d\'Attaque',
                'short' => 'Force',
                'icon'  => '⚔️',
                'c_val' => (int) $challenger['strength'],
                'o_val' => (int) $opponent['strength'],
            ],
            'speed' => [
                'label' => 'Vitesse & Initiative',
                'short' => 'Vitesse',
                'icon'  => '⚡',
                'c_val' => (int) $challenger['speed'],
                'o_val' => (int) $opponent['speed'],
            ],
            'defense' => [
                'label' => 'Défense & Armure',
                'short' => 'Défense',
                'icon'  => '🛡️',
                'c_val' => (int) $challenger['defense'],
                'o_val' => (int) $opponent['defense'],
            ],
        ];

        foreach ($stats as $key => &$stat) {
            $sum = $stat['c_val'] + $stat['o_val'];
            $stat['ratio_c'] = $sum > 0 ? (int) round(($stat['c_val'] / $sum) * 100) : 50;
            $stat['ratio_o'] = 100 - $stat['ratio_c'];
            $stat['diff'] = $stat['c_val'] - $stat['o_val'];
        }
        unset($stat);

        $powerDiff = (int) $challenger['power_score'] - (int) $opponent['power_score'];
        $speedDiff = (int) $challenger['speed'] - (int) $opponent['speed'];

        $initiative = match (true) {
            $speedDiff > 0 => [
                'label' => 'Tu frappes en 1er',
                'badge' => 'text-emerald-400 bg-emerald-950/70 border-emerald-800/60',
                'icon'  => '⚡',
            ],
            $speedDiff < 0 => [
                'label' => 'L\'adversaire frappe en 1er',
                'badge' => 'text-rose-400 bg-rose-950/70 border-rose-800/60',
                'icon'  => '⚠️',
            ],
            default        => [
                'label' => 'Égalité (Tirage 50/50)',
                'badge' => 'text-amber-400 bg-amber-950/70 border-amber-800/60',
                'icon'  => '🪙',
            ],
        };

        return [
            'stats'       => $stats,
            'power_diff'  => $powerDiff,
            'initiative'  => $initiative,
            'challenger'  => $challenger,
            'opponent'    => $opponent,
        ];
    }

    /** Temps avant le prochain ticket, au format "mm:ss". */
    #[Computed]
    public function nextTicketIn(): string
    {
        return gmdate('i:s', $this->nextTicketSeconds);
    }

    /** Recharge les tickets (rattrapage hors-ligne compris) et met à jour le compteur affiché. */
    public function refreshTickets(): void
    {
        $user = auth()->user();
        $energy = app(EnergyManagerService::class);

        $energy->regenArenaTickets($user);

        $this->isAdmin = $user->isAdmin();
        $this->arenaTickets = $user->arena_tickets;
        $this->maxTickets = GameConfig::arenaEnergy()['max'];
        $this->nextTicketSeconds = $energy->getNextArenaRegenSeconds($user);
    }

    /** Coût en or du changement d'adversaire (1er gratuit après un combat, puis 10 🪙). */
    #[Computed]
    public function rerollCost(): int
    {
        $user = auth()->user();
        return ($user && $user->has_free_arena_reroll) ? 0 : (GameConfig::matchmaking()['reroll_cost'] ?? 10);
    }

    public function selectFighter(string $characterId): void
    {
        if (!$this->fighters->contains('id', $characterId)) {
            return;
        }

        $this->fighterId = $characterId;
        $this->showFighterModal = false;
        unset($this->fighter);

        // L'adversaire dépend de la puissance de la carte choisie : on en cherche un nouveau
        session()->forget('arena_current_opponent');
        $this->newOpponent();
    }

    public function newOpponent(): void
    {
        $currentId = $this->opponent['id'] ?? null;
        $this->opponent = app(ArenaService::class)->findOpponent(auth()->user(), $this->fighter, $currentId);
        session(['arena_current_opponent' => $this->opponent]);
        $this->resetBattle();
    }

    /** Changer d'adversaire avant le duel avec gestion du coût (1er gratuit après un combat, suivants payants). */
    public function rerollOpponent(): void
    {
        if ($this->result !== null || !$this->fighter) {
            return;
        }

        $user = auth()->user();
        $cost = $this->rerollCost;

        if ($cost > 0) {
            if ($user->coins < $cost) {
                $this->error = "Pas assez de pièces ({$cost} 🪙 requis) pour chercher un autre adversaire.";
                return;
            }

            $user->decrement('coins', $cost);
        } else {
            // Le reroll gratuit accordé après un combat est consommé
            $user->update(['has_free_arena_reroll' => false]);
        }

        // Met à jour la barre HUD en temps réel sans rechargement de page
        $this->dispatch('stats-updated', coins: $user->fresh()->coins, elo: $user->elo, spinCharges: $user->spin_charges);

        unset($this->rerollCost);
        $currentId = $this->opponent['id'] ?? null;
        $this->opponent = app(ArenaService::class)->findOpponent($user, $this->fighter, $currentId);
        session(['arena_current_opponent' => $this->opponent]);
        $this->resetBattle();
    }

    public function fight(): void
    {
        if (!$this->fighter || !$this->opponent) {
            return;
        }

        $user = auth()->user();

        // Ticket et combat dans la même transaction : si le combat échoue, le ticket est rendu
        $outcome = DB::transaction(fn () => app(EnergyManagerService::class)->consumeArenaTicket($user)
            ? app(ArenaService::class)->fight($user, $this->fighter, $this->opponent)
            : null);

        $this->refreshTickets();

        if ($outcome === null) {
            $this->error = "Plus de ticket d'arène ! Un nouveau ticket arrive dans {$this->nextTicketIn}.";
            return;
        }

        // Combat terminé : l'adversaire en session est effacé pour le prochain duel
        session()->forget('arena_current_opponent');
        unset($this->rerollCost);

        // Le journal part directement à l'animation Alpine, inutile de le garder dans l'état du composant
        $this->result = collect($outcome)->except('log')->all();
        unset($this->fighters);

        // Les nouvelles stats (Élo, pièces) partent avec l'animation : elle ne les affichera dans le HUD
        // qu'à la fin du combat, pour ne pas dévoiler le résultat avant
        $this->dispatch('battle-started',
            log: $outcome['log'],
            maxHp: $outcome['max_hp'],
            stats: ['coins' => $user->coins, 'spinCharges' => $user->spin_charges, 'elo' => $user->elo],
        );
    }

    public function rematch(): void
    {
        $this->resetBattle();
    }

    /**
     * Revoir un combat depuis l'historique : on rejoue son journal enregistré, sans ticket ni recalcul.
     * L'Élo et les pièces ne bougent pas, le HUD n'est donc pas mis à jour.
     */
    #[On('replay-battle')]
    public function replayBattle(int $battleId): void
    {
        $replay = app(BattleHistoryService::class)->replayData(auth()->user(), $battleId);
        if (!$replay) {
            return;
        }

        $this->replayFighter = $replay['challenger'];
        $this->opponent = $replay['opponent'];
        $this->result = $replay['result'];
        $this->error = null;

        $this->dispatch('battle-started', log: $replay['log'], maxHp: $replay['max_hp'], stats: null, replay: true);
    }

    private function resetBattle(): void
    {
        $this->result = null;
        $this->replayFighter = null;
        $this->error = null;
        $this->dispatch('battle-reset');
    }

    public function render()
    {
        return view('livewire.battle-arena', [
            'cards'      => $this->cards,
            'comparison' => $this->comparison,
        ]);
    }
}
