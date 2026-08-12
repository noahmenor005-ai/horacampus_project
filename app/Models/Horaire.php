<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'annee_academique_id',
        'domaine_id',
        'filiere_id',
        'mention_id',
        'ue_id',
        'ec_id',
        'date',
        'jour',
        'heure_debut',
        'heure_fin',
        'effectif_attendu',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
        'effectif_attendu' => 'integer',
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

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class, 'horaire_id');
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function domaine(): BelongsTo
    {
        return $this->belongsTo(Domaine::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function ec(): BelongsTo
    {
        return $this->belongsTo(Ec::class);
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

    public function getJourAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        return self::jourFr($this->date);
    }

    public function hasSalle(): bool
    {
        return !empty($this->auditoire_id) && optional($this->auditoire)->nom !== 'EN-ATTENTE';
    }

    public function demandeEnAttente(): ?DemandeAuditoire
    {
        return $this->demandes()
            ->whereIn('statut', [DemandeAuditoire::STATUT_EN_ATTENTE, DemandeAuditoire::STATUT_PENDING])
            ->latest()
            ->first();
    }

    public static function jourFr($date): string
    {
        if (!$date) {
            return '';
        }

        $noms = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        return $noms[(int) ($date instanceof \Carbon\Carbon ? $date->dayOfWeek : \Carbon\Carbon::parse($date)->dayOfWeek)];
    }
}
