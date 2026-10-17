<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Compte Administrateur dédié
        User::updateOrCreate(
            ['email' => 'admin@wheelborn.test'],
            [
                'name'          => 'Administrateur',
                'password'      => Hash::make('password'),
                'coins'         => 999999,
                'elo'           => 1600,
                'spin_charges'  => 999,
                'arena_tickets' => 999,
                'pity_counter'  => 0,
                'level'         => 10,
                'is_admin'      => true,
            ]
        );

        // 2. Promotion des comptes existants (Kevin & Nathanaël) en admin pour les tests immédiats
        User::whereIn('email', ['nathanael.leang@gmail.com', 'tnm59163@gmail.com'])
            ->update([
                'coins'        => 999999,
                'spin_charges' => 999,
                'arena_tickets'=> 999,
                'is_admin'     => true,
            ]);
    }
}
