<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandeAuditoire extends Model
{
    use HasFactory;

    protected $table = 'demandes_auditoire';

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_ACCEPTEE = 'acceptee';
    public const STATUT_REFUSEE = 'refusee';
    public const STATUT_MODIFIEE = 'modifiee';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_ACCEPTEE => 'Acceptée',
        self::STATUT_REFUSEE => 'Refusée',
        self::STATUT_MODIFIEE => 'Modifiée',
    ];

    protected $fillable = [
        'cours_id',
        'enseignant_id',
        'promotion_id',
        'auditoire_id',
        'semestre_id',
        'created_by',
        'date',
        'heure_debut',
        'heure_fin',
        'effectif_attendu',
        'statut',
        'motif_refus',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'effectif_attendu' => 'integer',
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function auditoire(): BelongsTo
    {
        return $this->belongsTo(Auditoire::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class, 'source_demande_id');
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function estModifiable(): bool
    {
        return in_array($this->statut, [self::STATUT_EN_ATTENTE, self::STATUT_MODIFIEE], true);
    }

    public function badgeClass(): string
    {
        return [
            self::STATUT_EN_ATTENTE => 'warning',
            self::STATUT_ACCEPTEE => 'success',
            self::STATUT_REFUSEE => 'danger',
            self::STATUT_MODIFIEE => 'info',
        ][$this->statut] ?? 'secondary';
    }
}
