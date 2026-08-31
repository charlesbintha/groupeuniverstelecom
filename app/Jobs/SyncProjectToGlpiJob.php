<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\External\GlpiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProjectToGlpiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The project instance.
     *
     * @var \App\Models\Project
     */
    public $project;

    /**
     * Number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(GlpiService $glpiService): void
    {
        Log::info('SyncProjectToGlpiJob::handle started', [
            'project_id' => $this->project->id,
            'maintenance_glpi' => $this->project->maintenance_glpi,
            'glpi_project_id' => $this->project->glpi_project_id,
            'code_projet' => $this->project->code_projet,
        ]);

        // Vérifier si la maintenance GLPI est activée
        if (!$this->project->maintenance_glpi) {
            Log::info('GLPI sync skipped - maintenance not enabled', [
                'project_id' => $this->project->id,
                'project_code' => $this->project->code_projet,
            ]);
            return;
        }

        Log::info('SyncProjectToGlpiJob - Proceeding with sync', [
            'project_id' => $this->project->id,
            'has_glpi_project_id' => !empty($this->project->glpi_project_id),
        ]);

        try {
            // Préparer les données du projet
            $projectData = [
                'nom_projet' => $this->project->nom_projet,
                'code_projet' => $this->project->code_projet,
                'objectif_projet' => $this->project->objectif_projet,
                'date_demarrage' => $this->project->date_demarrage,
                'date_fin' => $this->project->date_fin,
                'budget_initial' => $this->project->budget_initial,
                'type_projet' => $this->project->type_projet?->value,
                'statut_initial' => $this->project->statut_initial?->value,
                'notes' => $this->project->notes,
            ];

            // Si un projet GLPI existe déjà, mettre à jour, sinon créer
            if (!empty($this->project->glpi_project_id)) {
                Log::info('Updating existing GLPI project', [
                    'project_id' => $this->project->id,
                    'glpi_project_id' => $this->project->glpi_project_id,
                ]);

                // Vérifier si le projet existe toujours dans GLPI
                $existingProject = $glpiService->getProject($this->project->glpi_project_id);

                if ($existingProject === null) {
                    // Le projet n'existe plus dans GLPI, recréer
                    Log::warning('GLPI project not found, creating new one', [
                        'project_id' => $this->project->id,
                        'old_glpi_project_id' => $this->project->glpi_project_id,
                    ]);

                    $this->createGlpiProject($glpiService, $projectData);
                } else {
                    // Mettre à jour le projet existant
                    $glpiService->updateProject($this->project->glpi_project_id, $projectData);

                    Log::info('GLPI project updated successfully', [
                        'project_id' => $this->project->id,
                        'glpi_project_id' => $this->project->glpi_project_id,
                    ]);
                }
            } else {
                // Créer un nouveau projet GLPI
                $this->createGlpiProject($glpiService, $projectData);
            }

        } catch (\Exception $e) {
            Log::error('GLPI sync job failed', [
                'project_id' => $this->project->id,
                'project_code' => $this->project->code_projet,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Relancer l'exception pour que le job soit réessayé
            throw $e;
        }
    }

    /**
     * Create a new GLPI project and store the ID.
     *
     * @param GlpiService $glpiService
     * @param array $projectData
     * @return void
     * @throws \Exception
     */
    protected function createGlpiProject(GlpiService $glpiService, array $projectData): void
    {
        Log::info('Creating new GLPI project', [
            'project_id' => $this->project->id,
            'project_code' => $this->project->code_projet,
        ]);

        $result = $glpiService->createProject($projectData);

        // Stocker l'ID du projet GLPI
        if (isset($result['id'])) {
            $this->project->glpi_project_id = (string) $result['id'];
            $this->project->saveQuietly(); // saveQuietly pour éviter de redéclencher l'observer

            Log::info('GLPI project created and ID stored', [
                'project_id' => $this->project->id,
                'glpi_project_id' => $this->project->glpi_project_id,
            ]);
        } else {
            Log::warning('GLPI project created but no ID returned', [
                'project_id' => $this->project->id,
                'result' => $result,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GLPI sync job failed permanently', [
            'project_id' => $this->project->id,
            'project_code' => $this->project->code_projet,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // TODO: Vous pouvez envoyer une notification à l'admin ici si nécessaire
    }
}
