<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== CRÉER PERMISSIONS ==========
        $permissions = [
            // Projets - View permissions
            'projects.view-any',        // Admin/Project Admin: voir tous les projets
            'projects.view-own',        // User: voir ses propres projets
            'projects.view-filiale',    // Manager: voir projets de sa filiale

            // Projets - Manage permissions
            'projects.create',
            'projects.update',          // Admin/Project Admin: éditer n'importe quel projet
            'projects.update-own',      // User/Manager: éditer ses propres projets
            'projects.delete',
            'projects.duplicate',

            // Projets - External sync
            'projects.sync-planner',

            // Utilisateurs
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Documents
            'documents.view',
            'documents.download',

            // Services externes
            'salesforce.search',
            'planner.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Update cache after permission creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== CRÉER RÔLES ==========
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $projectAdmin = Role::create(['name' => 'Project Admin', 'guard_name' => 'web']);
        $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $user = Role::create(['name' => 'User', 'guard_name' => 'web']);

        // ========== ASSIGNER PERMISSIONS AUX RÔLES ==========

        // Admin : TOUTES les permissions
        $admin->givePermissionTo(Permission::all());

        // Project Admin : Tout SAUF users.*
        $projectAdmin->givePermissionTo([
            'projects.view-any',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.duplicate',
            'projects.sync-planner',
            'documents.view',
            'documents.download',
            'salesforce.search',
            'planner.manage',
        ]);

        // Manager : Projets filiale + création + édition own
        $manager->givePermissionTo([
            'projects.view-filiale',
            'projects.view-own',
            'projects.create',
            'projects.update-own',
            'projects.duplicate',
            'projects.sync-planner',
            'documents.view',
            'documents.download',
            'salesforce.search',
            'planner.manage',
        ]);

        // User : Own projects seulement
        $user->givePermissionTo([
            'projects.view-own',
            'projects.create',
            'projects.update-own',
            'documents.view',
            'documents.download',
            'salesforce.search',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('   - Admin: ' . $admin->permissions->count() . ' permissions');
        $this->command->info('   - Project Admin: ' . $projectAdmin->permissions->count() . ' permissions');
        $this->command->info('   - Manager: ' . $manager->permissions->count() . ' permissions');
        $this->command->info('   - User: ' . $user->permissions->count() . ' permissions');
    }
}
