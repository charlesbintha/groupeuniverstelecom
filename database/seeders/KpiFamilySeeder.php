<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $families = [
            [
                'code' => 'AVANCEMENT',
                'nom_famille' => 'Indicateurs d\'avancement',
                'mesure' => 'Progression réelle du projet par rapport au plan initial',
                'applicabilite' => null,
                'ordre' => 1,
                'actif' => 'Y',
            ],
            [
                'code' => 'DELAI',
                'nom_famille' => 'Indicateurs de délai',
                'mesure' => 'Capacité du projet à respecter les échéances',
                'applicabilite' => null,
                'ordre' => 2,
                'actif' => 'Y',
            ],
            [
                'code' => 'COUTS',
                'nom_famille' => 'Indicateurs de coûts',
                'mesure' => 'Suivi budgétaire du projet',
                'applicabilite' => null,
                'ordre' => 3,
                'actif' => 'Y',
            ],
            [
                'code' => 'QUALITE',
                'nom_famille' => 'Indicateurs de qualité',
                'mesure' => 'Niveau de conformité des livrables et satisfaction client/utilisateur',
                'applicabilite' => null,
                'ordre' => 4,
                'actif' => 'Y',
            ],
            [
                'code' => 'RISQUES',
                'nom_famille' => 'Indicateurs de risques',
                'mesure' => 'Suivi de la gestion des risques tout au long du projet',
                'applicabilite' => null,
                'ordre' => 5,
                'actif' => 'Y',
            ],
        ];

        foreach ($families as $family) {
            DB::table('kpi_familles')->updateOrInsert(
                ['code' => $family['code']],
                array_merge($family, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ KPI Families seeded successfully (5 families)');
    }
}
