<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectExternalStakeholder extends Model
{
    protected $table = 'project_external_stakeholders';

    protected $fillable = [
        'project_id',
        'organisation',
        'nom_complet',
        'email',
        'role',
        'attentes',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
