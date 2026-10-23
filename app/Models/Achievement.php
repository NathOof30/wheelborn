<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Achievement extends Model
{
    public $timestamps = false;

    // Catalogue des succès : titre honorifique et bourse de pièces versée une seule fois au déblocage
    public const DEFINITIONS = [
        'first_blood' => [
            'name'        => 'Premier Sang',
            'icon'        => '🩸',
            'description' => 'Remporter ta première victoire en arène.',
            'title'       => 'Gladiateur',
            'reward'      => 100,
        ],
        'master_alchemist' => [
            'name'        => 'Maître Alchimiste',
            'icon'        => '⚗️',
            'description' => 'Recycler 10 cartes.',
            'title'       => 'Alchimiste',
            'reward'      => 150,
        ],
        'divine_blessing' => [
            'name'        => 'Bénédiction Divine',
            'icon'        => '🌟',
            'description' => 'Obtenir une carte Légendaire.',
            'title'       => 'Élu des Dieux',
            'reward'      => 250,
        ],
        'unstable_combination' => [
            'name'        => 'Combinaison Instable',
            'icon'        => '💥',
            'description' => 'Tirer une synergie à risque.',
            'title'       => 'Apprenti Sorcier',
            'reward'      => 150,
        ],
    ];

    // Nombre de cartes à recycler pour « Maître Alchimiste »
    public const ALCHEMIST_RECYCLE_GOAL = 10;

    protected $fillable = [
        'user_id',
        'achievement_key',
        'unlocked_at',
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function definition(): ?array
    {
        return self::DEFINITIONS[$this->achievement_key] ?? null;
    }

    /**
     * Débloque un succès et verse sa bourse de pièces, une seule fois par joueur.
     * Le joueur est verrouillé pendant la transaction : deux déclenchements simultanés ne paient pas deux fois.
     * Retourne false si le succès était déjà débloqué.
     */
    public static function unlock(User $user, string $key): bool
    {
        $definition = self::DEFINITIONS[$key] ?? null;
        if (!$definition) {
            return false;
        }

        $unlocked = DB::transaction(function () use ($user, $key, $definition) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (self::where('user_id', $lockedUser->id)->where('achievement_key', $key)->exists()) {
                return false;
            }

            self::create(['user_id' => $lockedUser->id, 'achievement_key' => $key, 'unlocked_at' => now()]);
            $lockedUser->increment('coins', $definition['reward']);

            return true;
        });

        if ($unlocked) {
            // Le modèle de l'appelant reflète la bourse (HUD, mises à jour suivantes dans la même requête)
            $user->coins += $definition['reward'];
            $user->syncOriginalAttribute('coins');
        }

        return $unlocked;
    }
}
