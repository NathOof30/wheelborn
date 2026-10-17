<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arena_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->uuid('card_1_id');
            $table->uuid('card_2_id');
            $table->uuid('card_3_id');

            $table->timestamp('started_at');
            $table->timestamp('ends_at');
            $table->timestamp('claimed_at')->nullable();

            $table->json('results');

            $table->unsignedTinyInteger('total_wins')->default(0);
            $table->unsignedTinyInteger('total_losses')->default(0);
            $table->smallInteger('elo_delta')->default(0);
            $table->unsignedSmallInteger('coins_earned')->default(0);
            $table->unsignedSmallInteger('xp_earned')->default(0);

            $table->enum('status', ['active', 'completed', 'claimed'])->default('active');

            $table->index(['user_id', 'status']);
        });
    }
};
