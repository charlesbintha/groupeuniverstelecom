<?php

namespace Database\Seeders;

use App\Models\Direction;
use Illuminate\Database\Seeder;

class DirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directions = [
            ['filiale' => 'Univers Academy', 'nom_direction' => 'DIRECTION UA', 'code_direction' => 'DUA'],
            ['filiale' => 'Univers Capital', 'nom_direction' => 'DIRECTION UC', 'code_direction' => 'DUC'],
            ['filiale' => 'Univers Télécom Afrique', 'nom_direction' => 'DIRECTION UTA', 'code_direction' => 'DUTA'],
            ['filiale' => 'Univers Technology & Energies', 'nom_direction' => 'DIRECTION UTE', 'code_direction' => 'DUTE'],
            ['filiale' => 'Cabinet Pencco', 'nom_direction' => 'INNOVATION', 'code_direction' => 'INNOVCP'],
            ['filiale' => 'Cabinet Pencco', 'nom_direction' => 'DIRECTION CP', 'code_direction' => 'DCP'],
            ['filiale' => 'Univers Distribution et Equipement', 'nom_direction' => 'DIRECTION UDE', 'code_direction' => 'DUDE'],
            ['filiale' => 'Groupe Univers Télécom', 'nom_direction' => 'RAF', 'code_direction' => 'DRAF'],
            ['filiale' => 'Groupe Univers Télécom', 'nom_direction' => 'DIQ', 'code_direction' => 'DDIQ'],
            ['filiale' => 'Groupe Univers Télécom', 'nom_direction' => 'PSI', 'code_direction' => 'DPSI'],
        ];

        foreach ($directions as $direction) {
            Direction::firstOrCreate(
                ['code_direction' => $direction['code_direction']],
                $direction
            );
        }

        $this->command->info('✅ Directions seeded successfully (10 directions)');
    }
}
