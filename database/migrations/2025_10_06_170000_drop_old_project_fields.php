<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Supprime les anciens champs maintenant remplacés par la structure Exécutant/Contractant
     *
     * Champs supprimés:
     * - direction_filiale → remplacé par filiale_executant + filiale_contractant
     * - direction → remplacé par direction_executant + direction_contractant
     * - chef_projet → remplacé par owner_executant + owner_contractant
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['direction_filiale']);
            $table->dropIndex(['chef_projet']);
            $table->dropColumn([
                'direction_filiale',
                'direction',
                'chef_projet',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('direction_filiale')->nullable()->after('nature_projet');
            $table->string('direction')->nullable()->after('direction_filiale');
            $table->string('chef_projet')->nullable()->after('direction');
        });
    }
};
