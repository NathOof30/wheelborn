<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteAdmin extends Command
{
    protected $signature = 'user:admin {email? : L\'adresse email de l\'utilisateur à promouvoir}';
    protected $description = 'Passe un utilisateur en administrateur avec tirages et pièces illimités';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Adresse email du compte à promouvoir :');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$email}");
            return self::FAILURE;
        }

        $user->update([
            'is_admin'     => true,
            'coins'        => 999999,
            'spin_charges' => 999,
            'arena_tickets'=> 999,
        ]);

        $this->info("✅ Succès ! L'utilisateur {$user->name} ({$user->email}) est maintenant Administrateur.");
        $this->line("• Tirages : Illimités (999)");
        $this->line("• Pièces  : 999 999 🪙");

        return self::SUCCESS;
    }
}
