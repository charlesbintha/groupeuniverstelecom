<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('statut_financier', 50)->default('Non démarré')->after('statut_initial');
            $table->boolean('ressource_a_mobiliser')->default(false)->after('statut_financier');
            $table->boolean('contractualisation')->default(false)->after('ressource_a_mobiliser');
            $table->string('contractualisation_type', 50)->nullable()->after('contractualisation');

            $table->index('statut_financier', 'idx_projects_statut_financier');
            $table->index('contractualisation', 'idx_projects_contractualisation');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_contractualisation');
            $table->dropIndex('idx_projects_statut_financier');

            $table->dropColumn([
                'statut_financier',
                'ressource_a_mobiliser',
                'contractualisation',
                'contractualisation_type',
            ]);
        });
    }
};
