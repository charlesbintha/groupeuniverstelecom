<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class MigrateExistingUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🔄 Migrating existing users to Spatie roles...');

        // Migrer depuis la colonne enum 'role'
        $adminCount = 0;
        $managerCount = 0;
        $userCount = 0;

        User::where('role', 'admin')->each(function ($user) use (&$adminCount) {
            if (!$user->hasRole('Admin')) {
                $user->assignRole('Admin');
                $adminCount++;
            }
        });

        User::where('role', 'manager')->each(function ($user) use (&$managerCount) {
            if (!$user->hasRole('Manager')) {
                $user->assignRole('Manager');
                $managerCount++;
            }
        });

        User::where('role', 'user')->each(function ($user) use (&$userCount) {
            if (!$user->hasRole('User')) {
                $user->assignRole('User');
                $userCount++;
            }
        });

        $this->command->info("✅ Migrated {$adminCount} admin(s)");
        $this->command->info("✅ Migrated {$managerCount} manager(s)");
        $this->command->info("✅ Migrated {$userCount} user(s)");
        $this->command->newLine();
        $this->command->info('ℹ️  Note: Old "role" column is preserved for rollback safety');
    }
}
