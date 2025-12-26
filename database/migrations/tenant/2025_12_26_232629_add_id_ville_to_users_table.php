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

            // 🔹 Ville de supervision (admin / superviseur)
            $table->foreignId('id_ville')
                ->nullable()
                ->after('id_station')
                ->constrained('villes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['id_ville']);
            $table->dropColumn('id_ville');
        });
    }
};
