<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['domaine_id', 'nom', 'description', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function domaine(): BelongsTo
    {
        return $this->belongsTo(Domaine::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    public function faculte(): ?Faculte
    {
        return optional($this->domaine)->faculte;
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->whereHas('domaine', fn ($q) => $q->where('faculte_id', $faculteId));
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
