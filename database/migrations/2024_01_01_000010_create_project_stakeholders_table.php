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
        Schema::create('project_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            // Rôle RACI
            $table->string('role', 100)->nullable();

            // Snapshot employé (pour gérer guests/externes)
            $table->unsignedBigInteger('employe_id')->nullable();
            $table->string('prenom_nom')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('aad_id', 64)->nullable()->comment('Azure AD Object ID');

            // Attentes
            $table->text('attentes')->nullable();

            $table->timestamps();

            // Index
            $table->index(['project_id', 'role']);
            $table->index('employe_id');
            $table->index('email');
            $table->index('aad_id');

            // Foreign key optionnelle (peut être NULL pour guests)
            $table->foreign('employe_id')
                ->references('id')
                ->on('employes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stakeholders');
    }
};
