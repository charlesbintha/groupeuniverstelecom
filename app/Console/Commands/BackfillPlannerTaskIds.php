<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectAction;
use App\Services\External\MicrosoftGraphService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillPlannerTaskIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'planner:backfill-task-ids
                            {--project-id= : Specific project ID to backfill}
                            {--dry-run : Run without saving to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill ms_task_id and ms_task_etag for existing project actions from MS Planner';

    protected MicrosoftGraphService $graphService;

    /**
     * Execute the console command.
     */
    public function handle(MicrosoftGraphService $graphService): int
    {
        $this->graphService = $graphService;
        $isDryRun = $this->option('dry-run');

        $this->info('🔄 Starting MS Planner task ID backfill...');

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be saved to database');
        }

        // Get projects to process
        $projectId = $this->option('project-id');

        if ($projectId) {
            $projects = Project::where('id', $projectId)
                ->whereNotNull('ms_plan_id')
                ->get();
        } else {
            $projects = Project::whereNotNull('ms_plan_id')
                ->whereNotNull('ms_bucket_id')
                ->get();
        }

        if ($projects->isEmpty()) {
            $this->error('❌ No projects with MS Planner synchronization found');
            return Command::FAILURE;
        }

        $this->info("Found {$projects->count()} synchronized projects");
        $this->newLine();

        $stats = [
            'projects_processed' => 0,
            'actions_matched' => 0,
            'actions_updated' => 0,
            'actions_not_found' => 0,
            'errors' => 0,
        ];

        foreach ($projects as $project) {
            $this->info("📦 Project #{$project->id}: {$project->code_projet} - {$project->nom_projet}");
            $this->line("   Plan ID: {$project->ms_plan_id}");

            try {
                $result = $this->backfillProject($project, $isDryRun);

                $stats['projects_processed']++;
                $stats['actions_matched'] += $result['matched'];
                $stats['actions_updated'] += $result['updated'];
                $stats['actions_not_found'] += $result['not_found'];

                $this->line("   ✅ Matched: {$result['matched']}, Updated: {$result['updated']}, Not found: {$result['not_found']}");

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("   ❌ Error: {$e->getMessage()}");
                Log::error('Backfill error for project ' . $project->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $this->newLine();
        }

        // Display summary
        $this->info('📊 Backfill Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Projects processed', $stats['projects_processed']],
                ['Actions matched', $stats['actions_matched']],
                ['Actions updated', $stats['actions_updated']],
                ['Actions not found in Planner', $stats['actions_not_found']],
                ['Errors', $stats['errors']],
            ]
        );

        if ($isDryRun) {
            $this->warn('⚠️  This was a DRY RUN - no changes were saved');
            $this->info('Run without --dry-run to save changes');
        }

        return Command::SUCCESS;
    }

    /**
     * Backfill task IDs for a single project.
     *
     * @param Project $project
     * @param bool $isDryRun
     * @return array
     */
    protected function backfillProject(Project $project, bool $isDryRun): array
    {
        $stats = [
            'matched' => 0,
            'updated' => 0,
            'not_found' => 0,
        ];

        // Get all tasks from this plan
        $plannerTasks = $this->getPlannerTasks($project->ms_plan_id);

        if (empty($plannerTasks)) {
            $this->warn("   ⚠️  No tasks found in Planner for this project");
            return $stats;
        }

        // Get all actions for this project
        $actions = $project->actions()->orderBy('ordre')->get();

        foreach ($actions as $action) {
            // Skip if already has task ID
            if (!empty($action->ms_task_id)) {
                $this->line("   ⏭️  Action #{$action->id} already has task ID, skipping");
                continue;
            }

            // Try to match by title
            $matchedTask = $this->findMatchingTask($action->libelle, $plannerTasks);

            if ($matchedTask) {
                $stats['matched']++;

                $this->line("   🎯 Matched: \"{$action->libelle}\" → Task ID: {$matchedTask['id']}");

                if (!$isDryRun) {
                    ProjectAction::where('id', $action->id)->update([
                        'ms_task_id' => $matchedTask['id'],
                        'ms_task_etag' => $matchedTask['etag'],
                    ]);
                    $stats['updated']++;
                }
            } else {
                $stats['not_found']++;
                $this->line("   ❓ Not found: \"{$action->libelle}\"");
            }
        }

        return $stats;
    }

    /**
     * Get all tasks from a Planner plan.
     *
     * @param string $planId
     * @return array
     */
    protected function getPlannerTasks(string $planId): array
    {
        try {
            $response = $this->callGraphAPI('GET', "/planner/plans/{$planId}/tasks");

            $tasks = [];
            foreach ($response['value'] ?? [] as $task) {
                $tasks[] = [
                    'id' => $task['id'] ?? null,
                    'title' => $task['title'] ?? '',
                    'etag' => $task['@odata.etag'] ?? null,
                    'bucketId' => $task['bucketId'] ?? null,
                    'percentComplete' => $task['percentComplete'] ?? 0,
                ];
            }

            return $tasks;

        } catch (\Exception $e) {
            $this->error("   ❌ Failed to fetch tasks from Planner: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Find matching task by title (case-insensitive, trimmed).
     *
     * @param string $actionLibelle
     * @param array $plannerTasks
     * @return array|null
     */
    protected function findMatchingTask(string $actionLibelle, array $plannerTasks): ?array
    {
        $normalizedLibelle = mb_strtolower(trim($actionLibelle));

        foreach ($plannerTasks as $task) {
            $normalizedTitle = mb_strtolower(trim($task['title']));

            if ($normalizedLibelle === $normalizedTitle) {
                return $task;
            }
        }

        // Try fuzzy match (contains)
        foreach ($plannerTasks as $task) {
            $normalizedTitle = mb_strtolower(trim($task['title']));

            if (str_contains($normalizedTitle, $normalizedLibelle) || str_contains($normalizedLibelle, $normalizedTitle)) {
                return $task;
            }
        }

        return null;
    }

    /**
     * Call MS Graph API.
     *
     * @param string $method
     * @param string $endpoint
     * @return array
     */
    protected function callGraphAPI(string $method, string $endpoint): array
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->graphService);
        $callMethod = $reflection->getMethod('call');
        $callMethod->setAccessible(true);

        return $callMethod->invoke($this->graphService, $method, $endpoint);
    }
}
