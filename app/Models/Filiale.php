<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiale extends Model
{
    protected $table = 'filiales';
    protected $primaryKey = 'id_filiale';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_filiale',
        'code_filiale',
    ];

    /**
     * Get the directions for this filiale.
     */
    public function directions()
    {
        return $this->hasMany(Direction::class, 'filiale', 'nom_filiale');
    }

    /**
     * Scope to search by name or code
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('nom_filiale', 'like', "%{$search}%")
                    ->orWhere('code_filiale', 'like', "%{$search}%");
    }

    /**
     * Scope to find by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code_filiale', $code);
    }
}
