<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/** Un duel d'arène vient d'être enregistré (Élo, pièces et palmarès déjà à jour). */
class ArenaBattleFinished
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public bool $won,
        public bool $draw,
    ) {
    }
}
