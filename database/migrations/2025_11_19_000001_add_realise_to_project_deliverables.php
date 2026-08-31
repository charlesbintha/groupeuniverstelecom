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
        Schema::table('project_deliverables', function (Blueprint $table) {
            $table->boolean('realise')->default(false)->after('date_prevue');
            $table->index('realise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_deliverables', function (Blueprint $table) {
            $table->dropIndex(['realise']);
            $table->dropColumn('realise');
        });
    }
};
