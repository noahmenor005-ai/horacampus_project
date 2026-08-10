<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = ['mention_id', 'annee_academique_id', 'nom', 'niveau', 'effectif'];

    protected $casts = ['effectif' => 'integer'];

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
}
