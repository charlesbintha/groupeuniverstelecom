<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAction extends Model
{
    protected $table = 'project_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'libelle',
        'ordre',
        'ms_task_id',
        'ms_task_etag',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_id' => 'integer',
        'ordre' => 'integer',
    ];

    /**
     * Get the project that owns this action.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Scope to order by ordre field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Scope to filter by project
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Check if action is synced to MS Planner
     */
    public function isSyncedToPlanner(): bool
    {
        return !empty($this->ms_task_id);
    }

    /**
     * Scope to filter synced actions (have ms_task_id)
     */
    public function scopeSynced($query)
    {
        return $query->whereNotNull('ms_task_id');
    }

    /**
     * Scope to filter unsynced actions (no ms_task_id)
     */
    public function scopeUnsynced($query)
    {
        return $query->whereNull('ms_task_id');
    }
}
