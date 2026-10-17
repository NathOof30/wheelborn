<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArenaSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'card_1_id',
        'card_2_id',
        'card_3_id',
        'started_at',
        'ends_at',
        'claimed_at',
        'results',
        'total_wins',
        'total_losses',
        'elo_delta',
        'coins_earned',
        'xp_earned',
        'status',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'ends_at'      => 'datetime',
        'claimed_at'   => 'datetime',
        'results'      => 'array',
        'total_wins'   => 'integer',
        'total_losses' => 'integer',
        'elo_delta'    => 'integer',
        'coins_earned' => 'integer',
        'xp_earned'    => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card1(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'card_1_id');
    }

    public function card2(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'card_2_id');
    }

    public function card3(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'card_3_id');
    }
}
