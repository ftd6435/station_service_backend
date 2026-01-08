<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {

            $table->enum('type_appro', [
                'approvisionnement',
                'retour_cuve',
            ])
            ->default('approvisionnement')
            ->after('qte_appro')
            ->comment('Type d\'entrée : approvisionnement ou retour cuve');
        });
    }

    public function down(): void
    {
        Schema::table('approvisionnement_cuves', function (Blueprint $table) {
            $table->dropColumn('type_appro');
        });
    }
};
