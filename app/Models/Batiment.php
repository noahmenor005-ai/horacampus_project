<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batiment extends Model
{
    use HasFactory;

    public const STATUTS = [
        'actif' => 'Actif',
        'inactif' => 'Inactif',
        'maintenance' => 'Maintenance',
    ];

    protected $fillable = [
        'code',
        'nom',
        'adresse',
        'localisation',
        'nombre_etages',
        'description',
        'statut',
    ];

    protected $casts = [
        'nombre_etages' => 'integer',
    ];

    public function auditoires(): HasMany
    {
        return $this->hasMany(Auditoire::class);
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut ?? 'actif'] ?? ($this->statut ?? 'Actif');
    }

    public function localisationLabel(): string
    {
        return $this->localisation ?: ($this->adresse ?: '—');
    }
}
