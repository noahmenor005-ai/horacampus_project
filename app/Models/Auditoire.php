<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoire extends Model
{
    use HasFactory;

    public const TYPES = [
        'auditoire' => 'Auditoire',
        'laboratoire' => 'Laboratoire',
        'informatique' => 'Salle informatique',
        'cours' => 'Salle de cours',
        'polyvalente' => 'Salle polyvalente',
        'attente' => 'Salle virtuelle',
    ];

    public const EQUIPEMENTS = [
        'projecteur' => 'Projecteur',
        'tableau' => 'Tableau',
        'ordinateur' => 'Ordinateur',
        'audio' => 'Système audio',
        'internet' => 'Internet',
    ];

    public const ETATS = [
        'disponible' => 'Disponible',
        'occupee' => 'Occupée',
        'maintenance' => 'En maintenance',
    ];

    protected $fillable = [
        'batiment_id',
        'nom',
        'numero',
        'capacite',
        'type',
        'equipements',
        'disponibilite',
        'etat',
        'statut',
    ];

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
        return $this->disponibilite && $this->etat === 'disponible' && ($this->statut ?? 'actif') !== 'inactif';
    }

    public function isPlaceholder(): bool
    {
        return $this->nom === 'EN-ATTENTE' || $this->type === 'attente';
    }

    public function equipementsList(): array
    {
        if (!$this->equipements) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;|]/', mb_strtolower($this->equipements)))));
    }
}
