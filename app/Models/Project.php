<?php

namespace App\Models;

use App\Enums\BankType;
use App\Enums\ContractualisationType;
use App\Enums\ProjectNature;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Enums\ResourceType;
use App\Enums\StatutFinancier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code_projet',
        'nom_projet',
        'type_projet',
        'axe_strategique',
        'nature_projet',
        // Structure Exécutant vs Contractant
        'filiale_executant',
        'filiale_contractant',
        'direction_executant',
        'direction_contractant',
        'owner_executant',
        'owner_contractant',
        'account_manager',
        // Autres champs
        'statut_initial',
        'statut_financier',
        'ressource_a_mobiliser',
        'resource_type',
        'resource_bank',
        'contractualisation',
        'contractualisation_type',
        'objectif_projet',
        'contexte',
        'synthese',
        'budget_initial',
        'montant_encaissement',
        'montant_decaissement_2',
        'montant_decaissement',
        'montant_recouvrement',
        'montant_recouvre',
        'date_demarrage',
        'date_fin',
        'prochaine_etape',
        'notes',
        'sf_opportunity_id',
        'sf_opportunity_name',
        'sf_opportunity_stage',
        'sf_opportunity_amount',
        'ms_group_id',
        'ms_plan_id',
        'ms_bucket_id',
        'maintenance_glpi',
        'glpi_project_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type_projet' => ProjectType::class,
        'nature_projet' => ProjectNature::class,
        'statut_initial' => ProjectStatus::class,
        'statut_financier' => StatutFinancier::class,
        'ressource_a_mobiliser' => 'boolean',
        'resource_type' => ResourceType::class,
        'resource_bank' => BankType::class,
        'contractualisation' => 'boolean',
        'contractualisation_type' => ContractualisationType::class,
        'budget_initial' => 'decimal:2',
        'montant_encaissement' => 'decimal:2',
        'montant_decaissement_2' => 'decimal:2',
        'montant_decaissement' => 'decimal:2',
        'montant_recouvrement' => 'decimal:2',
        'montant_recouvre' => 'decimal:2',
        'sf_opportunity_amount' => 'decimal:2',
        'date_demarrage' => 'date',
        'date_fin' => 'date',
        'notes' => 'string',
        'maintenance_glpi' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Mutator: Convert project name to uppercase
     */
    protected function nomProjet(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper(Str::ascii(trim($value))),
        );
    }

    /**
     * Get the user (creator) of this project.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the actions for this project.
     */
    public function actions()
    {
        return $this->hasMany(ProjectAction::class, 'project_id');
    }

    /**
     * Get the deliverables for this project.
     */
    public function deliverables()
    {
        return $this->hasMany(ProjectDeliverable::class, 'project_id');
    }

    /**
     * Get the stakeholders for this project.
     */
    public function stakeholders()
    {
        return $this->hasMany(ProjectStakeholder::class, 'project_id');
    }

    /**
     * Get the external stakeholders for this project.
     */
    public function externalStakeholders()
    {
        return $this->hasMany(ProjectExternalStakeholder::class, 'project_id');
    }

    /**
     * Get the issues for this project.
     */
    public function issues()
    {
        return $this->hasMany(ProjectIssue::class, 'project_id');
    }

    /**
     * Get the documents for this project.
     */
    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    /**
     * Get employees associated as stakeholders via pivot.
     */
    public function stakeholderEmployees()
    {
        return $this->belongsToMany(
            Employee::class,
            'project_stakeholders',
            'project_id',
            'employe_id'
        )->withPivot(['role', 'snapshot_nom', 'snapshot_filiale', 'snapshot_direction'])
         ->withTimestamps();
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, ProjectType $type)
    {
        return $query->where('type_projet', $type);
    }

    /**
     * Scope to filter by nature
     */
    public function scopeByNature($query, ProjectNature $nature)
    {
        return $query->where('nature_projet', $nature);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, ProjectStatus $status)
    {
        return $query->where('statut_initial', $status);
    }

    /**
     * Scope to filter by filiale (exécutant ou contractant)
     */
    public function scopeByFiliale($query, string $filiale)
    {
        return $query->where(function($q) use ($filiale) {
            $q->where('filiale_executant', $filiale)
              ->orWhere('filiale_contractant', $filiale);
        });
    }

    /**
     * Scope to filter by filiale exécutant
     */
    public function scopeByFilialeExecutant($query, string $filiale)
    {
        return $query->where('filiale_executant', $filiale);
    }

    /**
     * Scope to filter by filiale contractant
     */
    public function scopeByFilialeContractant($query, string $filiale)
    {
        return $query->where('filiale_contractant', $filiale);
    }

    /**
     * Scope to filter by owner (exécutant ou contractant)
     */
    public function scopeByOwner($query, string $owner)
    {
        return $query->where(function($q) use ($owner) {
            $q->where('owner_executant', $owner)
              ->orWhere('owner_contractant', $owner);
        });
    }

    /**
     * Scope to filter by owner exécutant
     */
    public function scopeByOwnerExecutant($query, string $owner)
    {
        return $query->where('owner_executant', $owner);
    }

    /**
     * Scope to filter by owner contractant
     */
    public function scopeByOwnerContractant($query, string $owner)
    {
        return $query->where('owner_contractant', $owner);
    }

    /**
     * Check if project has different filiales (executant vs contractant)
     * If true, automatic tasks should be added
     */
    public function hasDifferentFiliales(): bool
    {
        return !empty($this->filiale_executant)
            && !empty($this->filiale_contractant)
            && $this->filiale_executant !== $this->filiale_contractant;
    }

    /**
     * Get the owner executant name (for task assignment)
     */
    public function getOwnerExecutantName(): ?string
    {
        return $this->owner_executant;
    }

    /**
     * Scope to filter by user (creator)
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by current authenticated user
     */
    public function scopeOwnedByCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope to search by code or name
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('code_projet', 'like', "%{$search}%")
                    ->orWhere('nom_projet', 'like', "%{$search}%");
    }

    /**
     * Scope for internal projects
     */
    public function scopeInternal($query)
    {
        return $query->where('type_projet', ProjectType::INTERNE);
    }

    public function getTypeProjetStringAttribute(): string
    {
        $type = $this->type_projet;

        if ($type instanceof \BackedEnum) {
            return $type->value;
        }

        return (string) ($type ?? '');
    }
    /**
     * Scope for external projects
     */
    public function scopeExternal($query)
    {
        return $query->where('type_projet', ProjectType::EXTERNE);
    }

    /**
     * Scope for active projects (Planifié or En cours)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('statut_initial', [
            ProjectStatus::PLANIFIE,
            ProjectStatus::EN_COURS
        ]);
    }

    /**
     * Scope for completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->where('statut_initial', ProjectStatus::TERMINE);
    }

    /**
     * Scope for projects with Salesforce integration
     */
    public function scopeWithSalesforce($query)
    {
        return $query->whereNotNull('sf_opportunity_id');
    }

    /**
     * Scope for projects with MS Planner integration
     */
    public function scopeWithMsPlanner($query)
    {
        return $query->whereNotNull('ms_plan_id');
    }

    /**
     * Check if project requires Salesforce
     */
    public function requiresSalesforce(): bool
    {
        return $this->type_projet === ProjectType::EXTERNE;
    }

    /**
     * Check if project has Salesforce integration
     */
    public function hasSalesforceIntegration(): bool
    {
        return !empty($this->sf_opportunity_id);
    }

    /**
     * Check if project has MS Planner integration
     */
    public function hasMsPlannerIntegration(): bool
    {
        return !empty($this->ms_plan_id);
    }

    /**
     * Check if project is active
     */
    public function isActive(): bool
    {
        return $this->statut_initial?->isActive() ?? false;
    }

    /**
     * Check if project is completed
     */
    public function isCompleted(): bool
    {
        return $this->statut_initial === ProjectStatus::TERMINE;
    }

    /**
     * Get allowed status transitions
     */
    public function allowedStatusTransitions(): array
    {
        return $this->statut_initial?->allowedTransitions() ?? [];
    }
}
