<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('is_active_fighter');
            $table->index(['user_id', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_locked']);
            $table->dropColumn('is_locked');
        });
    }
};
