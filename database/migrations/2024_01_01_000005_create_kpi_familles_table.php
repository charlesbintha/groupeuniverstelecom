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
        Schema::create('kpi_familles', function (Blueprint $table) {
            $table->id('famille_id');
            $table->string('code', 50)->unique();
            $table->string('nom_famille', 200);
            $table->text('mesure')->nullable();
            $table->string('applicabilite', 200)->nullable();
            $table->unsignedTinyInteger('ordre');
            $table->enum('actif', ['Y', 'N'])->default('Y');
            $table->timestamps();

            // Index
            $table->index('code');
            $table->index(['actif', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_familles');
    }
};
