<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    public const NIVEAUX = ['L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3', 'M1' => 'M1', 'M2' => 'M2'];

    protected $fillable = ['mention_id', 'annee_academique_id', 'nom', 'niveau', 'effectif', 'actif'];

    protected $casts = [
        'effectif' => 'integer',
        'actif' => 'boolean',
    ];

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function ues(): HasMany
    {
        return $this->hasMany(Ue::class);
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(User::class, 'promotion_id');
    }

    public function cours(): HasMany
    {
        return $this->hasMany(Cours::class);
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class);
    }

    public function faculte(): ?Faculte
    {
        return optional($this->mention)->faculte();
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId));
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
