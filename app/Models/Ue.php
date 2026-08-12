<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ue extends Model
{
    use HasFactory;

    public const STATUTS = [
        'actif' => 'Actif',
        'inactif' => 'Inactif',
    ];

    protected $fillable = [
        'promotion_id',
        'semestre_id',
        'mention_id',
        'annee_academique_id',
        'code',
        'nom',
        'description',
        'credit',
        'statut',
    ];

    protected $casts = [
        'credit' => 'integer',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function ecs(): HasMany
    {
        return $this->hasMany(Ec::class);
    }

    public function getIntituleAttribute(): string
    {
        return $this->nom;
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId));
    }

    public function estActif(): bool
    {
        return ($this->statut ?? 'actif') === 'actif';
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut ?? 'actif'] ?? ($this->statut ?? 'Actif');
    }
}
