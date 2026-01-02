<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations_comptes', function (Blueprint $table) {
            $table->id();

            // =================================================
            // 🔹 COMPTES
            // =================================================
            $table->foreignId('id_compte')
                ->constrained('comptes')
                ->onDelete('cascade');

            // 🔹 Pour les transferts
            $table->foreignId('id_source')
                ->nullable()
                ->constrained('comptes')
                ->onDelete('cascade');

            $table->foreignId('id_destination')
                ->nullable()
                ->constrained('comptes')
                ->onDelete('cascade');

            // =================================================
            // 🔹 TYPE OPÉRATION
            // =================================================
            $table->foreignId('id_type_operation')
                ->constrained('type_operations')
                ->onDelete('cascade');

            // =================================================
            // 🔹 MÉTIER
            // =================================================
            $table->decimal('montant', 15, 2);
            $table->string('reference', 100);
            $table->string('commentaire', 255)->nullable();

            $table->enum('status', [
                'en_attente',
                'effectif',
                'annule',
            ])->default('effectif');

            // =================================================
            // 🔹 AUDIT
            // =================================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();

            // =================================================
            // 🔹 INDEX
            // =================================================
            $table->index(['id_compte', 'status']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_comptes');
    }
};
