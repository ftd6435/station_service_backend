<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuves', function (Blueprint $table) {
            $table->id();

            // =========================
            // 🔹 RELATION MÉTIER
            // =========================
            // Station propriétaire de la cuve
            $table->foreignId('id_station')
                  ->nullable()
                  ->constrained('stations')
                  ->nullOnDelete();

            // =========================
            // 🔹 IDENTITÉ CUVE
            // =========================
            $table->string('libelle');
            $table->string('reference', 50)->nullable();
            $table->string('type_cuve'); // gasoil, essence, super, etc.

            // =========================
            // 🔹 STOCK
            // =========================
            $table->decimal('qt_initial', 15, 3)->default(0);
            $table->decimal('qt_actuelle', 15, 3)->default(0);

            // =========================
            // 🔹 PRIX
            // =========================
            $table->decimal('pu_vente', 15, 2)->default(0);
            $table->decimal('pu_unitaire', 15, 2)->default(0);

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

            // =========================
            // 🔹 CONTRAINTE MÉTIER
            // =========================
            // Une référence de cuve est unique par station
            $table->unique(['id_station', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuves');
    }
};
