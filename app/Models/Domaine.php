<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domaine extends Model
{
    use HasFactory;

    protected $fillable = ['faculte_id', 'nom', 'description', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function faculte(): BelongsTo
    {
        return $this->belongsTo(Faculte::class);
    }

    public function filieres(): HasMany
    {
        return $this->hasMany(Filiere::class);
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->where('faculte_id', $faculteId);
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function statutLabel(): string
    {
        return $this->actif ? 'Actif' : 'Inactif';
    }
}
