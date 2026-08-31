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
        Schema::table('projects', function (Blueprint $table) {
            // Checkbox: Ce projet fait-il l'objet de maintenance ?
            $table->boolean('maintenance_glpi')->default(false)->after('ms_bucket_id');

            // ID du projet créé dans GLPI (stocké après création)
            $table->string('glpi_project_id')->nullable()->after('maintenance_glpi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['maintenance_glpi', 'glpi_project_id']);
        });
    }
};
