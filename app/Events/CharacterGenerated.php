<?php

namespace App\Events;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/** Un combattant vient d'être tiré à la roue par le joueur (les bots d'arène ne déclenchent pas cet événement). */
class CharacterGenerated
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public Character $character,
    ) {
    }
}
