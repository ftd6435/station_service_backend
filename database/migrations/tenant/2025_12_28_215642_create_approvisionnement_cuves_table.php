<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvisionnement_cuves', function (Blueprint $table) {
            $table->id();

            /**
             * =================================================
             * RELATION CUVE
             * =================================================
             */
            $table->foreignId('id_cuve')
                  ->constrained('cuves')
                  ->cascadeOnDelete();

            /**
             * =================================================
             * DONNÉES D’APPROVISIONNEMENT
             * =================================================
             */
            $table->decimal('qte_appro', 15, 2);
            $table->decimal('pu_unitaire', 15, 2)->nullable();
            $table->text('commentaire')->nullable();

            /**
             * =================================================
             * AUDIT
             * =================================================
             */
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvisionnement_cuves');
    }
};
