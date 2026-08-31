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
            // Make code_projet nullable and drop unique constraint temporarily
            $table->dropUnique(['code_projet']);
            $table->string('code_projet', 100)->nullable()->change();

            // Re-add unique constraint but allow NULL values
            // SQLite: unique constraint allows multiple NULL values by default
            // MySQL/PostgreSQL: unique constraint allows multiple NULL values
            $table->unique('code_projet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Restore original constraint: NOT NULL + UNIQUE
            $table->dropUnique(['code_projet']);
            $table->string('code_projet', 100)->nullable(false)->change();
            $table->unique('code_projet');
        });
    }
};
