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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string("name", length: 255);
            $table->string("telephone", length: 14)->unique();
            $table->string("adresse", length: 60);
            $table->string("email")->unique();
            $table->boolean("is_created")->default(false);
            $table->boolean("is_active")->default(true);
            $table->string('database')->unique();
            $table->boolean("status")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
