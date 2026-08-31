<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStakeholder extends Model
{
    protected $table = 'project_stakeholders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'employe_id',
        'role',
        'prenom_nom',
        'email',
        'attentes',
        'aad_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_id' => 'integer',
        'employe_id' => 'integer',
    ];

    /**
     * Get the project that owns this stakeholder.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the employee associated with this stakeholder.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employe_id');
    }

    /**
     * Scope to filter by project
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope to filter by employee
     */
    public function scopeByEmployee($query, int $employeeId)
    {
        return $query->where('employe_id', $employeeId);
    }

    /**
     * Scope to filter by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

}
