<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Résolution préventive des doublons existants (pour éviter toute collision)
        $duplicates = DB::table('users')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicates as $duplicateName) {
            $usersWithSameName = DB::table('users')
                ->where('name', $duplicateName)
                ->orderBy('id')
                ->get();

            $first = true;
            $counter = 2;
            foreach ($usersWithSameName as $user) {
                if ($first) {
                    $first = false;
                    continue;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'name' => $duplicateName . '_' . $counter++,
                    ]);
            }
        }

        // 2. Ajout de la contrainte d'unicité au niveau de la base de données
        Schema::table('users', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
