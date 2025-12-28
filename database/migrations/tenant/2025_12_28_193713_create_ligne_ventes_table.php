<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_ventes', function (Blueprint $table) {
            $table->id();

            // Relations (TOUTES NULLABLES)
            $table->foreignId('id_station')
                ->nullable()
                ->constrained('stations')
                ->nullOnDelete();

            $table->foreignId('id_produit')
                ->nullable()
                ->constrained('produits')
                ->nullOnDelete();

            $table->foreignId('id_affectation')
                ->nullable()
                ->constrained('affectations')
                ->nullOnDelete();

            // Données vente (TOUTES NULLABLES)
            $table->decimal('index_debut', 15, 2)->nullable();
            $table->decimal('index_fin', 15, 2)->nullable();
            $table->decimal('qte_vendu', 15, 2)->nullable();

            // État
            $table->boolean('status')->default(false);

            // Audit
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
        Schema::dropIfExists('ligne_ventes');
    }
};
