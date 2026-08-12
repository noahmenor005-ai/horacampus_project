<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mention extends Model
{
    use HasFactory;

    protected $fillable = ['filiere_id', 'nom', 'description', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function ues(): HasMany
    {
        return $this->hasMany(Ue::class);
    }

    public function faculte(): ?Faculte
    {
        return optional($this->filiere)->faculte();
    }

    public function scopeForFaculty($query, $faculteId)
    {
        return $query->whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId));
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
