<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding database...');
        $this->command->newLine();

        // Seed reference data in order
        $this->call([
            // 1. Roles & Permissions FIRST (required by user seeders)
            RolesAndPermissionsSeeder::class,

            // 2. Reference data
            FilialeSeeder::class,
            DirectionSeeder::class,
            KpiFamilySeeder::class,
            KpiIndicatorSeeder::class,

            // 3. Users (needs roles to exist)
            AdminUserSeeder::class,

            // 4. Migrate existing users to Spatie (if any)
            MigrateExistingUsersSeeder::class,

            // 5. Projects & relations
            ProjectSeeder::class,
            ProjectActionSeeder::class,
            ProjectDeliverableSeeder::class,
            ProjectIssueSeeder::class,
            ProjectStakeholderSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
    }
}
