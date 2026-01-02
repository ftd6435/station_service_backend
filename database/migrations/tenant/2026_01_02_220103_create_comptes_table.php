<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();

            // =============================================
            // 🔹 MÉTIER
            // =============================================
            $table->foreignId('id_station')
                ->constrained('stations')
                ->onDelete('cascade');
             $table->string('numero', 50)->unique();
            $table->string('libelle', 100)->nullable();
            $table->string('commentaire', 255)->nullable();

            $table->decimal('solde_initial', 15, 2)->default(0);

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
            $table->unique('id_station'); // 1 compte par station
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
