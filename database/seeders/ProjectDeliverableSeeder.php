<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectDeliverableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds project deliverables with specific IDs from production database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate table
        DB::table('project_deliverables')->truncate();

        // Insert data with specific IDs from production
        $deliverables = [
            ['id' => 54, 'project_id' => 43, 'livrable' => 'Planning prévisionnel', 'description' => '', 'date_prevue' => null],
            ['id' => 55, 'project_id' => 43, 'livrable' => 'Rapport d\'exécution', 'description' => '', 'date_prevue' => null],
            ['id' => 56, 'project_id' => 44, 'livrable' => 'Planning prévisionnel', 'description' => '', 'date_prevue' => null],
            ['id' => 57, 'project_id' => 44, 'livrable' => 'Rapport d\'exécution', 'description' => '', 'date_prevue' => null],
            ['id' => 58, 'project_id' => 45, 'livrable' => 'Calendrier de projet', 'description' => 'Liste dees jalons du projets', 'date_prevue' => '2025-09-30'],
            ['id' => 59, 'project_id' => 45, 'livrable' => 'Document d\'architecture technique', 'description' => '', 'date_prevue' => '2025-10-10'],
            ['id' => 60, 'project_id' => 45, 'livrable' => 'Maquette Interface client', 'description' => 'Maquette app mobile et Web client', 'date_prevue' => '2025-10-30'],
            ['id' => 61, 'project_id' => 45, 'livrable' => 'MVP', 'description' => 'Un version démo du produit', 'date_prevue' => '2025-11-30'],
            ['id' => 62, 'project_id' => 45, 'livrable' => 'Plateforme E-Teral V1', 'description' => '', 'date_prevue' => '2025-12-31'],
            ['id' => 63, 'project_id' => 46, 'livrable' => 'Fourniture d\'équipements réseaux informatiques', 'description' => 'La fourniture de vingt (20) switches CISCO de niveau 2 de 24 ports ; La fourniture de vingt-cinq (25) switches FORTINET niveau 2 de 24 ports ; La fourniture de six (06) switches de niveau 2 de 48 Ports ; La fourniture de deux CUCM ; La fourniture de trois cent (300) postes téléphoniques modernes et intelligents ; L\'installation du call manager et son déploiement sur les téléphones ; La fourniture d\'un (01) armoire informatique 42U profondeur 1200 ;  La fourniture de douze (12) coffrets informatiques de 24U profondeur 1070 ;  La fourniture de deux coffret (02) coffrets informatiques de 12U profondeur 600 ; La fourniture des accessoires y afférent ;  L\'identification et le renommage de tout le câblage informatique et téléphonique des bâtiments de la DGD ;  La certification de tout le câblage informatique et téléphonique des bâtiments de la DGD ; La fourniture des licences support de trois (03) ans pour les routeurs, switches et les téléphones;  La fourniture des licences d\'appliance et d\'accessoires au nom de la Douane sénégalaise ; L\'installation de tous les équipements sur les sites désignés.', 'date_prevue' => '2025-09-30'],
            ['id' => 64, 'project_id' => 46, 'livrable' => 'Fourniture d\'équipements de connexion & câblage', 'description' => 'Le câblage du bureau courrier à l\'entrée de la DGD ; Le câblage du bureau du Chef BAF de la DRAV ; La fourniture et l\'installation de sept (07) points d\'accès WiFi UBIQUITI ;  La fourniture Unifi switch PoE+ Gigabit US-16-150W  Liaison en fibre optique entre la DRAV et la DFPE ;', 'date_prevue' => '2025-09-30'],
            ['id' => 65, 'project_id' => 46, 'livrable' => 'Fourniture dispositif d\'accès et incendie', 'description' => 'La fourniture de treize (13) serrures électroniques ; La fourniture de treize (13) kits de détection et d\'extinction automatique ; L\'installation de tous les équipements sur les sites désignés.', 'date_prevue' => '2025-09-30'],
            ['id' => 66, 'project_id' => 46, 'livrable' => 'Fourniture de climatiseurs', 'description' => 'La fourniture de vingt-six (26) climatiseurs ; La fourniture de treize (13) dispositifs d\'inverseur de source pour deux (2) climatiseurs ; L\'installation de tous les équipements sur les sites désignés', 'date_prevue' => '2025-09-30'],
            ['id' => 69, 'project_id' => 48, 'livrable' => 'Planning de déploiement', 'description' => 'A partager en début de mission', 'date_prevue' => '2025-08-06'],
            ['id' => 70, 'project_id' => 48, 'livrable' => 'Rapport de déploiement', 'description' => 'A partager en fin de mission', 'date_prevue' => '2025-10-17'],
            ['id' => 71, 'project_id' => 49, 'livrable' => 'Planning de déploiement', 'description' => 'A partager en début de mission', 'date_prevue' => '2025-08-06'],
            ['id' => 72, 'project_id' => 49, 'livrable' => 'Rapport de déploiement', 'description' => 'A partager en fin de mission', 'date_prevue' => '2025-10-17'],
            ['id' => 73, 'project_id' => 50, 'livrable' => 'Planning de déploiement', 'description' => 'A partager en début de mission (non encore transmis)', 'date_prevue' => '2025-10-08'],
            ['id' => 74, 'project_id' => 50, 'livrable' => 'Rapport de déploiement', 'description' => 'A partager en fin de mission', 'date_prevue' => '2025-11-07'],
            ['id' => 75, 'project_id' => 51, 'livrable' => 'Partage du planning de survey', 'description' => 'Planning validé par le client', 'date_prevue' => '2025-10-03'],
            ['id' => 76, 'project_id' => 51, 'livrable' => 'Rapports de survey', 'description' => 'Un rapport de survey devra être partagé au client pour chaque site', 'date_prevue' => '2025-10-25'],
            ['id' => 77, 'project_id' => 51, 'livrable' => 'Attestation de bonne exécution', 'description' => 'A réclamer au client à la fin de la mission', 'date_prevue' => '2025-10-31'],
            ['id' => 78, 'project_id' => 52, 'livrable' => 'Livraison du matériel', 'description' => 'Livrer le matériel objet de cette DAO au ayant droit', 'date_prevue' => '2025-11-10'],
            ['id' => 79, 'project_id' => 52, 'livrable' => 'Dépôt des BL', 'description' => 'Transmettre au client le BL signé par les bénéficiaires', 'date_prevue' => '2025-11-11'],
            ['id' => 80, 'project_id' => 52, 'livrable' => 'Attestation de bonne exécution', 'description' => 'Signé par le client', 'date_prevue' => '2025-11-13'],
            ['id' => 81, 'project_id' => 52, 'livrable' => 'Plan de suivi des garanties', 'description' => 'SAV et suivi des garanties sur le matériel livré', 'date_prevue' => '2025-11-13'],
            ['id' => 82, 'project_id' => 53, 'livrable' => 'Livraison du matériel', 'description' => 'Livrer le matériel objet de cette DAO', 'date_prevue' => '2025-11-16'],
            ['id' => 83, 'project_id' => 53, 'livrable' => 'Réception du PV de recette', 'description' => 'Réception du PV de recette qui clôture le projet', 'date_prevue' => '2025-11-17'],
            ['id' => 84, 'project_id' => 53, 'livrable' => 'Plan de suivi des garanties', 'description' => 'Suivi de SAV et garanties', 'date_prevue' => '2025-11-18'],
            ['id' => 85, 'project_id' => 54, 'livrable' => 'Note de Cadrage', 'description' => 'Mémoire technique en réponse aux termes de référence', 'date_prevue' => null],
            ['id' => 86, 'project_id' => 54, 'livrable' => 'Planning', 'description' => 'planning de déploiement du projet', 'date_prevue' => null],
            ['id' => 87, 'project_id' => 54, 'livrable' => 'PV de reception ddu Bordereau de livraison', 'description' => 'Livraison des équipements  spécifiés dans les TDRs', 'date_prevue' => null],
            ['id' => 88, 'project_id' => 54, 'livrable' => 'Documents de conception et de migration', 'description' => '', 'date_prevue' => null],
            ['id' => 89, 'project_id' => 54, 'livrable' => 'Documents d\'exploitation', 'description' => '', 'date_prevue' => null],
            ['id' => 90, 'project_id' => 54, 'livrable' => 'PV de clôture', 'description' => '', 'date_prevue' => null],
            ['id' => 91, 'project_id' => 54, 'livrable' => 'Attestation de bonne Exécution signée du  client', 'description' => '', 'date_prevue' => null],
        ];

        // Insert in batches of 10 for performance
        foreach (array_chunk($deliverables, 10) as $batch) {
            DB::table('project_deliverables')->insert($batch);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
