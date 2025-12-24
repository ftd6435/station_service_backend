<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Station (liaison métier)
            $table->foreignId('id_station')
                  ->nullable()
                  ->after('role')
                  ->constrained('stations')
                  ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['id_station']);
            $table->dropColumn('id_station');

        });
    }
};
