<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {

            /**
             * -------------------------------------------------
             * 1. SUPPRESSION DES CONTRAINTES EXISTANTES
             * -------------------------------------------------
             */
            $table->dropForeign(['id_pompiste']);
            $table->dropForeign(['id_pompe']);

            /**
             * -------------------------------------------------
             * 2. RENOMMAGE DE LA COLONNE
             * -------------------------------------------------
             */
            $table->renameColumn('id_pompiste', 'id_user');

            /**
             * -------------------------------------------------
             * 3. RENDRE id_pompe NULLABLE
             * -------------------------------------------------
             */
            $table->foreignId('id_pompe')->nullable()->change();
        });

        Schema::table('affectations', function (Blueprint $table) {

            /**
             * -------------------------------------------------
             * 4. RECRÉATION DES CLÉS ÉTRANGÈRES
             * -------------------------------------------------
             */
            $table->foreign('id_user')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('id_pompe')
                  ->references('id')
                  ->on('pompes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {

            // rollback clés étrangères
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_pompe']);

            // rollback renommage
            $table->renameColumn('id_user', 'id_pompiste');

            // rollback nullable
            $table->foreignId('id_pompe')->nullable(false)->change();
        });

        Schema::table('affectations', function (Blueprint $table) {

            $table->foreign('id_pompiste')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('id_pompe')
                  ->references('id')
                  ->on('pompes')
                  ->cascadeOnDelete();
        });
    }
};
