<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horaire extends Model
{
    use HasFactory;

    public const STATUT_VALIDE = 'valide';
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_ANNULE = 'annule';

    public const STATUTS = [
        self::STATUT_VALIDE => 'Validé',
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_ANNULE => 'Annulé',
    ];

    protected $fillable = [
        'cours_id',
        'auditoire_id',
        'enseignant_id',
        'promotion_id',
        'semestre_id',
        'source_demande_id',
        'date',
        'heure_debut',
        'heure_fin',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    public function auditoire(): BelongsTo
    {
        return $this->belongsTo(Auditoire::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeAuditoire::class, 'source_demande_id');
    }

    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function badgeClass(): string
    {
        return [
            self::STATUT_VALIDE => 'success',
            self::STATUT_BROUILLON => 'warning',
            self::STATUT_ANNULE => 'danger',
        ][$this->statut] ?? 'secondary';
    }

    public function getJourAttribute(): string
    {
        return self::jourFr($this->date);
    }

    public static function jourFr($date): string
    {
        $noms = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        return $noms[(int) ($date instanceof \Carbon\Carbon ? $date->dayOfWeek : \Carbon\Carbon::parse($date)->dayOfWeek)];
    }
}
