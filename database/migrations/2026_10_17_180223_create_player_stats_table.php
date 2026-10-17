<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->unsignedInteger('total_generations')->default(0);
            $table->unsignedInteger('total_battles')->default(0);
            $table->unsignedInteger('total_wins')->default(0);
            $table->unsignedInteger('total_losses')->default(0);
            $table->unsignedInteger('total_recycled')->default(0);
            $table->unsignedInteger('legendaries_found')->default(0);
            $table->unsignedSmallInteger('highest_power')->default(0);
            $table->unsignedInteger('coins_earned_total')->default(0);

            $table->json('discovered_synergies')->nullable();
            $table->json('discovered_powers')->nullable();

            $table->timestamp('updated_at')->useCurrent();
        });
    }
};
