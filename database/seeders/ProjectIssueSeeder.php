<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectIssueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds project issues with specific IDs from production database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate table
        DB::table('project_issues')->truncate();

        // Insert data with specific IDs from production
        $issues = [
            ['id' => 43, 'project_id' => 43, 'categorie' => 'Enjeux', 'detail' => ''],
            ['id' => 44, 'project_id' => 44, 'categorie' => 'Enjeux', 'detail' => ''],
            ['id' => 45, 'project_id' => 45, 'categorie' => 'Risques', 'detail' => 'Non disponibilité des ressources financières à temps'],
            ['id' => 46, 'project_id' => 46, 'categorie' => 'Enjeux', 'detail' => 'Renforcer de notre présence au sein du réseau de la Douane'],
            ['id' => 47, 'project_id' => 46, 'categorie' => 'Contraintes', 'detail' => 'Retard de livraison du matériel (partie téléphonie, CISCO)'],
            ['id' => 48, 'project_id' => 46, 'categorie' => 'Risques', 'detail' => 'Dépassement de délai'],
            ['id' => 51, 'project_id' => 48, 'categorie' => 'Enjeux', 'detail' => 'Stratégique car existence de plusieurs sites du même type, des projets similaires pourront donc nous être confiés'],
            ['id' => 52, 'project_id' => 48, 'categorie' => 'Contraintes', 'detail' => 'Demande urgente du client, connexion intégrale du site avant fin septembre'],
            ['id' => 53, 'project_id' => 48, 'categorie' => 'Risques', 'detail' => 'Indisponibilité de l\'ensemble du matériel avant démarrage mission'],
            ['id' => 54, 'project_id' => 48, 'categorie' => 'Risques', 'detail' => 'Risque financier car préfinancement de 120.205.474 TTC (sur avance de 75 millions du client) pour les deux sites'],
            ['id' => 55, 'project_id' => 49, 'categorie' => 'Enjeux', 'detail' => 'Stratégique car existence de plusieurs sites du même type, des projets similaires pourront donc nous être confiés'],
            ['id' => 56, 'project_id' => 49, 'categorie' => 'Contraintes', 'detail' => 'Demande urgente du client, connexion intégrale du site avant fin septembre'],
            ['id' => 57, 'project_id' => 49, 'categorie' => 'Risques', 'detail' => 'Indisponibilité de l\'ensemble du matériel avant démarrage mission'],
            ['id' => 58, 'project_id' => 49, 'categorie' => 'Risques', 'detail' => 'Risque financier car préfinancement de 120.205.474 TTC (sur avance de 75 millions du client) pour les deux sites'],
            ['id' => 59, 'project_id' => 50, 'categorie' => 'Enjeux', 'detail' => 'Stratégique car existence de plusieurs sites du même type, donc des projets similaires pourraient nous être confiés à l\'issu'],
            ['id' => 60, 'project_id' => 50, 'categorie' => 'Risques', 'detail' => 'Dépassement des délais prévisionnels du client (fin Septembre initialement)'],
            ['id' => 61, 'project_id' => 50, 'categorie' => 'Contraintes', 'detail' => 'Indisponibilité des fonds pour effectuer l\'ensemble des travaux'],
            ['id' => 62, 'project_id' => 50, 'categorie' => 'Contraintes', 'detail' => 'Indisponibilité des équipes (faible effectif)'],
            ['id' => 63, 'project_id' => 50, 'categorie' => 'Enjeux', 'detail' => ''],
            ['id' => 64, 'project_id' => 51, 'categorie' => 'Enjeux', 'detail' => 'Mainten de l\'infrastructure existante, et meilleur positionnement vis-à-vis du projet structurant SIGIF à venir'],
            ['id' => 65, 'project_id' => 51, 'categorie' => 'Contraintes', 'detail' => 'Prise en charge financière de l\'ensemble du périmètre du projet'],
            ['id' => 66, 'project_id' => 51, 'categorie' => 'Risques', 'detail' => 'Indisponibilité de ressources dû aux nombreux sites d\'intervention'],
            ['id' => 67, 'project_id' => 52, 'categorie' => 'Risques', 'detail' => 'retard de l\'achat du matériel'],
            ['id' => 68, 'project_id' => 52, 'categorie' => 'Contraintes', 'detail' => 'Lieux de livraison du client final'],
            ['id' => 69, 'project_id' => 53, 'categorie' => 'Enjeux', 'detail' => 'Livrer dans les temps pur figurer parmi les bon partenaire de GIZ'],
            ['id' => 70, 'project_id' => 53, 'categorie' => 'Risques', 'detail' => 'retard de l\'achat du matériel'],
            ['id' => 71, 'project_id' => 53, 'categorie' => 'Contraintes', 'detail' => 'Exécuter deux projet de même nature en même temps'],
            ['id' => 72, 'project_id' => 54, 'categorie' => 'Enjeux', 'detail' => 'Bascule des données critiques sans Interruption de service et sans perte de données'],
            ['id' => 73, 'project_id' => 54, 'categorie' => 'Contraintes', 'detail' => 'Exécution du projet  suivant la disponibilité du client'],
            ['id' => 74, 'project_id' => 54, 'categorie' => 'Risques', 'detail' => 'Dépassement du planning d\'exécution'],
        ];

        // Insert in batches of 15 for performance
        foreach (array_chunk($issues, 15) as $batch) {
            DB::table('project_issues')->insert($batch);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
