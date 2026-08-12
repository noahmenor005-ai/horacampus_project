<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ec extends Model
{
    use HasFactory;

    public const STATUTS = [
        'actif' => 'Actif',
        'inactif' => 'Inactif',
    ];

    protected $fillable = [
        'ue_id',
        'code',
        'nom',
        'coefficient',
        'volume_horaire',
        'credit',
        'enseignant_id',
        'statut',
    ];

    protected $casts = [
        'coefficient' => 'integer',
        'volume_horaire' => 'integer',
        'credit' => 'integer',
    ];

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ec_user', 'ec_id', 'user_id')->withTimestamps();
    }

    public function cours(): HasMany
    {
        return $this->hasMany(Cours::class);
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class);
    }

    public function promotion(): ?Promotion
    {
        return optional($this->ue)->promotion;
    }

    public function getIntituleAttribute(): string
    {
        return $this->nom;
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->whereHas('ue.promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId));
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
