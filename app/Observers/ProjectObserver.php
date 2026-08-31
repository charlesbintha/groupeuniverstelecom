<?php

namespace App\Observers;

use App\Jobs\SyncProjectToGlpiJob;
use App\Jobs\SyncProjectToPlannerJob;
use App\Models\Filiale;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     * code_projet is nullable, so no need to set temporary value.
     * It will be generated in 'created' event with the actual ID.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function creating(Project $project): void
    {
        // code_projet is nullable - will be set in 'created' event
        // Nothing to do here
    }

    /**
     * Generate project code with actual ID.
     * Format: {CODE_DIRECTION}-{CODE_FILIALE}-{ID}
     * Example: INNOV-GUT-0045
     *
     * Uses fallbacks if codes cannot be resolved:
     * - code_direction not found → 'DIR'
     * - code_filiale not found → 'GEN'
     *
     * @param  \App\Models\Project  $project
     * @return string
     */
    protected function generateProjectCodeWithId(Project $project): string
    {
        // Get direction code from directions table (with fallback)
        $codeDirection = $this->getDirectionCode($project->filiale_executant, $project->direction_executant);

        // Get filiale code from filiales table (with fallback)
        $codeFiliale = $this->getFilialeCode($project->filiale_executant);

        // Pad project ID to 4 digits
        $seqPad = str_pad((string)$project->id, 4, '0', STR_PAD_LEFT);

        return "{$codeDirection}-{$codeFiliale}-{$seqPad}";
    }

    /**
     * Get direction code from directions table.
     * Matches the logic in save_project.php lines 45-64
     * Returns 'DIR' fallback if code cannot be resolved.
     *
     * Uses case-insensitive and trimmed comparison for robustness.
     *
     * @param  string|null  $filialeName
     * @param  string|null  $directionName
     * @return string
     */
    protected function getDirectionCode(?string $filialeName, ?string $directionName): string
    {
        if (empty($directionName)) {
            Log::warning('Direction name is empty, using fallback', [
                'fallback' => 'DIR',
            ]);
            return 'DIR';
        }

        // Normalize search value
        $directionNameNormalized = trim($directionName);

        // First, get filiale ID using normalized search
        $filialeId = null;
        if (!empty($filialeName)) {
            $filialeNameNormalized = trim($filialeName);
            $filiale = Filiale::whereRaw('UPPER(TRIM(nom_filiale)) = ?', [strtoupper($filialeNameNormalized)])
                ->first();
            $filialeId = $filiale?->id;
        }

        // Try to find direction with filiale constraint (case-insensitive)
        if ($filialeId) {
            $direction = DB::table('directions')
                ->whereRaw('UPPER(TRIM(nom_direction)) = ?', [strtoupper($directionNameNormalized)])
                ->where(function ($query) use ($filialeId, $filialeName) {
                    $query->where('filiale', $filialeId)
                          ->orWhereRaw('UPPER(TRIM(filiale)) = ?', [strtoupper(trim($filialeName ?? ''))]);
                })
                ->first();

            if ($direction && !empty($direction->code_direction)) {
                return trim($direction->code_direction);
            }
        }

        // Fallback: find direction by name only (case-insensitive)
        $direction = DB::table('directions')
            ->whereRaw('UPPER(TRIM(nom_direction)) = ?', [strtoupper($directionNameNormalized)])
            ->first();

        if ($direction && !empty($direction->code_direction)) {
            return trim($direction->code_direction);
        }

        // Ultimate fallback
        Log::warning('Direction code not found, using fallback', [
            'direction_name' => $directionName,
            'direction_name_normalized' => $directionNameNormalized,
            'filiale_name' => $filialeName,
            'fallback' => 'DIR',
        ]);

        return 'DIR';
    }

    /**
     * Get filiale code from filiales table.
     * Returns 'GEN' fallback if code cannot be resolved.
     *
     * Uses case-insensitive and trimmed comparison for robustness.
     *
     * @param  string|null  $filialeName
     * @return string
     */
    protected function getFilialeCode(?string $filialeName): string
    {
        if (empty($filialeName)) {
            Log::warning('Filiale name is empty, using fallback', [
                'fallback' => 'GEN',
            ]);
            return 'GEN';
        }

        // Normalize search value
        $filialeNameNormalized = trim($filialeName);

        // Search with case-insensitive comparison
        $filiale = Filiale::whereRaw('UPPER(TRIM(nom_filiale)) = ?', [strtoupper($filialeNameNormalized)])
            ->first();

        if ($filiale && !empty($filiale->code_filiale)) {
            return trim($filiale->code_filiale);
        }

        // Fallback
        Log::warning('Filiale code not found, using fallback', [
            'filiale_name' => $filialeName,
            'filiale_name_normalized' => $filialeNameNormalized,
            'fallback' => 'GEN',
        ]);

        return 'GEN';
    }

    /**
     * Dispatch Planner sync job intelligently based on queue configuration.
     * Defers sync execution until after the HTTP response when no worker is available.
     *
     * @param  int  $projectId
     * @param  string  $mode
     * @return void
     */
    protected function dispatchPlannerSync(int $projectId, string $mode): void
    {
        $queueConnection = config('queue.default');

        if ($queueConnection === 'sync') {
            SyncProjectToPlannerJob::dispatchAfterResponse($projectId, $mode);

            Log::info('MS Planner sync deferred until after response (sync mode)', [
                'project_id' => $projectId,
                'mode' => $mode,
            ]);
        } else {
            // Dispatch to queue (for cPanel with cron or local dev)
            SyncProjectToPlannerJob::dispatch($projectId, $mode);

            Log::info('MS Planner sync job dispatched (queue mode)', [
                'project_id' => $projectId,
                'mode' => $mode,
                'queue_connection' => $queueConnection,
            ]);
        }
    }

    /**
     * Dispatch GLPI sync job intelligently based on queue configuration.
     * Uses sync execution if queue is 'sync', otherwise dispatches to queue.
     *
     * @param  Project  $project
     * @return void
     */
    protected function dispatchGlpiSync(Project $project): void
    {
        $queueConnection = config('queue.default');

        Log::info('ProjectObserver::dispatchGlpiSync called', [
            'project_id' => $project->id,
            'queue_connection' => $queueConnection,
            'maintenance_glpi' => $project->maintenance_glpi,
            'glpi_project_id' => $project->glpi_project_id,
        ]);

        if ($queueConnection === 'sync') {
            // Execute immediately in sync mode (for cPanel without cron)
            try {
                Log::info('GLPI sync executing synchronously (sync mode)', [
                    'project_id' => $project->id,
                ]);

                $glpiService = app(\App\Services\External\GlpiService::class);
                $job = new SyncProjectToGlpiJob($project);
                $job->handle($glpiService);

                Log::info('GLPI sync completed synchronously', [
                    'project_id' => $project->id,
                ]);
            } catch (\Exception $e) {
                Log::error('GLPI sync failed (sync mode)', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            // Dispatch to queue (for cPanel with cron or local dev)
            try {
                SyncProjectToGlpiJob::dispatch($project);

                Log::info('GLPI sync job dispatched (queue mode)', [
                    'project_id' => $project->id,
                    'queue_connection' => $queueConnection,
                ]);
            } catch (\Exception $e) {
                Log::error('GLPI sync job dispatch failed', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Handle the Project "created" event.
     * 1. Generate final code_projet with actual ID (same format as save_project.php)
     * 2. Add automatic tasks if filiale_executant ≠ filiale_contractant
     * 3. Auto-sync to Microsoft Planner AFTER transaction commits
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function created(Project $project): void
    {
        // Step 1: Add automatic tasks if different filiales (happens immediately)
        if ($project->hasDifferentFiliales()) {
            $this->addAutomaticTasks($project);
        }

        // Step 2: Generate code_projet AND dispatch Planner sync AFTER transaction commits
        // Single afterCommit ensures proper ordering: code generation → Planner dispatch
        DB::afterCommit(function () use ($project) {
            // Part A: Generate code_projet with actual ID
            $finalCode = null;
            if (empty($project->code_projet)) {
                try {
                    // Generate code with fallbacks (DIR, GEN) if codes not found
                    $finalCode = $this->generateProjectCodeWithId($project);

                    // Update using query builder to avoid triggering observers
                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['code_projet' => $finalCode]);

                    Log::info('Project code generated with ID', [
                        'project_id' => $project->id,
                        'code_projet' => $finalCode,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update project code', [
                        'project_id' => $project->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // code_projet remains NULL if generation fails
                }
            }

            // Part B: Dispatch GLPI sync job if maintenance is enabled (independent of MS Planner)
            // Refresh project to get the updated code_projet
            $project->refresh();
            $this->dispatchGlpiSyncIfNeeded($project);

            // Part C: Dispatch Planner sync job (AFTER code is generated)
            // Check if auto-sync is enabled
            if (!config('microsoft-graph.auto_sync', false)) {
                return;
            }

            // Check if credentials are configured
            if (empty(config('microsoft-graph.client_id')) || empty(config('microsoft-graph.client_secret'))) {
                Log::info('MS Planner auto-sync skipped: credentials not configured', [
                    'project_id' => $project->id,
                    'code_projet' => $finalCode,
                ]);
                return;
            }

            // Dispatch job with code_projet already generated (intelligent dispatch)
            $this->dispatchPlannerSync($project->id, 'create');
        });
    }

    /**
     * Dispatch GLPI sync job if maintenance is enabled and auto-sync is configured.
     *
     * @param  Project  $project
     * @return void
     */
    protected function dispatchGlpiSyncIfNeeded(Project $project): void
    {
        Log::info('ProjectObserver::dispatchGlpiSyncIfNeeded called', [
            'project_id' => $project->id,
            'maintenance_glpi' => $project->maintenance_glpi,
            'code_projet' => $project->code_projet,
        ]);

        // Check if maintenance GLPI is enabled for this project
        if (!$project->maintenance_glpi) {
            Log::info('GLPI sync skipped: maintenance_glpi is false', [
                'project_id' => $project->id,
            ]);
            return;
        }

        // Check if auto-sync is enabled
        $autoSync = config('glpi.auto_sync', true);
        Log::info('GLPI auto-sync config', [
            'auto_sync' => $autoSync,
            'project_id' => $project->id,
        ]);

        if (!$autoSync) {
            Log::info('GLPI auto-sync disabled', [
                'project_id' => $project->id,
            ]);
            return;
        }

        // Check if credentials are configured (username/password OR user_token)
        $username = config('glpi.username');
        $password = config('glpi.password');
        $appToken = config('glpi.app_token');

        Log::info('GLPI credentials check', [
            'has_username' => !empty($username),
            'has_password' => !empty($password),
            'has_app_token' => !empty($appToken),
            'project_id' => $project->id,
        ]);

        if (empty($username) || empty($password) || empty($appToken)) {
            Log::warning('GLPI auto-sync skipped: credentials not configured', [
                'project_id' => $project->id,
                'has_username' => !empty($username),
                'has_password' => !empty($password),
                'has_app_token' => !empty($appToken),
            ]);
            return;
        }

        // Dispatch GLPI sync job
        Log::info('GLPI sync job will be dispatched', [
            'project_id' => $project->id,
        ]);
        $this->dispatchGlpiSync($project);
    }

    /**
     * Add automatic tasks when filiale_executant ≠ filiale_contractant.
     *
     * Tasks added:
     * 1. Formalisation
     * 2. Définition de la répartition des revenus
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function addAutomaticTasks(Project $project): void
    {
        // Get current max ordre
        $maxOrdre = $project->actions()->max('ordre') ?? 0;

        // Task 1: Formalisation
        $project->actions()->create([
            'libelle' => 'Formalisation',
            'ordre' => $maxOrdre + 1,
        ]);

        // Task 2: Définition de la répartition des revenus
        $project->actions()->create([
            'libelle' => 'Définition de la répartition des revenus',
            'ordre' => $maxOrdre + 2,
        ]);

        Log::info('Automatic tasks added (different filiales)', [
            'project_id' => $project->id,
            'filiale_executant' => $project->filiale_executant,
            'filiale_contractant' => $project->filiale_contractant,
            'tasks_added' => 2,
        ]);
    }

    /**
     * Handle the Project "updating" event.
     * Prevent code_projet from being modified.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function updating(Project $project): void
    {
        // Prevent modification of code_projet after creation
        if ($project->isDirty('code_projet') && $project->getOriginal('code_projet')) {
            $project->code_projet = $project->getOriginal('code_projet');
        }
    }

    /**
     * Handle the Project "updated" event.
     * Auto-sync to Microsoft Planner if project was already synchronized.
     *
     * UPDATE mode behavior:
     * - ✅ Add NEW stakeholders to M365 group
     * - ✅ Create NEW tasks for actions without ms_task_id
     * - ✅ Update EXISTING tasks for actions with ms_task_id (title, due date, assignments)
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function updated(Project $project): void
    {
        // ANTI-RECURSION PROTECTION
        // Ignore updates that ONLY change code_projet (automatic generation from created())
        // This prevents double triggering when DB::table()->update() is called in created()
        $changedFields = array_keys($project->getChanges());

        // Remove 'updated_at' from comparison (always changes)
        $changedFieldsFiltered = array_diff($changedFields, ['updated_at']);

        // If ONLY code_projet changed, it's the automatic generation - skip sync
        if ($changedFieldsFiltered === ['code_projet'] ||
            (count($changedFieldsFiltered) === 1 && in_array('code_projet', $changedFieldsFiltered))) {

            Log::info('ProjectObserver::updated() - skipping auto-sync: only code_projet was updated (automatic generation)', [
                'project_id' => $project->id,
                'changed_fields' => $changedFields,
            ]);
            return;
        }

        Log::info('ProjectObserver::updated() triggered', [
            'project_id' => $project->id,
            'has_ms_plan_id' => !empty($project->ms_plan_id),
            'changed_fields' => $changedFields,
        ]);

        // Check if auto-sync is enabled
        if (!config('microsoft-graph.auto_sync', false)) {
            Log::info('ProjectObserver::updated() - auto-sync disabled', [
                'project_id' => $project->id,
            ]);
            return;
        }

        // Only sync if project was already synchronized (has ms_plan_id)
        if (empty($project->ms_plan_id)) {
            Log::info('ProjectObserver::updated() - no ms_plan_id, skipping sync', [
                'project_id' => $project->id,
            ]);
            return;
        }

        // Check if credentials are configured
        if (empty(config('microsoft-graph.client_id')) || empty(config('microsoft-graph.client_secret'))) {
            Log::info('ProjectObserver::updated() - credentials not configured', [
                'project_id' => $project->id,
            ]);
            return;
        }

        Log::info('ProjectObserver::updated() - dispatching sync job', [
            'project_id' => $project->id,
            'mode' => 'update',
        ]);

        // Dispatch async job AFTER the transaction commits
        // This ensures all related data (stakeholders) are updated
        // Job handles retries automatically (intelligent dispatch)
        DB::afterCommit(function () use ($project) {
            $this->dispatchPlannerSync($project->id, 'update');

            // Also dispatch GLPI sync if maintenance is enabled
            $this->dispatchGlpiSyncIfNeeded($project);
        });
    }
}
