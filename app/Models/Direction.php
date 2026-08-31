<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $table = 'directions';
    protected $primaryKey = 'id_direction';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filiale',
        'nom_direction',
        'code_direction',
    ];

    /**
     * Get the filiale that owns this direction.
     */
    public function filialeRelation()
    {
        return $this->belongsTo(Filiale::class, 'filiale', 'nom_filiale');
    }

    /**
     * Scope to search by name or code
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('nom_direction', 'like', "%{$search}%")
                    ->orWhere('code_direction', 'like', "%{$search}%");
    }

    /**
     * Scope to filter by filiale
     */
    public function scopeByFiliale($query, string $filiale)
    {
        return $query->where('filiale', $filiale);
    }

    /**
     * Scope to find by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code_direction', $code);
    }
}
