<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user if doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'cheikh.sene@cp-experts.sn'],
            [
                'name' => 'Administrateur GUT',
                'password' => Hash::make('SCjE-hNV$dEL'),
                'role' => 'admin',
                'is_active' => true,
                'employe_id' => null,
            ]
        );

        // Assign Spatie role (idempotent - won't duplicate if already assigned)
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
            $this->command->info('   ✅ Admin role assigned');
        }

        if ($admin->wasRecentlyCreated) {
            $this->command->info('✅ Admin user created successfully');
            $this->command->info('   Email: cheikh.sene@cp-experts.sn');
            $this->command->info('   Password: SCjE-hNV$dEL');
            $this->command->warn('   ⚠️  IMPORTANT: Change this password after first login!');
        } else {
            $this->command->info('ℹ️  Admin user already exists');
        }
    }
}
