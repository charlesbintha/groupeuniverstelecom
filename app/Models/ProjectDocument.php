<?php

namespace App\Models;

use App\Enums\ContractualisationType;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'document_type',
        'deliverable_id',
        'name',
        'original_filename',
        'stored_filename',
        'path',
        'mime_type',
        'size',
        'contract_type',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'project_id' => 'integer',
            'deliverable_id' => 'integer',
            'size' => 'integer',
            'uploaded_by' => 'integer',
            'document_type' => DocumentType::class,
            'contract_type' => ContractualisationType::class,
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function deliverable()
    {
        return $this->belongsTo(ProjectDeliverable::class, 'deliverable_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasFile(): bool
    {
        return $this->path && Storage::disk('private')->exists($this->path);
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    public function deleteFile(): bool
    {
        if ($this->hasFile()) {
            return Storage::disk('private')->delete($this->path);
        }

        return false;
    }
}
