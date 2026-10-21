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
}
