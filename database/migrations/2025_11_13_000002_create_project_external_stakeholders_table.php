<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_external_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->text('attentes')->nullable();
            $table->timestamps();

            $table->index('project_id', 'idx_ext_stakeholders_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_external_stakeholders');
    }
};
