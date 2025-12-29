<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_ventes', function (Blueprint $table) {
            $table->id();

            // =========================
            // MÉTIER
            // =========================
            $table->foreignId('id_vente')
                  ->constrained('ligne_ventes')
                  ->cascadeOnDelete();

            $table->string('commentaire')->nullable();

            // =========================
            // AUDIT
            // =========================
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('modify_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_ventes');
    }
};
