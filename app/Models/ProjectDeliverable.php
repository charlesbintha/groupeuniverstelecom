<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDeliverable extends Model
{
    protected $table = 'project_deliverables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'livrable',
        'description',
        'date_prevue',
        'realise',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_id' => 'integer',
        'date_prevue' => 'date',
        'realise' => 'boolean',
    ];

    /**
     * Get the project that owns this deliverable.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the documents for this deliverable.
     */
    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'deliverable_id');
    }

    /**
     * Scope to filter by project
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope to filter by status
     */


    /**
     * Check if deliverable is overdue
     */
    public function isOverdue(): bool
    {
        return !$this->date_reelle && $this->date_prevue && $this->date_prevue->isPast();
    }

    /**
     * Check if deliverable is completed
     */
    public function isCompleted(): bool
    {
        return !is_null($this->date_reelle);
    }
}
