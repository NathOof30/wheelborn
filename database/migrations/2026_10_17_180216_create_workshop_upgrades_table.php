<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workshop_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rare_magnet_level')->default(0);
            $table->unsignedTinyInteger('hero_forge_level')->default(0);
            $table->unsignedTinyInteger('arena_bonus_level')->default(0);

            $table->unsignedTinyInteger('emergency_purchases_today')->default(0);
            $table->date('last_emergency_date')->nullable();

            $table->timestamp('updated_at')->useCurrent();
        });
    }
};
