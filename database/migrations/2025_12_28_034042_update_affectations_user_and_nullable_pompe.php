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
        Schema::table('affectations', function (Blueprint $table) {

            $table->unsignedBigInteger('id_user')
                  ->nullable()
                  ->after('id_pompiste');

            $table->unsignedBigInteger('id_station_tmp')
                  ->nullable()
                  ->after('id_station');

            $table->unsignedBigInteger('id_pompe_tmp')
                  ->nullable()
                  ->after('id_pompe');
        });

        /**
         * =================================================
         * 2. COPIE DES DONNÉES EXISTANTES
         * =================================================
         */
        DB::statement('UPDATE affectations SET id_user = id_pompiste');
        DB::statement('UPDATE affectations SET id_station_tmp = id_station');
        DB::statement('UPDATE affectations SET id_pompe_tmp = id_pompe');

        /**
         * =================================================
         * 3. SUPPRESSION DES CLÉS ÉTRANGÈRES EXISTANTES
         * =================================================
         */
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropForeign(['id_pompiste']);
            $table->dropForeign(['id_station']);
            $table->dropForeign(['id_pompe']);
        });

        /**
         * =================================================
         * 4. SUPPRESSION DES ANCIENNES COLONNES
         * =================================================
         */
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn(['id_pompiste', 'id_station', 'id_pompe']);
        });

        /**
         * =================================================
         * 5. RENOMMAGE DES COLONNES TEMPORAIRES
         * =================================================
         */
        Schema::table('affectations', function (Blueprint $table) {
            $table->renameColumn('id_station_tmp', 'id_station');
            $table->renameColumn('id_pompe_tmp', 'id_pompe');
        });

        /**
         * =================================================
         * 6. CRÉATION DES CLÉS ÉTRANGÈRES (NULLABLE)
         * =================================================
         */
        Schema::table('affectations', function (Blueprint $table) {

            $table->foreign('id_user')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('id_station')
                  ->references('id')
                  ->on('stations')
                  ->nullOnDelete();

            $table->foreign('id_pompe')
                  ->references('id')
                  ->on('pompes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        /**
         * =================================================
         * ROLLBACK SÉCURISÉ
         * =================================================
         */
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_station']);
            $table->dropForeign(['id_pompe']);
        });

        Schema::table('affectations', function (Blueprint $table) {

            $table->unsignedBigInteger('id_pompiste')->nullable();
            DB::statement('UPDATE affectations SET id_pompiste = id_user');

            $table->dropColumn('id_user');

            $table->foreign('id_pompiste')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
