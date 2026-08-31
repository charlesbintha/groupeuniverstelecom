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
        Schema::create('kpi_indicateurs', function (Blueprint $table) {
            $table->id('indicateur_id');
            $table->foreignId('famille_id')
                ->constrained('kpi_familles', 'famille_id')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('ordre');
            $table->string('libelle', 400);
            $table->string('cible_affichage', 100)->nullable();
            $table->enum('cible_operateur', ['=', '>=', '<=', '>', '<'])->default('=');
            $table->decimal('cible_valeur', 10, 2)->nullable();
            $table->string('cible_unite', 20)->nullable();
            $table->enum('actif', ['Y', 'N'])->default('Y');
            $table->timestamps();

            // Index
            $table->index(['famille_id', 'ordre'], 'ix_kpi_ind_fam_ordre');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_indicateurs');
    }
};
