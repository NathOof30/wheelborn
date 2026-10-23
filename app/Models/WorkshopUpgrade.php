<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopUpgrade extends Model
{
    public const CREATED_AT = null;

    // Améliorations permanentes de l'Atelier (clés de GameConfig::workshop()) => colonne du niveau atteint
    public const LEVEL_COLUMNS = [
        'rare_magnet' => 'rare_magnet_level',
        'hero_forge'  => 'hero_forge_level',
        'arena_bonus' => 'arena_bonus_level',
    ];

    protected $fillable = [
        'user_id',
        'rare_magnet_level',
        'hero_forge_level',
        'arena_bonus_level',
        'emergency_purchases_today',
        'last_emergency_date',
    ];

    // Mêmes valeurs par défaut que la table, pour qu'un atelier tout juste créé soit utilisable sans refresh()
    protected $attributes = [
        'rare_magnet_level'         => 0,
        'hero_forge_level'          => 0,
        'arena_bonus_level'         => 0,
        'emergency_purchases_today' => 0,
    ];

    protected $casts = [
        'rare_magnet_level'         => 'integer',
        'hero_forge_level'          => 'integer',
        'arena_bonus_level'         => 'integer',
        'emergency_purchases_today' => 'integer',
        'last_emergency_date'       => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Niveau atteint pour une amélioration (0 si jamais achetée ou clé inconnue). */
    public function levelOf(string $upgrade): int
    {
        $column = self::LEVEL_COLUMNS[$upgrade] ?? null;

        return $column ? (int) $this->{$column} : 0;
    }
}
