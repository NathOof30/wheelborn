<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Battle extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'player_id',
        'challenger_id',
        'opponent_id',
        'opponent_name',
        'opponent_elo',
        'winner_id',
        'seed',
        'turns_count',
        'player_old_elo',
        'player_new_elo',
        'elo_delta',
        'coins_awarded',
        'xp_awarded',
        'turns_log',
        'fighters_snapshot',
    ];

    protected $casts = [
        'turns_log'         => 'array',
        'fighters_snapshot' => 'array',
        'seed'           => 'integer',
        'turns_count'    => 'integer',
        'player_old_elo' => 'integer',
        'player_new_elo' => 'integer',
        'elo_delta'      => 'integer',
        'coins_awarded'  => 'integer',
        'xp_awarded'     => 'integer',
        'opponent_elo'   => 'integer',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'challenger_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'opponent_id');
    }

    public function isPlayerVictory(): bool
    {
        return $this->winner_id === $this->challenger_id;
    }

    /** Match nul (double K.O.) : aucun vainqueur. */
    public function isDraw(): bool
    {
        return $this->winner_id === null;
    }
}
