<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /**
             * ⚠️ id_ville AJOUTÉ MANUELLEMENT
             * → on ne crée JAMAIS la colonne ici
             * → FK uniquement si absente
             */
            if (Schema::hasColumn('users', 'id_ville')) {
                $table->foreign('id_ville')
                      ->references('id')
                      ->on('villes')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_ville']);
            // ❌ on ne supprime PAS la colonne
        });
    }
};
