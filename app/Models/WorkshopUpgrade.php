<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopUpgrade extends Model
{
    public const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'rare_magnet_level',
        'hero_forge_level',
        'arena_bonus_level',
        'emergency_purchases_today',
        'last_emergency_date',
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
}
