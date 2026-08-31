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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('code_projet', 100)->unique();
            $table->string('nom_projet');

            // Catégorisation
            $table->string('type_projet', 100); // Enum: Interne, Externe
            $table->string('nature_projet', 20)->nullable(); // Enum: B2B, B2C, GOV, Autres
            $table->string('axe_strategique')->nullable();

            // Organisation
            $table->string('direction_filiale')->nullable();
            $table->string('direction', 50)->nullable();
            $table->string('chef_projet')->nullable();

            // Planification
            $table->date('date_demarrage')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut_initial', 100)->nullable(); // Enum: Planifié, En cours, Pause, Terminé

            // Budget
            $table->decimal('budget_initial', 14, 2)->nullable();

            // Description
            $table->text('objectif_projet')->nullable();
            $table->text('prochaine_etape')->nullable();

            // Intégration Salesforce
            $table->string('sf_opportunity_id', 40)->nullable();
            $table->string('sf_opportunity_name')->nullable();
            $table->string('sf_opportunity_stage', 80)->nullable();
            $table->decimal('sf_opportunity_amount', 18, 2)->nullable();

            // Intégration Microsoft Graph / Planner
            $table->string('ms_group_id', 64)->nullable()->comment('M365 Group ID');
            $table->string('ms_plan_id', 64)->nullable()->comment('Planner Plan ID');
            $table->string('ms_bucket_id', 64)->nullable()->comment('Planner Bucket ID');

            // Timestamps + Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('code_projet');
            $table->index('type_projet');
            $table->index('nature_projet');
            $table->index('statut_initial');
            $table->index('direction_filiale');
            $table->index('chef_projet');
            $table->index('date_demarrage');
            $table->index(['sf_opportunity_id'], 'idx_projects_sf_opp');
            $table->index('ms_plan_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
