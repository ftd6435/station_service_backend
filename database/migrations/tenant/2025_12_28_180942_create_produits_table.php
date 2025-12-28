<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();

            // =========================
            // Données métier
            // =========================
            $table->string('libelle');
            $table->string('type_produit');

            $table->decimal('qt_initial', 15, 3)->nullable()->default(0);
            $table->decimal('qt_actuelle', 15, 3)->nullable()->default(0);

            $table->decimal('pu_vente', 15, 2)->nullable()->default(0);
            $table->decimal('pu_unitaire', 15, 2)->nullable()->default(0);

            $table->boolean('status')->default(true);

            // =========================
            // Audit
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
        Schema::dropIfExists('produits');
    }
};
