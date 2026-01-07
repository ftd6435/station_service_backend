<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();

            // =========================
            // 🔹 RELATIONS MÉTIER
            // =========================

            // Utilisateur (pompiste / gérant / etc.)
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnDelete();

            // Station
            $table->foreignId('id_station')
                ->constrained('stations')
                ->cascadeOnDelete();

            // Pompe
            $table->foreignId('id_pompe')
                ->nullable()
                ->constrained('pompes')
                ->nullOnDelete();

            // =========================
            // 🔹 ÉTAT
            // =========================
            $table->boolean('status')->default(true);

            // =========================
            // 🔹 AUDIT
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
        Schema::dropIfExists('affectations');
    }
};
