<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('users')->cascadeOnDelete();

            $table->uuid('challenger_id');
            $table->uuid('opponent_id')->nullable();
            $table->string('opponent_name')->nullable();
            $table->unsignedInteger('opponent_elo')->nullable();
            $table->uuid('winner_id');

            $table->bigInteger('seed');
            $table->unsignedTinyInteger('turns_count');

            $table->unsignedInteger('player_old_elo');
            $table->unsignedInteger('player_new_elo');
            $table->smallInteger('elo_delta');

            $table->unsignedSmallInteger('coins_awarded')->default(0);
            $table->unsignedSmallInteger('xp_awarded')->default(0);

            $table->json('combat_log')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['player_id', 'created_at']);
        });
    }
};
