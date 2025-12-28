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

            // =================================================
            // RELATIONS (TOUTES NULLABLES)
            // =================================================

            $table->foreignId('id_station')
                ->nullable()
                ->constrained('stations')
                ->nullOnDelete();

            /**
             * 🔹 CUVE
             * La table reste "produits"
             * mais la FK métier est id_cuve
             */
            $table->foreignId('id_cuve')
                ->nullable()
                ->constrained('produits')
                ->nullOnDelete();

            $table->foreignId('id_affectation')
                ->nullable()
                ->constrained('affectations')
                ->nullOnDelete();

            // =================================================
            // DONNÉES DE VENTE
            // =================================================

            $table->decimal('index_debut', 15, 2)->nullable();
            $table->decimal('index_fin', 15, 2)->nullable();
            $table->decimal('qte_vendu', 15, 2)->nullable();

            // =================================================
            // ÉTAT
            // =================================================

            $table->boolean('status')->default(false);

            // =================================================
            // AUDIT
            // =================================================

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
