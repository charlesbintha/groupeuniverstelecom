<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiFamily extends Model
{
    protected $table = 'kpi_familles';
    protected $primaryKey = 'id_famille';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_famille',
        'description',
        'ordre',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ordre' => 'integer',
    ];

    /**
     * Get the KPI indicators for this family.
     */
    public function indicators()
    {
        return $this->hasMany(KpiIndicator::class, 'famille_id', 'id_famille');
    }

    /**
     * Scope to order by ordre field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('nom_famille', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }
}
