<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/** Le joueur vient de recycler une ou plusieurs cartes (recyclage unitaire ou en masse). */
class CharactersRecycled
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public int $count,
    ) {
    }
}
