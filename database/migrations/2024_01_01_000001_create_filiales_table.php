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
        Schema::create('filiales', function (Blueprint $table) {
            $table->id('id_filiale');
            $table->string('nom_filiale', 50)->unique();
            $table->string('code_filiale', 10)->unique();
            $table->timestamps();

            // Index
            $table->index('code_filiale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filiales');
    }
};
