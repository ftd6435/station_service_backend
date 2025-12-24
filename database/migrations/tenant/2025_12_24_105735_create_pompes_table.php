<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pompes', function (Blueprint $table) {
            $table->id();

            // Métier
            $table->string('libelle', 150);
            $table->string('reference', 50)->nullable();

            $table->enum('type_pompe', ['essence', 'gasoil']);
            $table->decimal('index_initial', 15, 2)->default(0);

            // Relation station
            $table->foreignId('id_station')
                  ->constrained('stations')
                  ->cascadeOnDelete();

            // État
            $table->boolean('status')->default(true);

            // Audit
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
        Schema::dropIfExists('pompes');
    }
};
