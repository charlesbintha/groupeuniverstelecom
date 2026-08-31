<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add resource type field (GUT, Banque, etc.)
            $table->string('resource_type', 50)->nullable()->after('ressource_a_mobiliser');

            // Add bank selection field (for when resource_type = 'Banque')
            $table->string('resource_bank', 50)->nullable()->after('resource_type');

            // Add indexes for performance
            $table->index('resource_type');
            $table->index('resource_bank');
        });

        // Data migration: Set existing projects with ressource_a_mobiliser = 1 to resource_type = 'GUT'
        DB::table('projects')
            ->where('ressource_a_mobiliser', true)
            ->whereNull('resource_type')
            ->update(['resource_type' => 'GUT']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['resource_type']);
            $table->dropIndex(['resource_bank']);
            $table->dropColumn(['resource_type', 'resource_bank']);
        });
    }
};
