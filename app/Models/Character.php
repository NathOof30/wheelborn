<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\PowerData;
use App\Data\RaceData;
use App\Data\WeaponData;
use App\Enums\PowerTier;
use App\Enums\Rarity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'is_favorite',
        'pending_reveal',
        'recycled_at',
    ];

    protected $casts = [
        'rarity' => Rarity::class,
        'power_tier' => PowerTier::class,
        'synergies_data' => 'array',
        'is_lucky_spin' => 'boolean',
        'is_unstable' => 'boolean',
        'is_active_fighter' => 'boolean',
        'is_locked' => 'boolean',
        'is_favorite' => 'boolean',
        'pending_reveal' => 'boolean',
        'recycled_at' => 'datetime',
        'hp' => 'integer',
        'max_hp' => 'integer',
        'strength' => 'integer',
        'speed' => 'integer',
        'defense' => 'integer',
        'power_score' => 'integer',
        'recycle_value' => 'integer',
        'battles_count' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
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
        return RaceData::find($this->race_id);
    }

    public function getWeaponDataAttribute(): ?array
    {
        return WeaponData::find($this->weapon_id);
    }

    public function getPowerDataAttribute(): ?array
    {
        return PowerData::find($this->power_id);
    }

    public function getRaceLabelAttribute(): string
    {
        return (string) ($this->race_data['name'] ?? ucfirst((string) $this->race_id));
    }

    public function getWeaponLabelAttribute(): string
    {
        return (string) ($this->weapon_data['name'] ?? ucfirst((string) $this->weapon_id));
    }

    public function getPowerLabelAttribute(): string
    {
        return (string) ($this->power_data['name'] ?? ucfirst((string) $this->power_id));
    }

    public function getWinrateAttribute(): int
    {
        $total = $this->wins + $this->losses;

        return $total > 0 ? (int) round(($this->wins / $total) * 100) : 0;
    }
}
