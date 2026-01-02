<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_operations', function (Blueprint $table) {
            $table->id();

            // =============================================
            // 🔹 MÉTIER
            // =============================================
            $table->string('libelle', 100);
            $table->string('commentaire', 255)->nullable();

            /**
             * nature :
             * 1 = entrée
             * 0 = sortie
             * 2 = transfert inter-station
             */
            $table->tinyInteger('nature');

            // =============================================
            // 🔹 AUDIT (CASCADE)
            // =============================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();

            // =============================================
            // 🔹 CONTRAINTE MÉTIER
            // =============================================
            $table->unique(['libelle', 'nature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_operations');
    }
};
