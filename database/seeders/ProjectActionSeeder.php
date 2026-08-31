<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds project actions with specific IDs from production database.
     * Note: ms_task_id and ms_task_etag are NULL by default.
     * Run 'php artisan planner:backfill-task-ids' after seeding to populate these fields from MS Planner.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate table
        DB::table('project_actions')->truncate();

        // Insert data with specific IDs from production
        // ms_task_id and ms_task_etag will be NULL initially (can be backfilled later with the artisan command)
        $actions = [
            ['id' => 80, 'project_id' => 45, 'libelle' => 'Créer un plan pour le suite des activités', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 81, 'project_id' => 45, 'libelle' => 'Recruiter les membre des l\'équipe projet', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 82, 'project_id' => 45, 'libelle' => 'Organiser réunion de lancement', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 83, 'project_id' => 46, 'libelle' => 'Fourniture d\'équipements réseaux informatiques, téléphones et accessoires puis installation', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 84, 'project_id' => 46, 'libelle' => 'Fourniture et installation d\'équipements de connexion wifi et câblage ;', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 85, 'project_id' => 46, 'libelle' => 'Fourniture et installation de dispositif d\'accès physique et de détection d\'incendie ;', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 86, 'project_id' => 46, 'libelle' => 'Fourniture et installation de Climatiseurs et d\'inverseurs de source', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 89, 'project_id' => 48, 'libelle' => 'Partage du planning de déploiement', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 90, 'project_id' => 48, 'libelle' => 'Câblage réseaux informatiques', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 91, 'project_id' => 48, 'libelle' => 'Installation Climatisation', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 92, 'project_id' => 48, 'libelle' => 'Installation onduleurs', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 93, 'project_id' => 48, 'libelle' => 'Installation caméras et postes téléphoniques', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 94, 'project_id' => 48, 'libelle' => 'Cloisonement du local technique', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 95, 'project_id' => 48, 'libelle' => 'Fourniture et livraison de chaises', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 96, 'project_id' => 49, 'libelle' => 'Partage du planning de déploiement', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 97, 'project_id' => 49, 'libelle' => 'Câblage réseaux informatiques', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 98, 'project_id' => 49, 'libelle' => 'Installation Climatisation', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 99, 'project_id' => 49, 'libelle' => 'Installation onduleurs', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 100, 'project_id' => 49, 'libelle' => 'Installation caméras et postes téléphoniques', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 101, 'project_id' => 49, 'libelle' => 'Cloisonement du local technique', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 102, 'project_id' => 49, 'libelle' => 'Fourniture et livraison de chaises', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 103, 'project_id' => 50, 'libelle' => 'Partage du planning de déploiement', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 104, 'project_id' => 50, 'libelle' => 'Câblage réseaux informatiques (avec génie civil en amont)', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 105, 'project_id' => 50, 'libelle' => 'Installation Climatisation', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 106, 'project_id' => 50, 'libelle' => 'Installation onduleurs', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 107, 'project_id' => 50, 'libelle' => 'Installation caméras et postes téléphoniques', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 108, 'project_id' => 50, 'libelle' => 'Cloisonement du local technique', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 109, 'project_id' => 50, 'libelle' => 'Fourniture et livraison de chaises', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 110, 'project_id' => 50, 'libelle' => 'Paiement perdium (agents GUT & DOUANE)', 'ordre' => 8, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 111, 'project_id' => 50, 'libelle' => 'Frais de transport du matériel', 'ordre' => 9, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 112, 'project_id' => 51, 'libelle' => 'Mission survey Axe NORD', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 113, 'project_id' => 51, 'libelle' => 'Mission survey Axe SUD', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 114, 'project_id' => 51, 'libelle' => 'Mission survey Axe SUD-EST', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 115, 'project_id' => 51, 'libelle' => 'Mission survey Zone CENTRE', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 116, 'project_id' => 51, 'libelle' => 'Mission survey DAKAR', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 117, 'project_id' => 51, 'libelle' => 'Upload des informations/besoins recueillis lors des survey (en temps réel) sur la plateforme prévue à cet effet', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 118, 'project_id' => 52, 'libelle' => 'Signature du contrat', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 119, 'project_id' => 52, 'libelle' => 'Rédaction de l\'expression du besoin', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 120, 'project_id' => 52, 'libelle' => 'Validation des demandes d\'achat', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 121, 'project_id' => 52, 'libelle' => 'Achat du matériel', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 122, 'project_id' => 52, 'libelle' => 'Réceptionner le matériel et planifier la livraison', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 123, 'project_id' => 52, 'libelle' => 'Livraison du matériel', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 124, 'project_id' => 52, 'libelle' => 'Signature des BL', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 125, 'project_id' => 52, 'libelle' => 'Transmettre les BL signés à Luxdev et la facture', 'ordre' => 8, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 126, 'project_id' => 52, 'libelle' => 'Réceptionner l\'attestation de service fait', 'ordre' => 9, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 127, 'project_id' => 53, 'libelle' => 'Réception du BC', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 128, 'project_id' => 53, 'libelle' => 'Expression du besoin', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 129, 'project_id' => 53, 'libelle' => 'Validation des demandes d\'achat', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 130, 'project_id' => 53, 'libelle' => 'Achat du matériel', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 131, 'project_id' => 53, 'libelle' => 'Réceptionner le matériel et planifier la livraison', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 132, 'project_id' => 53, 'libelle' => 'Livraison du matériel et signer les BL', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 133, 'project_id' => 53, 'libelle' => 'Réceptionner le PV de recette', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 134, 'project_id' => 53, 'libelle' => 'Déposer la facture', 'ordre' => 8, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 135, 'project_id' => 54, 'libelle' => 'Analyse des TDR', 'ordre' => 1, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 136, 'project_id' => 54, 'libelle' => 'Elaboration de la Note de Cadrage', 'ordre' => 2, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 137, 'project_id' => 54, 'libelle' => 'Soutenance de la Note de Cadrage', 'ordre' => 3, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 138, 'project_id' => 54, 'libelle' => 'Notification officielle d\'Attribution', 'ordre' => 4, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 139, 'project_id' => 54, 'libelle' => 'Rédaction des Annexes et  termes du contrat', 'ordre' => 5, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 140, 'project_id' => 54, 'libelle' => 'Passage des commandes  Dell', 'ordre' => 6, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 141, 'project_id' => 54, 'libelle' => 'Réception et validation des commandes', 'ordre' => 7, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 142, 'project_id' => 54, 'libelle' => 'Livraison des baies de stockage et switch SAN  chez le client final', 'ordre' => 8, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 143, 'project_id' => 54, 'libelle' => 'Réunion de lancement pour l\'exécution technique du projet', 'ordre' => 9, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 144, 'project_id' => 54, 'libelle' => 'Workhsop technique de collecte des informations', 'ordre' => 10, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 145, 'project_id' => 54, 'libelle' => 'Workshop de Design et de conception de la solution cible', 'ordre' => 11, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 146, 'project_id' => 54, 'libelle' => 'Elaboration des livrables (HLD, LLD, Plan de migration)', 'ordre' => 12, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 147, 'project_id' => 54, 'libelle' => 'Configuration des érquipements', 'ordre' => 13, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 148, 'project_id' => 54, 'libelle' => 'Rackage et cablâge', 'ordre' => 14, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 149, 'project_id' => 54, 'libelle' => 'Migration  vers la solution Cible et bascule de productio', 'ordre' => 15, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 150, 'project_id' => 54, 'libelle' => 'Validation  aprés une semaine d\'observation', 'ordre' => 16, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 151, 'project_id' => 54, 'libelle' => 'Réalisation des  formations en 2 sessions de 3 personnes au Centre Fastlane France', 'ordre' => 17, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 152, 'project_id' => 54, 'libelle' => 'Livraison des équipements C9300', 'ordre' => 18, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 153, 'project_id' => 54, 'libelle' => 'Livraison des modules sfp  faisant l\'objet de la réserve de l\'action précédente', 'ordre' => 19, 'ms_task_id' => null, 'ms_task_etag' => null],
            ['id' => 154, 'project_id' => 54, 'libelle' => 'Elaboration  et emission des PV de réceptions définitives et des attestations de bonnes exécution', 'ordre' => 20, 'ms_task_id' => null, 'ms_task_etag' => null],
        ];

        // Insert in batches of 20 for performance
        foreach (array_chunk($actions, 20) as $batch) {
            DB::table('project_actions')->insert($batch);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
