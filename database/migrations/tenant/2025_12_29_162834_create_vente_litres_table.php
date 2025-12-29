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
        Schema::create('vente_litres', function (Blueprint $table) {

            // ==================================================
            // 🔹 CLÉ PRIMAIRE
            // ==================================================
            $table->id();

            // ==================================================
            // 🔹 RELATION CUVE
            // ==================================================
            $table->foreignId('id_cuve')
                ->constrained('cuves')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // ==================================================
            // 🔹 DONNÉES DE VENTE
            // ==================================================
            $table->decimal('qte_vendu', 12, 3)
                ->default(0);

            $table->text('commentaire')
                ->nullable();

            $table->boolean('status')
                ->default(false)
                ->comment('false = en cours, true = validée');

            // ==================================================
            // 🔹 CHAMPS D’AUDIT
            // ==================================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ==================================================
            // 🔹 TIMESTAMPS
            // ==================================================
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vente_litres');
    }
};
