<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Super Admin bypass - Admin role bypasses all checks
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('Admin') ? true : null;
    }

    /**
     * Determine if user can view any projects (for index listing)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view-any')
            || $user->can('projects.view-filiale')
            || $user->can('projects.view-own');
    }

    /**
     * Determine if user can view a specific project
     * Préserve la logique filiale-based pour managers
     */
    public function view(User $user, Project $project): bool
    {
        // Admin/Project Admin : accès total
        if ($user->can('projects.view-any')) {
            return true;
        }

        // Manager : accès par filiale (LOGIQUE PRÉSERVÉE)
        if ($user->can('projects.view-filiale')) {
            $userFiliale = $user->getFiliale();

            if (!$userFiliale) {
                return false; // Manager sans filiale = no access (sécurité)
            }

            // Normalisation des accents (PRÉSERVÉE)
            $normalizedUser = $this->normalizeFilialeName($userFiliale);
            $normalizedExecutant = $this->normalizeFilialeName($project->filiale_executant);
            $normalizedContractant = $this->normalizeFilialeName($project->filiale_contractant);

            return $normalizedExecutant === $normalizedUser
                || $normalizedContractant === $normalizedUser;
        }

        // User : ses propres projets
        if ($user->can('projects.view-own')) {
            return $project->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can create projects
     */
    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    /**
     * Determine if user can update a project
     */
    public function update(User $user, Project $project): bool
    {
        // Peut éditer n'importe quel projet (Admin/Project Admin)
        if ($user->can('projects.update')) {
            return true;
        }

        // Peut éditer ses propres projets (Manager/User)
        if ($user->can('projects.update-own')) {
            return $project->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can delete a project
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete');
    }

    /**
     * Determine if user can duplicate a project
     * Peut dupliquer si peut voir le projet
     */
    public function duplicate(User $user, Project $project): bool
    {
        return $user->can('projects.duplicate') && $this->view($user, $project);
    }

    /**
     * Determine if user can sync project to Planner
     */
    public function syncPlanner(User $user, Project $project): bool
    {
        return $user->can('projects.sync-planner') && $this->view($user, $project);
    }

    /**
     * Determine if user can download project documents
     */
    public function downloadDocument(User $user, Project $project): bool
    {
        return $user->can('documents.download') && $this->view($user, $project);
    }

    /**
     * Validate that a manager's project includes their filiale
     * Prevents creating projects they won't be able to see
     *
     * Rule: At least ONE filiale (executant OR contractant) must match manager's filiale
     *
     * @param User $user
     * @param array $projectData
     * @return bool
     */
    public function validateManagerFiliale(User $user, array $projectData): bool
    {
        // Admin/Project Admin: no constraint (bypass via view-any permission)
        if ($user->can('projects.view-any')) {
            return true;
        }

        // Not a manager: no constraint (Users see projects via user_id)
        if (!$user->can('projects.view-filiale')) {
            return true;
        }

        // Manager without filiale: cannot create projects (security)
        $userFiliale = $user->getFiliale();
        if (!$userFiliale) {
            return false;
        }

        // Extract filiales from project data
        $filialeExecutant = $projectData['filiale_executant'] ?? null;
        $filialeContractant = $projectData['filiale_contractant'] ?? null;

        // Normalize for accent-insensitive comparison (same as view())
        $normalizedUser = $this->normalizeFilialeName($userFiliale);
        $normalizedExecutant = $this->normalizeFilialeName($filialeExecutant);
        $normalizedContractant = $this->normalizeFilialeName($filialeContractant);

        // At least ONE must match
        return $normalizedExecutant === $normalizedUser
            || $normalizedContractant === $normalizedUser;
    }

    /**
     * Helper préservé de User.php pour normaliser les noms de filiale
     * Gère les accents français (Télécom vs Telecom)
     *
     * @param string|null $name
     * @return string
     */
    private function normalizeFilialeName(?string $name): string
    {
        if (!$name) {
            return '';
        }

        $normalized = mb_strtolower($name);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        // Normalisation des accents français
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ù', 'û', 'ü', 'ô', 'ö', 'î', 'ï', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'o', 'o', 'i', 'i', 'c'],
            $normalized
        );

        return $normalized;
    }
}
