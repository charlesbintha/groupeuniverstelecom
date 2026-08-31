<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\External\MicrosoftGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProjectToPlannerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The project ID to sync.
     *
     * @var int
     */
    protected int $projectId;

    /**
     * The sync mode (create or update).
     *
     * @var string
     */
    protected string $mode;

    /**
     * Create a new job instance.
     *
     * @param  int  $projectId
     * @param  string  $mode  'create' or 'update'
     * @return void
     */
    public function __construct(int $projectId, string $mode = 'create')
    {
        $this->projectId = $projectId;
        $this->mode = $mode;
    }

    /**
     * Execute the job.
     *
     * @param  MicrosoftGraphService  $graphService
     * @return void
     */
    public function handle(MicrosoftGraphService $graphService): void
    {
        // Load project with all relations
        $project = Project::with(['actions', 'stakeholders', 'deliverables'])
            ->find($this->projectId);

        if (!$project) {
            Log::error('MS Planner sync job failed: project not found', [
                'project_id' => $this->projectId,
                'mode' => $this->mode,
            ]);
            return;
        }

        if ($this->mode === 'create') {
            $this->handleCreateSync($project, $graphService);
        } else {
            $this->handleUpdateSync($project, $graphService);
        }
    }

    /**
     * Handle CREATE mode sync (new project).
     *
     * @param  Project  $project
     * @param  MicrosoftGraphService  $graphService
     * @return void
     */
    protected function handleCreateSync(Project $project, MicrosoftGraphService $graphService): void
    {
        Log::info('MS Planner auto-sync job starting (CREATE mode)', [
            'project_id' => $project->id,
            'code_projet' => $project->code_projet,
            'attempt' => $this->attempts(),
        ]);

        $result = $graphService->syncProjectToPlanner($project);

        if ($result['success']) {
            Log::info('MS Planner auto-sync job successful (CREATE mode)', [
                'project_id' => $project->id,
                'code_projet' => $project->code_projet,
                'ms_group_id' => $result['ms_group_id'] ?? null,
                'ms_plan_id' => $result['ms_plan_id'] ?? null,
                'tasks_created' => $result['tasks_created'] ?? 0,
                'members_added' => $result['added_members'] ?? 0,
                'owners_added' => $result['added_owners'] ?? 0,
                'aad_ids_updated' => $result['updated_aad'] ?? 0,
                'tasks_assigned_to_chef' => $result['tasks_assigned_to_chef'] ?? false,
            ]);

            if (!empty($result['errors'])) {
                Log::warning('MS Planner sync job completed with warnings', [
                    'project_id' => $project->id,
                    'warnings' => $result['errors'],
                ]);
            }
        } else {
            $errorMessage = $result['error'] ?? 'Unknown error';

            Log::error('MS Planner auto-sync job failed (CREATE mode)', [
                'project_id' => $project->id,
                'code_projet' => $project->code_projet,
                'error' => $errorMessage,
                'attempt' => $this->attempts(),
                'will_retry' => $this->attempts() < $this->tries,
            ]);

            // Throw exception to trigger retry
            if ($this->attempts() < $this->tries) {
                throw new \RuntimeException("MS Planner sync failed: {$errorMessage}");
            }
        }
    }

    /**
     * Handle UPDATE mode sync (existing project - sync members and tasks).
     *
     * @param  Project  $project
     * @param  MicrosoftGraphService  $graphService
     * @return void
     */
    protected function handleUpdateSync(Project $project, MicrosoftGraphService $graphService): void
    {
        Log::info('MS Planner auto-sync job starting (UPDATE mode)', [
            'project_id' => $project->id,
            'code_projet' => $project->code_projet,
            'ms_plan_id' => $project->ms_plan_id,
            'attempt' => $this->attempts(),
            'note' => 'Will sync members and tasks (create new + update existing)',
        ]);

        $result = $graphService->syncProjectToPlanner($project);

        if ($result['success']) {
            Log::info('MS Planner auto-sync job successful (UPDATE mode)', [
                'project_id' => $project->id,
                'code_projet' => $project->code_projet,
                'new_members_added' => $result['added_members'] ?? 0,
                'new_owners_added' => $result['added_owners'] ?? 0,
                'aad_ids_updated' => $result['updated_aad'] ?? 0,
                'tasks_created' => $result['tasks_created'] ?? 0,
                'tasks_updated' => $result['tasks_updated'] ?? 0,
            ]);

            if (!empty($result['errors'])) {
                Log::warning('MS Planner sync job UPDATE completed with warnings', [
                    'project_id' => $project->id,
                    'warnings' => $result['errors'],
                ]);
            }
        } else {
            $errorMessage = $result['error'] ?? 'Unknown error';

            Log::error('MS Planner auto-sync job failed (UPDATE mode)', [
                'project_id' => $project->id,
                'code_projet' => $project->code_projet,
                'error' => $errorMessage,
                'attempt' => $this->attempts(),
                'will_retry' => $this->attempts() < $this->tries,
            ]);

            // Throw exception to trigger retry
            if ($this->attempts() < $this->tries) {
                throw new \RuntimeException("MS Planner sync failed: {$errorMessage}");
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('MS Planner sync job permanently failed after all retries', [
            'project_id' => $this->projectId,
            'mode' => $this->mode,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'attempts' => $this->attempts(),
        ]);
    }
}
