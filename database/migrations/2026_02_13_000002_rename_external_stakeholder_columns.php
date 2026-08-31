<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_external_stakeholders', function (Blueprint $table) {
            $table->renameColumn('nom', 'organisation');
            $table->renameColumn('prenom', 'nom_complet');
        });
    }

    public function down(): void
    {
        Schema::table('project_external_stakeholders', function (Blueprint $table) {
            $table->renameColumn('organisation', 'nom');
            $table->renameColumn('nom_complet', 'prenom');
        });
    }
};
