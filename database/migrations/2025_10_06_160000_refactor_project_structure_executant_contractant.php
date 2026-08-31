<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * REFONTE: Structure Exécutant vs Contractant
     *
     * Ancienne structure:
     * - direction_filiale (une seule)
     * - direction (une seule)
     * - chef_projet (un seul)
     *
     * Nouvelle structure:
     * - filiale_executant + filiale_contractant
     * - direction_executant + direction_contractant
     * - owner_executant + owner_contractant
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Ajouter les nouveaux champs APRÈS les anciens (pour faciliter la migration)
            $table->string('filiale_executant')->nullable()->after('direction_filiale');
            $table->string('filiale_contractant')->nullable()->after('filiale_executant');

            $table->string('direction_executant')->nullable()->after('direction');
            $table->string('direction_contractant')->nullable()->after('direction_executant');

            $table->string('owner_executant')->nullable()->after('chef_projet');
            $table->string('owner_contractant')->nullable()->after('owner_executant');
        });

        // Migrer les données existantes vers la nouvelle structure
        DB::statement("
            UPDATE projects
            SET
                filiale_executant = direction_filiale,
                filiale_contractant = direction_filiale,
                direction_executant = direction,
                direction_contractant = NULL,
                owner_executant = chef_projet,
                owner_contractant = NULL
            WHERE direction_filiale IS NOT NULL
               OR direction IS NOT NULL
               OR chef_projet IS NOT NULL
        ");

        // Note: Les anciens champs sont conservés pour permettre un rollback
        // Une future migration pourra les supprimer après validation
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'filiale_executant',
                'filiale_contractant',
                'direction_executant',
                'direction_contractant',
                'owner_executant',
                'owner_contractant',
            ]);
        });

        // Les données dans les anciens champs sont préservées
    }
};
