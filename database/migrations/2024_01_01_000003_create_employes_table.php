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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('prenom_nom')->nullable();
            $table->date('date_recrutement')->nullable();
            $table->enum('sexe', ['Homme', 'Femme'])->nullable();
            $table->enum('situation_matrimoniale', [
                'Célibataire',
                'Marié',
                'Divorcé',
                'Veuf',
                'Mariage coutumier'
            ])->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->string('groupe_sanguin', 20)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 200)->nullable();
            $table->string('profession', 100)->nullable();
            $table->string('religion', 100)->nullable();
            $table->text('adresse')->nullable();
            $table->string('nationalite', 100)->nullable();
            $table->string('filiale', 100);
            $table->string('aad_id', 64)->nullable()->unique()->comment('Azure AD Object ID');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            // Index
            $table->index('email');
            $table->index('aad_id');
            $table->index(['filiale', 'actif']);
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
