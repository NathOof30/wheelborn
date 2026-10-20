<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->renameColumn('combat_log', 'turns_log');
        });

        Schema::table('battles', function (Blueprint $table) {
            // Stats des deux combattants au moment du duel, nécessaires pour recalculer le combat via la seed
            $table->json('fighters_snapshot')->nullable()->after('turns_log');
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->dropColumn('fighters_snapshot');
        });

        Schema::table('battles', function (Blueprint $table) {
            $table->renameColumn('turns_log', 'combat_log');
        });
    }
};
