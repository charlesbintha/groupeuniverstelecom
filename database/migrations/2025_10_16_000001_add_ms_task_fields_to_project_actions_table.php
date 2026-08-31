<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add ms_task_id and ms_task_etag to project_actions for MS Planner sync.
     */
    public function up(): void
    {
        Schema::table('project_actions', function (Blueprint $table) {
            // MS Planner task ID (nullable - only set for synced actions)
            $table->string('ms_task_id', 100)->nullable()->after('ordre');

            // MS Planner task etag (for optimistic concurrency control)
            $table->string('ms_task_etag', 255)->nullable()->after('ms_task_id');

            // Index for faster lookups
            $table->index('ms_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_actions', function (Blueprint $table) {
            $table->dropIndex(['ms_task_id']);
            $table->dropColumn(['ms_task_id', 'ms_task_etag']);
        });
    }
};
