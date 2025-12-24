<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametrage_stations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_station')
                  ->constrained('stations')
                  ->cascadeOnDelete();

            $table->time('h_ouvert')->nullable();
            $table->time('h_ferme')->nullable();

            // Audit (FK)
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('modify_by')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametrage_stations');
    }
};
