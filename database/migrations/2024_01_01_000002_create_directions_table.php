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
        Schema::create('directions', function (Blueprint $table) {
            $table->id('id_direction');
            $table->string('filiale', 50);
            $table->string('nom_direction', 50);
            $table->string('code_direction', 10)->unique();
            $table->timestamps();

            // Index
            $table->index(['filiale', 'nom_direction']);
            $table->index('code_direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};
