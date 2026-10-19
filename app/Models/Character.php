<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\Rarity;
use App\Enums\PowerTier;

class Character extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'race_id',
        'weapon_id',
        'power_id',
        'power_tier',
        'hp',
        'max_hp',
        'strength',
        'speed',
        'defense',
        'power_score',
        'rarity',
        'is_lucky_spin',
        'is_unstable',
        'synergies_data',
        'recycle_value',
        'battles_count',
        'wins',
        'losses',
        'is_active_fighter',
        'is_locked',
        'pending_reveal',
        'recycled_at',
    ];

    protected $casts = [
        'rarity'            => Rarity::class,
        'power_tier'        => PowerTier::class,
        'synergies_data'    => 'array',
        'is_lucky_spin'     => 'boolean',
        'is_unstable'       => 'boolean',
        'is_active_fighter' => 'boolean',
        'is_locked'         => 'boolean',
        'pending_reveal'    => 'boolean',
        'recycled_at'       => 'datetime',
        'hp'                => 'integer',
        'max_hp'            => 'integer',
        'strength'          => 'integer',
        'speed'             => 'integer',
        'defense'           => 'integer',
        'power_score'       => 'integer',
        'recycle_value'     => 'integer',
        'battles_count'     => 'integer',
        'wins'              => 'integer',
        'losses'            => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('recycled_at');
    }

    public function isRecycled(): bool
    {
        return $this->recycled_at !== null;
    }

    public function getRaceDataAttribute(): ?array
    {
        return \App\Data\RaceData::find($this->race_id);
    }

    public function getWeaponDataAttribute(): ?array
    {
        return \App\Data\WeaponData::find($this->weapon_id);
    }

    public function getPowerDataAttribute(): ?array
    {
        return \App\Data\PowerData::find($this->power_id);
    }

    public function getWinrateAttribute(): int
    {
        $total = $this->wins + $this->losses;
        return $total > 0 ? (int) round(($this->wins / $total) * 100) : 0;
    }
}
