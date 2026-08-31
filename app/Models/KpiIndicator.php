<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    protected $table = 'kpi_indicateurs';
    protected $primaryKey = 'id_indicateur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'famille_id',
        'nom_indicateur',
        'description',
        'unite',
        'ordre',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'famille_id' => 'integer',
        'ordre' => 'integer',
    ];

    /**
     * Get the family that owns this indicator.
     */
    public function family()
    {
        return $this->belongsTo(KpiFamily::class, 'famille_id', 'id_famille');
    }

    /**
     * Scope to order by ordre field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Scope to filter by family
     */
    public function scopeByFamily($query, int $familyId)
    {
        return $query->where('famille_id', $familyId);
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('nom_indicateur', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }
}
