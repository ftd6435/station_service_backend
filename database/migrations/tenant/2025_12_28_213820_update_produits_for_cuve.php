<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * =================================================
         * 1. AJOUT DES NOUVELLES COLONNES (NULLABLE)
         * =================================================
         */
        Schema::table('produits', function (Blueprint $table) {

            // 🔹 Station propriétaire de la cuve
            $table->foreignId('id_station')
                  ->nullable()
                  ->after('id')
                  ->constrained('stations')
                  ->nullOnDelete();

            // 🔹 Nouveau nom métier
            $table->string('type_cuve')
                  ->nullable()
                  ->after('type_produit');

            // 🔹 Référence métier cuve
            $table->string('reference', 50)
                  ->nullable()
                  ->after('libelle');
        });

        /**
         * =================================================
         * 2. COPIE DES DONNÉES EXISTANTES
         * =================================================
         */
        DB::statement('UPDATE produits SET type_cuve = type_produit');

        /**
         * =================================================
         * 3. SUPPRESSION DE L’ANCIENNE COLONNE
         * =================================================
         */
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('type_produit');
        });

        /**
         * =================================================
         * 4. (OPTIONNEL) INDEX UNIQUE MÉTIER
         * =================================================
         * Une référence est unique par station
         */
        // Schema::table('produits', function (Blueprint $table) {
        //     $table->unique(['id_station', 'reference']);
        // });
    }

    public function down(): void
    {
        /**
         * =================================================
         * ROLLBACK SÉCURISÉ
         * =================================================
         */

        /**
         * 1. SUPPRESSION DE LA FK STATION
         */
        Schema::table('produits', function (Blueprint $table) {
            $table->dropForeign(['id_station']);
        });

        /**
         * 2. RÉCRÉATION DE type_produit
         */
        Schema::table('produits', function (Blueprint $table) {

            $table->string('type_produit')
                  ->nullable()
                  ->after('libelle');
        });

        /**
         * 3. RESTAURATION DES DONNÉES
         */
        DB::statement('UPDATE produits SET type_produit = type_cuve');

        /**
         * 4. SUPPRESSION DES NOUVELLES COLONNES
         */
        Schema::table('produits', function (Blueprint $table) {

            $table->dropColumn('type_cuve');
            $table->dropColumn('reference');
            $table->dropColumn('id_station');
        });
    }
};
