<?php

namespace Database\Seeders;

use App\Models\Filiale;
use Illuminate\Database\Seeder;

class FilialeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filiales = [
            ['nom_filiale' => 'Groupe Univers Télécom', 'code_filiale' => 'GUT'],
            ['nom_filiale' => 'Univers Télécom Afrique', 'code_filiale' => 'UTA'],
            ['nom_filiale' => 'Cabinet Pencco', 'code_filiale' => 'CP'],
            ['nom_filiale' => 'Univers Technology & Energies', 'code_filiale' => 'UTE'],
            ['nom_filiale' => 'Univers Distribution et Equipement', 'code_filiale' => 'UDE'],
            ['nom_filiale' => 'Univers Academy', 'code_filiale' => 'UA'],
        ];

        foreach ($filiales as $filiale) {
            Filiale::firstOrCreate(
                ['code_filiale' => $filiale['code_filiale']],
                $filiale
            );
        }

        $this->command->info('✅ Filiales seeded successfully (6 filiales)');
    }
}
