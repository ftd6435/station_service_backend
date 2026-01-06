<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations_comptes', function (Blueprint $table) {

            // =============================================
            // 🔥 SUPPRIMER LA FK + COLONNE
            // =============================================
            if (Schema::hasColumn('operations_comptes', 'id_compte')) {
                $table->dropForeign(['id_compte']);
                $table->dropColumn('id_compte');
            }
        });

        Schema::table('operations_comptes', function (Blueprint $table) {

            // =============================================
            // 🔹 RECRÉER id_compte NULLABLE
            // =============================================
            $table->foreignId('id_compte')
                ->nullable()
                ->after('id')
                ->constrained('comptes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations_comptes', function (Blueprint $table) {

            // =============================================
            // 🔄 SUPPRIMER LA VERSION NULLABLE
            // =============================================
            $table->dropForeign(['id_compte']);
            $table->dropColumn('id_compte');
        });

        Schema::table('operations_comptes', function (Blueprint $table) {

            // =============================================
            // 🔄 RESTAURER id_compte NOT NULL
            // =============================================
            $table->foreignId('id_compte')
                ->after('id')
                ->constrained('comptes')
                ->cascadeOnDelete();
        });
    }
};
