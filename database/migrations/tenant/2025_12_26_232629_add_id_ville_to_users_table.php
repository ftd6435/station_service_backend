<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /**
             * 🔹 Ajout de la colonne UNIQUEMENT si absente
             */
            if (! Schema::hasColumn('users', 'id_ville')) {

                $table->foreignId('id_ville')
                    ->nullable()
                    ->after('id_station')
                    ->constrained('villes')
                    ->nullOnDelete();

            } else {
                /**
                 * 🔹 La colonne existe → on ajoute seulement la FK si besoin
                 */
                $table->foreign('id_ville')
                    ->references('id')
                    ->on('villes')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Supprimer la FK si elle existe
            $table->dropForeign(['id_ville']);

            // ⚠️ On ne supprime PAS la colonne en rollback
            // car elle peut être utilisée ailleurs
        });
    }
};
