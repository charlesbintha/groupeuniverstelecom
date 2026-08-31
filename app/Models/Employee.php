<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employes';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aad_id',
        'prenom_nom',
        'email',
        'filiale',
        'direction',
        'actif',
        'poste',
        'telephone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Get the user associated with this employee.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'employe_id');
    }

    /**
     * Get projects where this employee is chef de projet.
     */
    public function projectsAsChef()
    {
        return $this->hasMany(Project::class, 'chef_projet', 'prenom_nom');
    }

    /**
     * Get projects where this employee is directeur.
     */
    public function projectsAsDirecteur()
    {
        return $this->hasMany(Project::class, 'directeur_projet', 'prenom_nom');
    }

    /**
     * Get stakeholder records for this employee.
     */
    public function stakeholderRecords()
    {
        return $this->hasMany(ProjectStakeholder::class, 'employe_id');
    }

    /**
     * Get projects where this employee is a stakeholder.
     */
    public function projectsAsStakeholder()
    {
        return $this->belongsToMany(
            Project::class,
            'project_stakeholders',
            'employe_id',
            'project_id'
        )->withPivot(['role', 'snapshot_nom', 'snapshot_filiale', 'snapshot_direction'])
         ->withTimestamps();
    }

    /**
     * Scope to find active employees only.
     */
    public function scopeActive($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope to find inactive employees.
     */
    public function scopeInactive($query)
    {
        return $query->where('actif', false);
    }

    /**
     * Scope to search by name or email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('prenom_nom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
    }

    /**
     * Scope to filter by filiale
     */
    public function scopeByFiliale($query, string $filiale)
    {
        return $query->where('filiale', $filiale);
    }

    /**
     * Scope to filter by direction
     */
    public function scopeByDirection($query, string $direction)
    {
        return $query->where('direction', $direction);
    }

    /**
     * Scope to find by Azure AD ID
     */
    public function scopeByAadId($query, string $aadId)
    {
        return $query->where('aad_id', $aadId);
    }

    /**
     * Check if employee has Azure AD integration
     */
    public function hasAzureAdIntegration(): bool
    {
        return !empty($this->aad_id);
    }

    /**
     * Get full display name (same as prenom_nom but method for consistency)
     */
    public function getFullNameAttribute(): string
    {
        return $this->prenom_nom;
    }
}
