<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $filialeToTest = 'Groupe Univers Télécom';

        $employee = Employee::where('filiale', $filialeToTest)
            ->where('actif', true)
            ->whereNotNull('email')
            ->first();

        if (!$employee) {
            $this->command->warn("Aucun employé actif trouvé pour la filiale: {$filialeToTest}");
            $this->command->warn("Création d'un employé de test...");

            $employee = Employee::create([
                'prenom_nom' => 'Manager Test',
                'email' => 'manager.test@gut.sn',
                'filiale' => $filialeToTest,
                'direction' => 'Direction Générale',
                'actif' => true,
                'poste' => 'Manager Filiale',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'manager@gut.sn'],
            [
                'employe_id' => $employee->id,
                'name' => $employee->prenom_nom,
                'role' => 'manager',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );

        // Assign Spatie role (idempotent - won't duplicate if already assigned)
        if (!$user->hasRole('Manager')) {
            $user->assignRole('Manager');
            $this->command->info('   ✅ Manager role assigned');
        }

        if ($user->wasRecentlyCreated) {
            $this->command->info("✅ Manager créé: {$user->email}");
            $this->command->info("   Filiale: {$employee->filiale}");
            $this->command->info("   Mot de passe: password");
        } else {
            $this->command->info("Manager existe déjà: {$user->email}");
        }
    }
}
