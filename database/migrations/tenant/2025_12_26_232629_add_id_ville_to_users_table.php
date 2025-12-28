<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔒 Vérification directe côté MySQL
        $fkExists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'id_ville'
              AND CONSTRAINT_NAME = 'users_id_ville_foreign'
        ");

        // ➜ FK déjà présente → on ne fait RIEN
        if ($fkExists) {
            return;
        }

        // ➜ FK absente → on l’ajoute
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_ville')
                  ->references('id')
                  ->on('villes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_ville']);
        });
    }
};
