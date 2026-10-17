<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStat extends Model
{
    public const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'total_generations',
        'total_battles',
        'total_wins',
        'total_losses',
        'total_recycled',
        'legendaries_found',
        'highest_power',
        'coins_earned_total',
        'discovered_synergies',
        'discovered_powers',
    ];

    protected $casts = [
        'total_generations'    => 'integer',
        'total_battles'        => 'integer',
        'total_wins'           => 'integer',
        'total_losses'         => 'integer',
        'total_recycled'       => 'integer',
        'legendaries_found'    => 'integer',
        'highest_power'        => 'integer',
        'coins_earned_total'   => 'integer',
        'discovered_synergies' => 'array',
        'discovered_powers'    => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
