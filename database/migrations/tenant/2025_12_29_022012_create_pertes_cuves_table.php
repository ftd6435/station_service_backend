<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertes_cuves', function (Blueprint $table) {
            $table->id();

            // =========================
            // MÉTIER
            // =========================
            $table->foreignId('id_cuve')
                  ->constrained('cuves')
                  ->cascadeOnDelete();

            $table->decimal('quantite_perdue', 15, 3);
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
        Schema::dropIfExists('pertes_cuves');
    }
};
