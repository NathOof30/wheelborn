<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Économie
            $table->unsignedInteger('coins')->default(250);
            $table->unsignedInteger('elo')->default(1000);
            $table->unsignedTinyInteger('pity_counter')->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedTinyInteger('level')->default(1);

            // Énergies
            $table->unsignedTinyInteger('spin_charges')->default(3);
            $table->timestamp('last_spin_regen_at')->nullable();
            $table->unsignedTinyInteger('arena_tickets')->default(5);
            $table->timestamp('last_arena_regen_at')->nullable();

            // Index pour le leaderboard
            $table->index('elo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'coins',
                'elo',
                'pity_counter',
                'xp',
                'level',
                'spin_charges',
                'last_spin_regen_at',
                'arena_tickets',
                'last_arena_regen_at',
            ]);
        });
    }

};


