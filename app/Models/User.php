<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employe_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the employee associated with this user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employe_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Scope to find active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to find inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to find admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to find regular users.
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope to find manager users.
     */
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get user's filiale from their employee record.
     * Returns null if no employee or no filiale defined.
     */
    public function getFiliale(): ?string
    {
        if (! $this->employee) {
            return null;
        }

        $filiale = trim($this->employee->filiale ?? '');

        return $filiale !== '' ? $filiale : null;
    }

    /**
     * Normalize filiale name for comparison.
     * Removes accents, converts to lowercase, trims whitespace.
     */
    private function normalizeFilialeName(string $name): string
    {
        // Trim whitespace
        $name = trim($name);

        // Convert to lowercase
        $name = mb_strtolower($name);

        // Remove accents by replacing common French accented characters
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
        ];
        $name = strtr($name, $accents);

        // Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);

        return $name;
    }

    /**
     * Check if user can access a specific project.
     * Security: Strict validation with multiple checks.
     *
     * Access rules:
     * - Admin: Full access to all projects
     * - Manager: Access to projects where filiale_executant OR filiale_contractant matches their filiale
     * - User: Only their own projects (user_id match)
     */
    public function canAccessProject(Project $project): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isManager()) {
            // Ensure employee is loaded
            if (! $this->relationLoaded('employee')) {
                $this->load('employee');
            }

            $userFiliale = $this->getFiliale();

            if (! $userFiliale) {
                return false;
            }

            $executantFiliale = trim($project->filiale_executant ?? '');
            $contractantFiliale = trim($project->filiale_contractant ?? '');

            // Normalize for comparison (case-insensitive, accent-insensitive, trim whitespace)
            $normalizedUser = $this->normalizeFilialeName($userFiliale);
            $normalizedExecutant = $this->normalizeFilialeName($executantFiliale);
            $normalizedContractant = $this->normalizeFilialeName($contractantFiliale);

            $hasAccess = $normalizedExecutant === $normalizedUser ||
                         $normalizedContractant === $normalizedUser;

            return $hasAccess;
        }

        return $project->user_id === $this->id;
    }
}
