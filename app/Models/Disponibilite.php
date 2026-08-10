<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilite extends Model
{
    use HasFactory;

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_REFUSEE = 'refusee';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_VALIDEE => 'Validée',
        self::STATUT_REFUSEE => 'Refusée',
    ];

    protected $fillable = ['user_id', 'semestre_id', 'jour', 'heure_debut', 'heure_fin', 'statut'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function badgeClass(): string
    {
        return [
            self::STATUT_EN_ATTENTE => 'warning',
            self::STATUT_VALIDEE => 'success',
            self::STATUT_REFUSEE => 'danger',
        ][$this->statut] ?? 'secondary';
    }

    public function chevauche(self $autre): bool
    {
        return $this->jour === $autre->jour
            && $this->heure_debut < $autre->heure_fin
            && $this->heure_fin > $autre->heure_debut;
    }
}
