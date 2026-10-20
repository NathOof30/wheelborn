<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Character;
use App\Enums\Rarity;
use App\Enums\PowerTier;
use App\Data\SynergyData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =========================================================================
        // 1. COMPTE ADMINISTRATEUR (Plein Pouvoirs & Démo Soutenance)
        // =========================================================================
        $admin = User::updateOrCreate(
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

        // =========================================================================
        // 2. COMPTE JOUEUR NORMAL (Expérience Utilisateur Standard / Newbie)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'player@wheelborn.test'],
            [
                'name'          => 'Joueur Standard',
                'password'      => Hash::make('password'),
                'coins'         => 250,
                'elo'           => 1000,
                'spin_charges'  => 3,
                'arena_tickets' => 5,
                'pity_counter'  => 0,
                'level'         => 1,
                'is_admin'      => false,
            ]
        );

        // =========================================================================
        // 3. CARTES PRÉ-GÉNÉRÉES POUR L'ADMIN (Démonstration immédiate)
        // =========================================================================
        $seedCharacters = [
            // 1. Humain + Épée (Paper Doll Layer Arme 1)
            [
                'name'           => 'Valérian Brise-Fer',
                'race_id'        => 'human',
                'weapon_id'      => 'sword',
                'power_id'       => 'fire',
                'power_tier'     => PowerTier::Rare,
                'hp'             => 110,
                'max_hp'         => 110,
                'strength'       => 50,
                'speed'          => 55,
                'defense'        => 40,
                'power_score'    => 72,
                'rarity'         => Rarity::Rare,
                'is_locked'      => false,
                'wins'           => 4,
                'losses'         => 1,
                'recycle_value'  => 140,
            ],
            // 2. Humain + Bâton (Paper Doll Layer Arme 2 + Carte Verrouillée)
            [
                'name'           => 'Syrus l\'Archiviste',
                'race_id'        => 'human',
                'weapon_id'      => 'staff',
                'power_id'       => 'lightning',
                'power_tier'     => PowerTier::Mythic,
                'hp'             => 95,
                'max_hp'         => 95,
                'strength'       => 45,
                'speed'          => 70,
                'defense'        => 35,
                'power_score'    => 86,
                'rarity'         => Rarity::Epic,
                'is_locked'      => true, // 🔒 Verrouillé par défaut pour tester le cadenas
                'wins'           => 7,
                'losses'         => 0,
                'recycle_value'  => 185,
            ],
            // 3. Démon + Marteau + Feu (Synergie Démoniaque & Carte Légendaire)
            [
                'name'           => 'Malakor le Dévoreur',
                'race_id'        => 'demon',
                'weapon_id'      => 'hammer',
                'power_id'       => 'fire',
                'power_tier'     => PowerTier::Mythic,
                'hp'             => 145,
                'max_hp'         => 145,
                'strength'       => 90,
                'speed'          => 42,
                'defense'        => 65,
                'power_score'    => 98,
                'rarity'         => Rarity::Legendary,
                'is_locked'      => false,
                'wins'           => 14,
                'losses'         => 2,
                'recycle_value'  => 320,
            ],
            // 4. Elfe + Arc + Poison (Synergie Naturelle & Carte Épique)
            [
                'name'           => 'Sylvana Vente-Noire',
                'race_id'        => 'elf',
                'weapon_id'      => 'bow',
                'power_id'       => 'poison',
                'power_tier'     => PowerTier::Common,
                'hp'             => 90,
                'max_hp'         => 90,
                'strength'       => 52,
                'speed'          => 80,
                'defense'        => 30,
                'power_score'    => 78,
                'rarity'         => Rarity::Epic,
                'is_locked'      => false,
                'wins'           => 6,
                'losses'         => 3,
                'recycle_value'  => 160,
            ],
            // 5. Golem + Marteau + Foudre (Carte Commune de départ)
            [
                'name'           => 'Thorgal Poing-de-Roc',
                'race_id'        => 'golem',
                'weapon_id'      => 'hammer',
                'power_id'       => 'lightning',
                'power_tier'     => PowerTier::Rare,
                'hp'             => 120,
                'max_hp'         => 120,
                'strength'       => 46,
                'speed'          => 32,
                'defense'        => 52,
                'power_score'    => 56,
                'rarity'         => Rarity::Common,
                'is_locked'      => false,
                'wins'           => 2,
                'losses'         => 1,
                'recycle_value'  => 70,
            ],
        ];

        foreach ($seedCharacters as $charData) {
            $synergies = SynergyData::detect($charData['race_id'], $charData['weapon_id'], $charData['power_id']);
            $isUnstable = collect($synergies)->contains('is_unstable', true);

            Character::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'name'    => $charData['name'],
                ],
                array_merge($charData, [
                    'synergies_data'    => $synergies,
                    'is_unstable'       => $isUnstable,
                    'is_lucky_spin'     => $charData['rarity'] === Rarity::Legendary,
                    'is_active_fighter' => false,
                    'pending_reveal'    => false,
                    'recycled_at'       => null,
                    'battles_count'     => $charData['wins'] + $charData['losses'],
                ])
            );
        }

        // =========================================================================
        // 4. COMPTES DÉVELOPPEURS LOCAUX EXISTANTS (Si présents)
        // =========================================================================
        User::whereIn('email', ['nathanael.leang@gmail.com', 'tnm59163@gmail.com'])
            ->update([
                'coins'        => 999999,
                'spin_charges' => 999,
                'arena_tickets'=> 999,
                'is_admin'     => true,
            ]);
    }
}
