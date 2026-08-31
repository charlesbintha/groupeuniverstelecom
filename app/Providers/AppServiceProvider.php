<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use App\Observers\ProjectObserver;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Schema::defaultStringLength(191);
        Project::observe(ProjectObserver::class);
        

        // Register policies
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Verify critical storage directories are writable (production only)
        if (app()->environment('production')) {
            $this->verifyStoragePermissions();
        }
    }

    /**
     * Verify that critical storage directories are writable.
     * Logs warnings if permissions are incorrect.
     */
    protected function verifyStoragePermissions(): void
    {
        $criticalDirs = [
            storage_path('app') => 'Token cache and file storage',
            storage_path('logs') => 'Application logs',
            storage_path('framework/cache') => 'Framework cache',
            storage_path('framework/sessions') => 'Session storage',
            storage_path('framework/views') => 'Compiled views',
        ];

        foreach ($criticalDirs as $dir => $description) {
            if (!file_exists($dir)) {
                Log::critical("Storage directory missing: {$dir}", [
                    'description' => $description,
                    'path' => $dir,
                    'action' => 'Create directory with: mkdir -p ' . $dir,
                ]);
                continue;
            }

            if (!is_writable($dir)) {
                Log::critical("Storage directory not writable: {$dir}", [
                    'description' => $description,
                    'path' => $dir,
                    'current_permissions' => substr(sprintf('%o', fileperms($dir)), -4),
                    'action' => 'Fix with: chmod -R 775 ' . $dir,
                ]);
            }
        }
    }
}
