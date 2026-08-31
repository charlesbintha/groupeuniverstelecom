<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_external_stakeholders', function (Blueprint $table) {
            $table->string('organisation', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_external_stakeholders', function (Blueprint $table) {
            $table->string('organisation', 100)->nullable(false)->change();
        });
    }
};
