<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\League;
use App\Data\GameConfig;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'coins',
        'elo',
        'pity_counter',
        'xp',
        'level',
        'spin_charges',
        'last_spin_regen_at',
        'arena_tickets',
        'last_arena_regen_at',
        'has_free_arena_reroll',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'last_spin_regen_at'      => 'datetime',
            'last_arena_regen_at'     => 'datetime',
            'has_free_arena_reroll'   => 'boolean',
            'is_admin'                => 'boolean',
            'coins'                   => 'integer',
            'elo'                     => 'integer',
            'pity_counter'            => 'integer',
            'xp'                      => 'integer',
            'level'                   => 'integer',
            'spin_charges'            => 'integer',
            'arena_tickets'           => 'integer',
        ];
    }

    // --- RELATIONS ---

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class)
                    ->whereNull('recycled_at'); // Exclure les recyclés par défaut
    }

    public function favoriteCharacters(): HasMany
    {
        return $this->hasMany(Character::class)
                    ->whereNull('recycled_at')
                    ->where('is_favorite', true)
                    ->take(3);
    }

    public function allCharacters(): HasMany
    {
        return $this->hasMany(Character::class); // Inclut les recyclés
    }

    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class, 'player_id');
    }

    public function arenaSessions(): HasMany
    {
        return $this->hasMany(ArenaSession::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function workshopUpgrades(): HasOne
    {
        return $this->hasOne(WorkshopUpgrade::class);
    }

    public function playerStats(): HasOne
    {
        return $this->hasOne(PlayerStat::class);
    }

    /**
     * Statistiques globales d'arène calculées fidèlement depuis l'historique officiel des duels.
     *
     * @return array{total:int, wins:int, losses:int, draws:int, winrate:int}
     */
    public function battleStats(): array
    {
        $battles = Battle::where('player_id', $this->id)->get(['id', 'challenger_id', 'winner_id']);
        $total = $battles->count();
        $wins = $battles->filter(fn (Battle $b) => $b->isPlayerVictory())->count();
        $draws = $battles->filter(fn (Battle $b) => $b->isDraw())->count();
        $losses = max(0, $total - $wins - $draws);
        $winrate = $total > 0 ? (int) round(($wins / $total) * 100) : 0;

        return [
            'total'   => $total,
            'wins'    => $wins,
            'losses'  => $losses,
            'draws'   => $draws,
            'winrate' => $winrate,
        ];
    }

    // --- HELPERS ---

    public function getLeague(): League
    {
        return League::fromElo($this->elo ?? 1000);
    }

    public function activeFighter(): ?Character
    {
        return $this->characters()
                    ->where('is_active_fighter', true)
                    ->first();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    // --- PROGRESSION & NIVEAUX ---

    /**
     * XP total cumulé nécessaire pour atteindre un niveau donné.
     * Formule : Seuil = 25 * (N - 1) * (N + 2).
     */
    public static function totalXpRequiredForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        return 25 * ($level - 1) * ($level + 2);
    }

    /**
     * Calcule le niveau correspondant à un montant d'XP total.
     */
    public static function calculateLevelForXp(int $totalXp): int
    {
        if ($totalXp <= 0) {
            return 1;
        }

        $level = 1;
        while (self::totalXpRequiredForLevel($level + 1) <= $totalXp) {
            $level++;
        }

        return $level;
    }

    /**
     * Titre honorifique débloqué selon le niveau atteint.
     */
    public function getLevelTitle(?int $level = null): string
    {
        $level ??= (int) ($this->level ?: 1);
        $titles = GameConfig::leveling()['titles'];
        krsort($titles);

        foreach ($titles as $threshold => $title) {
            if ($level >= $threshold) {
                return $title;
            }
        }

        return 'Néophyte des Étoiles';
    }

    /**
     * Détail complet de la progression du joueur pour les barres d'XP et le profil.
     *
     * @return array{level: int, total_xp: int, current_level_xp: int, step_xp_required: int, next_level_total: int, percentage: float, title: string}
     */
    public function currentLevelProgress(): array
    {
        $totalXp = max(0, (int) $this->xp);
        $currentLevel = self::calculateLevelForXp($totalXp);
        $currentLevelFloorXp = self::totalXpRequiredForLevel($currentLevel);
        $nextLevelCeilXp = self::totalXpRequiredForLevel($currentLevel + 1);
        $stepXp = $nextLevelCeilXp - $currentLevelFloorXp;
        $progressInCurrentLevel = max(0, $totalXp - $currentLevelFloorXp);
        $percentage = $stepXp > 0 ? round(min(100, ($progressInCurrentLevel / $stepXp) * 100), 1) : 100.0;

        return [
            'level'            => $currentLevel,
            'total_xp'         => $totalXp,
            'current_level_xp' => $progressInCurrentLevel,
            'step_xp_required' => $stepXp,
            'next_level_total' => $nextLevelCeilXp,
            'percentage'       => $percentage,
            'title'            => $this->getLevelTitle($currentLevel),
        ];
    }

    /**
     * Crédite de l'XP au joueur, ajuste automatiquement son niveau et persiste les changements.
     *
     * @return array{old_level: int, new_level: int, leveled_up: bool, xp_gained: int, total_xp: int}
     */
    public function addXp(int $amount): array
    {
        $oldLevel = max(1, (int) $this->level);
        $newTotalXp = max(0, (int) $this->xp + $amount);
        $newLevel = self::calculateLevelForXp($newTotalXp);
        $leveledUp = $newLevel > $oldLevel;

        $this->update([
            'xp'    => $newTotalXp,
            'level' => $newLevel,
        ]);

        return [
            'old_level'  => $oldLevel,
            'new_level'  => $newLevel,
            'leveled_up' => $leveledUp,
            'xp_gained'  => $amount,
            'total_xp'   => $newTotalXp,
        ];
    }
}
