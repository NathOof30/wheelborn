<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identité
            $table->string('name');
            $table->string('race_id', 50);
            $table->string('weapon_id', 50);
            $table->string('power_id', 50);
            $table->enum('power_tier', ['common', 'rare', 'mythic']);

            // Stats
            $table->unsignedSmallInteger('hp');
            $table->unsignedSmallInteger('max_hp');
            $table->unsignedSmallInteger('strength');
            $table->unsignedSmallInteger('speed');
            $table->unsignedSmallInteger('defense');
            $table->unsignedSmallInteger('power_score');

            // Rareté & flags
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary']);
            $table->boolean('is_lucky_spin')->default(false);
            $table->boolean('is_unstable')->default(false);
            $table->json('synergies_data')->nullable();
            $table->unsignedSmallInteger('recycle_value');

            // Combat
            $table->unsignedSmallInteger('battles_count')->default(0);
            $table->unsignedSmallInteger('wins')->default(0);
            $table->unsignedSmallInteger('losses')->default(0);

            // Statut
            $table->boolean('is_active_fighter')->default(false);
            $table->boolean('pending_reveal')->default(true);
            $table->timestamp('recycled_at')->nullable();

            $table->timestamps();

            // Index
            $table->index(['user_id', 'recycled_at', 'is_active_fighter']);
            $table->index(['user_id', 'rarity']);
            $table->index('power_score');
        });
    }
};
