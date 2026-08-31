<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get famille IDs by code
        $avancement = DB::table('kpi_familles')->where('code', 'AVANCEMENT')->value('famille_id');
        $delai = DB::table('kpi_familles')->where('code', 'DELAI')->value('famille_id');
        $couts = DB::table('kpi_familles')->where('code', 'COUTS')->value('famille_id');
        $qualite = DB::table('kpi_familles')->where('code', 'QUALITE')->value('famille_id');
        $risques = DB::table('kpi_familles')->where('code', 'RISQUES')->value('famille_id');

        $indicators = [
            // Indicateurs d'avancement
            ['famille_id' => $avancement, 'ordre' => 1, 'libelle' => '% de livrables réalisés', 'cible_affichage' => '100%', 'cible_operateur' => '>=', 'cible_valeur' => 100.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $avancement, 'ordre' => 2, 'libelle' => '% d\'activités terminées', 'cible_affichage' => '100%', 'cible_operateur' => '>=', 'cible_valeur' => 100.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $avancement, 'ordre' => 3, 'libelle' => 'Respect des jalons clés', 'cible_affichage' => '80%', 'cible_operateur' => '>=', 'cible_valeur' => 80.00, 'cible_unite' => '%', 'actif' => 'Y'],

            // Indicateurs de délai
            ['famille_id' => $delai, 'ordre' => 1, 'libelle' => 'Respect du planning', 'cible_affichage' => '90%', 'cible_operateur' => '>=', 'cible_valeur' => 90.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $delai, 'ordre' => 2, 'libelle' => 'Dérive de planning (%)', 'cible_affichage' => '<= 5%', 'cible_operateur' => '<=', 'cible_valeur' => 5.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $delai, 'ordre' => 3, 'libelle' => 'Nombre de jours de retard', 'cible_affichage' => '<= 3 jours', 'cible_operateur' => '<=', 'cible_valeur' => 3.00, 'cible_unite' => 'jours', 'actif' => 'Y'],

            // Indicateurs de coûts
            ['famille_id' => $couts, 'ordre' => 1, 'libelle' => 'Budget consommé vs budget alloué (% utilisé)', 'cible_affichage' => '<= 100%', 'cible_operateur' => '<=', 'cible_valeur' => 100.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $couts, 'ordre' => 2, 'libelle' => 'Écarts de coûts', 'cible_affichage' => '<= 3%', 'cible_operateur' => '<=', 'cible_valeur' => 3.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $couts, 'ordre' => 3, 'libelle' => 'Coût prévisionnel à terminaison', 'cible_affichage' => '≤ budget alloué', 'cible_operateur' => '=', 'cible_valeur' => null, 'cible_unite' => null, 'actif' => 'Y'],

            // Indicateurs de qualité
            ['famille_id' => $qualite, 'ordre' => 1, 'libelle' => 'Taux de non-conformité', 'cible_affichage' => '99%', 'cible_operateur' => '<=', 'cible_valeur' => 1.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $qualite, 'ordre' => 2, 'libelle' => 'Satisfaction utilisateur', 'cible_affichage' => '80%', 'cible_operateur' => '>=', 'cible_valeur' => 99.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $qualite, 'ordre' => 3, 'libelle' => 'Nombre de reprises ou corrections', 'cible_affichage' => '<= 1', 'cible_operateur' => '<=', 'cible_valeur' => 1.00, 'cible_unite' => 'nb', 'actif' => 'Y'],

            // Indicateurs de risques
            ['famille_id' => $risques, 'ordre' => 1, 'libelle' => 'Nb de risques identifiés vs traités', 'cible_affichage' => '75%', 'cible_operateur' => '>=', 'cible_valeur' => 100.00, 'cible_unite' => '%', 'actif' => 'Y'],
            ['famille_id' => $risques, 'ordre' => 2, 'libelle' => 'Nb de risques critiques', 'cible_affichage' => '1%', 'cible_operateur' => '<=', 'cible_valeur' => 0.00, 'cible_unite' => 'nb', 'actif' => 'Y'],
            ['famille_id' => $risques, 'ordre' => 3, 'libelle' => 'Taux de couverture des risques critiques', 'cible_affichage' => '100%', 'cible_operateur' => '>=', 'cible_valeur' => 100.00, 'cible_unite' => '%', 'actif' => 'Y'],
        ];

        foreach ($indicators as $indicator) {
            DB::table('kpi_indicateurs')->insert(
                array_merge($indicator, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ KPI Indicators seeded successfully (15 indicators across 5 families)');
    }
}
