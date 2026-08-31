<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $createView = $isSqlite ? 'CREATE VIEW v_kpi AS' : 'CREATE OR REPLACE VIEW v_kpi AS';
        $targetExpression = $isSqlite
            ? "i.cible_operateur || ' ' || IFNULL(i.cible_valeur, '') || CASE WHEN i.cible_unite IS NULL THEN '' ELSE ' ' || i.cible_unite END"
            : "CONCAT(i.cible_operateur, ' ', IFNULL(i.cible_valeur, ''), IF(i.cible_unite IS NULL, '', CONCAT(' ', i.cible_unite)))";

        DB::statement("
            {$createView}
            SELECT
                f.ordre AS famille_ordre,
                f.nom_famille AS nom_famille,
                f.mesure AS mesure,
                f.applicabilite AS applicabilite,
                i.ordre AS indicateur_ordre,
                i.libelle AS libelle,
                COALESCE(
                    i.cible_affichage,
                    {$targetExpression}
                ) AS cible
            FROM kpi_familles f
            LEFT JOIN kpi_indicateurs i
                ON i.famille_id = f.famille_id
                AND i.actif = 'Y'
            WHERE f.actif = 'Y'
            ORDER BY f.ordre ASC, i.ordre ASC
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_kpi');
    }
};
