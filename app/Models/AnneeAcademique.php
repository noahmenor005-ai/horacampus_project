<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnneeAcademique extends Model
{
    use HasFactory;

    protected $table = 'annees_academiques';

    protected $fillable = ['libelle', 'date_debut', 'date_fin', 'active'];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'active' => 'boolean',
    ];

    public function semestres(): HasMany
    {
        return $this->hasMany(Semestre::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function estActive(): bool
    {
        return (bool) $this->active;
    }

    public function statutLabel(): string
    {
        return $this->active ? 'Active' : 'Inactive';
    }

    public function activerUniquement(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['active' => false]);
        $this->update(['active' => true]);
    }
}
