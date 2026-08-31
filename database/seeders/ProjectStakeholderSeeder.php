<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectStakeholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds project stakeholders with specific IDs from production database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate table
        DB::table('project_stakeholders')->truncate();

        // Insert data with specific IDs from production
        $stakeholders = [
            ['id' => 66, 'project_id' => 43, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL — awagueye.sall@universtelecom.net', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 67, 'project_id' => 43, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall — asta.fall@uta.sn', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 68, 'project_id' => 43, 'role' => '', 'prenom_nom' => '— Sélectionner un employé —', 'attentes' => '', 'employe_id' => null, 'email' => null, 'aad_id' => null],
            ['id' => 69, 'project_id' => 44, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL — awagueye.sall@universtelecom.net', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => null],
            ['id' => 70, 'project_id' => 44, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall — asta.fall@uta.sn', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => null],
            ['id' => 71, 'project_id' => 44, 'role' => '', 'prenom_nom' => '— Sélectionner un employé —', 'attentes' => '', 'employe_id' => null, 'email' => null, 'aad_id' => null],
            ['id' => 72, 'project_id' => 45, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL — awagueye.sall@universtelecom.net', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 73, 'project_id' => 45, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall — asta.fall@uta.sn', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 74, 'project_id' => 45, 'role' => 'Suivi Projet', 'prenom_nom' => 'Mame Marème BÂ — mareme.ba@universtelecom.net', 'attentes' => 'Information', 'employe_id' => 68, 'email' => 'mareme.ba@universtelecom.net', 'aad_id' => 2147483647],
            ['id' => 75, 'project_id' => 45, 'role' => 'Finances', 'prenom_nom' => 'Papa Mamadou THIAM — papamamadou.thiam@universtelecom.net', 'attentes' => 'Mise à disposition des ressources financières', 'employe_id' => 31, 'email' => 'papamamadou.thiam@universtelecom.net', 'aad_id' => 0],
            ['id' => 76, 'project_id' => 45, 'role' => 'Développeur', 'prenom_nom' => 'Louis Charles Rémi Bintha — louis.bintha@cp-experts.sn', 'attentes' => '', 'employe_id' => 27, 'email' => 'louis.bintha@cp-experts.sn', 'aad_id' => 98],
            ['id' => 77, 'project_id' => 45, 'role' => 'Développeur', 'prenom_nom' => 'Cheikh Ahmadou Bamba Sene — cheikh.sene@cp-experts.sn', 'attentes' => '', 'employe_id' => 71, 'email' => 'cheikh.sene@cp-experts.sn', 'aad_id' => 552],
            ['id' => 78, 'project_id' => 45, 'role' => 'Exploitation', 'prenom_nom' => 'Oumar Sow — oumar.sow@universtelecom.net', 'attentes' => '', 'employe_id' => 35, 'email' => 'oumar.sow@universtelecom.net', 'aad_id' => 8],
            ['id' => 79, 'project_id' => 46, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL — awagueye.sall@universtelecom.net', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 80, 'project_id' => 46, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall — asta.fall@uta.sn', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 81, 'project_id' => 46, 'role' => 'Chef de projet', 'prenom_nom' => 'NDEYE AWA SYLLA — awa.sylla@universtelecom.net', 'attentes' => 'Coordination du projet', 'employe_id' => 52, 'email' => 'awa.sylla@universtelecom.net', 'aad_id' => 932],
            ['id' => 82, 'project_id' => 46, 'role' => 'Account manager', 'prenom_nom' => 'ndeye ndack mar — ndeye.ndack@universtelecom.net', 'attentes' => 'Suivi client', 'employe_id' => 56, 'email' => 'ndeye.ndack@universtelecom.net', 'aad_id' => 2],
            ['id' => 83, 'project_id' => 46, 'role' => 'Equipe support', 'prenom_nom' => 'Diongue Papa — papa.diongue@cp-experts.sn', 'attentes' => 'Ingénieur déploiement', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => 2147483647],
            ['id' => 84, 'project_id' => 46, 'role' => 'Equipe support', 'prenom_nom' => 'Aly khassim diop — khassim.diop@cp-experts.sn', 'attentes' => 'Technicien déploiement', 'employe_id' => 61, 'email' => 'khassim.diop@cp-experts.sn', 'aad_id' => 6],
            ['id' => 85, 'project_id' => 46, 'role' => 'Equipe support', 'prenom_nom' => 'Robert Michel Kirine DIOUF — robert.diouf@cp-experts.sn', 'attentes' => 'Technicien déploiement', 'employe_id' => 69, 'email' => 'robert.diouf@cp-experts.sn', 'aad_id' => 226],
            ['id' => 89, 'project_id' => 48, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 90, 'project_id' => 48, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 91, 'project_id' => 48, 'role' => 'Chef de projet', 'prenom_nom' => 'NDEYE AWA SYLLA', 'attentes' => 'Coordination et pilotage du projet', 'employe_id' => 52, 'email' => 'awa.sylla@universtelecom.net', 'aad_id' => 932],
            ['id' => 92, 'project_id' => 48, 'role' => 'Account Manager', 'prenom_nom' => 'ndeye ndack mar', 'attentes' => 'Suivi Client', 'employe_id' => 56, 'email' => 'ndeye.ndack@universtelecom.net', 'aad_id' => 2],
            ['id' => 93, 'project_id' => 48, 'role' => 'Support', 'prenom_nom' => 'Aly khassim diop', 'attentes' => 'Technicien Déploiement', 'employe_id' => 61, 'email' => 'khassim.diop@cp-experts.sn', 'aad_id' => 6],
            ['id' => 94, 'project_id' => 48, 'role' => 'Support', 'prenom_nom' => 'Diongue Papa', 'attentes' => 'Ingénieur Déploiement', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => 2147483647],
            ['id' => 95, 'project_id' => 49, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 96, 'project_id' => 49, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 97, 'project_id' => 49, 'role' => 'Chef de projet', 'prenom_nom' => 'NDEYE AWA SYLLA', 'attentes' => 'Coordination et pilotage du projet', 'employe_id' => 52, 'email' => 'awa.sylla@universtelecom.net', 'aad_id' => 932],
            ['id' => 98, 'project_id' => 49, 'role' => 'Account Manager', 'prenom_nom' => 'ndeye ndack mar', 'attentes' => 'Suivi Client', 'employe_id' => 56, 'email' => 'ndeye.ndack@universtelecom.net', 'aad_id' => 2],
            ['id' => 99, 'project_id' => 49, 'role' => 'Support', 'prenom_nom' => 'Aly khassim diop', 'attentes' => 'Technicien Déploiement', 'employe_id' => 61, 'email' => 'khassim.diop@cp-experts.sn', 'aad_id' => 6],
            ['id' => 100, 'project_id' => 49, 'role' => 'Support', 'prenom_nom' => 'Diongue Papa', 'attentes' => 'Ingénieur Déploiement', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => 2147483647],
            ['id' => 101, 'project_id' => 50, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL — awagueye.sall@universtelecom.net', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 102, 'project_id' => 50, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall — asta.fall@uta.sn', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 103, 'project_id' => 50, 'role' => 'Chef de projet', 'prenom_nom' => 'NDEYE AWA SYLLA — awa.sylla@universtelecom.net', 'attentes' => 'Coordination du projet - pilote équipe technique', 'employe_id' => 52, 'email' => 'awa.sylla@universtelecom.net', 'aad_id' => null],
            ['id' => 104, 'project_id' => 50, 'role' => 'Account Manager', 'prenom_nom' => 'ndeye ndack mar — ndeye.ndack@universtelecom.net', 'attentes' => 'Suivi Client', 'employe_id' => 56, 'email' => 'ndeye.ndack@universtelecom.net', 'aad_id' => null],
            ['id' => 105, 'project_id' => 50, 'role' => 'Support', 'prenom_nom' => 'Aly khassim diop — khassim.diop@cp-experts.sn', 'attentes' => 'Technicien de déploiement', 'employe_id' => 61, 'email' => 'khassim.diop@cp-experts.sn', 'aad_id' => null],
            ['id' => 106, 'project_id' => 50, 'role' => 'Support', 'prenom_nom' => 'Diongue Papa — papa.diongue@cp-experts.sn', 'attentes' => 'Ingénieur Déploiement', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => null],
            ['id' => 107, 'project_id' => 51, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 108, 'project_id' => 51, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 109, 'project_id' => 51, 'role' => 'Chef de projet', 'prenom_nom' => '— Sélectionner un employé —', 'attentes' => 'Coordination et Pilotage du projet', 'employe_id' => null, 'email' => null, 'aad_id' => null],
            ['id' => 110, 'project_id' => 51, 'role' => 'Account Manager', 'prenom_nom' => '— Sélectionner un employé —', 'attentes' => 'Gestion relation client', 'employe_id' => null, 'email' => null, 'aad_id' => null],
            ['id' => 111, 'project_id' => 51, 'role' => 'Support', 'prenom_nom' => 'Diongue Papa', 'attentes' => 'Ingénieur Déploiement', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => 2147483647],
            ['id' => 112, 'project_id' => 51, 'role' => 'Support', 'prenom_nom' => '— Sélectionner un employé —', 'attentes' => 'Assane Diop / Ingénieur Système', 'employe_id' => null, 'email' => null, 'aad_id' => null],
            ['id' => 113, 'project_id' => 51, 'role' => 'Support', 'prenom_nom' => 'NABNEDA SAMIR COMPAORE', 'attentes' => 'Ingénieur Déploiement', 'employe_id' => 74, 'email' => 'samir.compaore@ute.sn', 'aad_id' => 7],
            ['id' => 114, 'project_id' => 52, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 115, 'project_id' => 52, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 116, 'project_id' => 52, 'role' => 'Responsable des achats', 'prenom_nom' => 'Jean Pierre Iba Mar MOISE', 'attentes' => 'Achat matériel', 'employe_id' => 26, 'email' => 'jeanpierre.moise@universtelecom.net', 'aad_id' => 91],
            ['id' => 117, 'project_id' => 52, 'role' => 'Chef de projet', 'prenom_nom' => 'Abdourahmane SOW', 'attentes' => 'Suivi du projet', 'employe_id' => 59, 'email' => 'abdourahmane.sow@ute.sn', 'aad_id' => 7468],
            ['id' => 118, 'project_id' => 53, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 119, 'project_id' => 53, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 120, 'project_id' => 53, 'role' => 'Responsable des achats', 'prenom_nom' => 'Jean Pierre Iba Mar MOISE', 'attentes' => 'Achat matériel', 'employe_id' => 26, 'email' => 'jeanpierre.moise@universtelecom.net', 'aad_id' => 91],
            ['id' => 121, 'project_id' => 53, 'role' => 'Chef de projet', 'prenom_nom' => 'Abdourahmane SOW', 'attentes' => 'Suivi du déroulement du projet', 'employe_id' => 59, 'email' => 'abdourahmane.sow@ute.sn', 'aad_id' => 7468],
            ['id' => 122, 'project_id' => 54, 'role' => 'Responsable qualité', 'prenom_nom' => 'Awa Gueye SALL', 'attentes' => '', 'employe_id' => 73, 'email' => 'awagueye.sall@universtelecom.net', 'aad_id' => 93],
            ['id' => 123, 'project_id' => 54, 'role' => 'Responsable Marketing', 'prenom_nom' => 'Sokhna Asta Walo Fall', 'attentes' => '', 'employe_id' => 34, 'email' => 'asta.fall@uta.sn', 'aad_id' => 0],
            ['id' => 124, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'ndeye ndack mar', 'attentes' => 'Account Manager / Renseignement sales Force/ Etablissement des DA', 'employe_id' => 56, 'email' => 'ndeye.ndack@universtelecom.net', 'aad_id' => 2],
            ['id' => 125, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Maguette Fancouna Sene', 'attentes' => 'Accompagnement sur l\'administratif', 'employe_id' => 55, 'email' => 'maguette.sene@universtelecom.net', 'aad_id' => 2],
            ['id' => 126, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Marie louise Bienvenue Mendy', 'attentes' => 'Passage des commande et gestion logistiques', 'employe_id' => 45, 'email' => 'marie.louise@universtelecom.net', 'aad_id' => 0],
            ['id' => 127, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'NDEYE AWA SYLLA', 'attentes' => 'Chef de projet', 'employe_id' => 52, 'email' => 'awa.sylla@universtelecom.net', 'aad_id' => 932],
            ['id' => 128, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Abdourahmane SOW', 'attentes' => 'Affection des ressources techniques', 'employe_id' => 59, 'email' => 'abdourahmane.sow@ute.sn', 'aad_id' => 7468],
            ['id' => 129, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Diongue Papa', 'attentes' => 'Ingénieur delivery', 'employe_id' => 36, 'email' => 'papa.diongue@cp-experts.sn', 'aad_id' => 2147483647],
            ['id' => 130, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Aly khassim diop', 'attentes' => 'Ingénieur delivery', 'employe_id' => 61, 'email' => 'khassim.diop@cp-experts.sn', 'aad_id' => 6],
            ['id' => 131, 'project_id' => 54, 'role' => '', 'prenom_nom' => 'Robert Michel Kirine DIOUF', 'attentes' => 'Ingénieur delivery', 'employe_id' => 69, 'email' => 'robert.diouf@cp-experts.sn', 'aad_id' => 226],
        ];

        // Insert in batches of 20 for performance
        foreach (array_chunk($stakeholders, 20) as $batch) {
            DB::table('project_stakeholders')->insert($batch);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


    }
}
