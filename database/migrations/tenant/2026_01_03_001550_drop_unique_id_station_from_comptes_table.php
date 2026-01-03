<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {

            // 🔥 suppression de la contrainte UNIQUE sur id_station
            $table->dropUnique(['id_station']);
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {

            // 🔁 restauration de la contrainte (rollback)
            $table->unique('id_station');
        });
    }
};
