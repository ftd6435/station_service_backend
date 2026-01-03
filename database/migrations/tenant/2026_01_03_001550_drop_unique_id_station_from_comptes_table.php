<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {

            // 1️⃣ supprimer la clé étrangère
            $table->dropForeign(['id_station']);

            // 2️⃣ supprimer l’unique
            $table->dropUnique('comptes_id_station_unique');

            // 3️⃣ recréer la clé étrangère simple (NON unique)
            $table->foreign('id_station')
                  ->references('id')
                  ->on('stations')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {

            // rollback propre
            $table->dropForeign(['id_station']);
            $table->unique('id_station');

            $table->foreign('id_station')
                  ->references('id')
                  ->on('stations')
                  ->cascadeOnDelete();
        });
    }
};
