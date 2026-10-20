<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            // Un match nul (double K.O.) n'a pas de vainqueur
            $table->uuid('winner_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->uuid('winner_id')->nullable(false)->change();
        });
    }
};
