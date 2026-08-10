<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoire extends Model
{
    use HasFactory;

    public const TYPES = ['cours' => 'Salle de cours', 'amphi' => 'Amphithéâtre', 'td' => 'Salle de TD', 'tp' => 'Laboratoire'];

    public const ETATS = ['disponible' => 'Disponible', 'occupee' => 'Occupée', 'maintenance' => 'En maintenance'];

    protected $fillable = ['batiment_id', 'nom', 'capacite', 'type', 'equipements', 'disponibilite', 'etat'];

    protected $casts = [
        'capacite' => 'integer',
        'disponibilite' => 'boolean',
    ];

    public function batiment(): BelongsTo
    {
        return $this->belongsTo(Batiment::class);
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function etatLabel(): string
    {
        return self::ETATS[$this->etat] ?? $this->etat;
    }

    public function estDisponible(): bool
    {
        return $this->disponibilite && $this->etat === 'disponible';
    }
}
