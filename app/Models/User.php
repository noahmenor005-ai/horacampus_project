<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_DECANAT = 'decanat';
    public const ROLE_ENSEIGNANT = 'enseignant';
    public const ROLE_ETUDIANT = 'etudiant';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_DECANAT,
        self::ROLE_ENSEIGNANT,
        self::ROLE_ETUDIANT,
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    public const SEXE_M = 'M';
    public const SEXE_F = 'F';
    public const SEXE_AUTRE = 'Autre';

    public const SEXES = ['M', 'F', 'Autre'];

    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';

    public const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

    protected $fillable = [
        'nom',
        'postnom',
        'prenom',
        'matricule',
        'sexe',
        'email',
        'password',
        'telephone',
        'role',
        'status',
        'statut_inscription',
        'is_active',
        'faculte_id',
        'domaine_id',
        'filiere_id',
        'mention_id',
        'promotion_id',
        'annee_academique_id',
        'photo_path',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom . ' ' : '') . $this->nom);
    }

    public function getNomCompletAvecPostnomAttribute(): string
    {
        $parts = array_filter([$this->prenom, $this->nom, $this->postnom]);
        return implode(' ', $parts);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDecanat(): bool
    {
        return $this->role === self::ROLE_DECANAT;
    }

    public function isEnseignant(): bool
    {
        return $this->role === self::ROLE_ENSEIGNANT;
    }

    public function isEtudiant(): bool
    {
        return $this->role === self::ROLE_ETUDIANT;
    }

    public function isAcademicStaff(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_DECANAT], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isActive(): bool
    {
        // Un étudiant/enseignant est actif si is_active=true et status=accepted (ou statut_inscription actif)
        if (isset($this->attributes['is_active'])) {
            return (bool) $this->is_active && $this->status !== self::STATUS_REJECTED;
        }
        return $this->isAccepted();
    }

    public function roleLabel(): string
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_DECANAT => 'Décanat',
            self::ROLE_ENSEIGNANT => 'Enseignant',
            self::ROLE_ETUDIANT => 'Étudiant',
        ][$this->role] ?? $this->role;
    }

    public function statusLabel(): string
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Accepté',
            self::STATUS_REJECTED => 'Refusé',
        ][$this->status] ?? $this->status;
    }

    public function sexeLabel(): string
    {
        return [
            'M' => 'Masculin',
            'F' => 'Féminin',
            'Autre' => 'Autre',
        ][$this->sexe] ?? ($this->sexe ?? '-');
    }

    public function faculte(): BelongsTo
    {
        return $this->belongsTo(Faculte::class, 'faculte_id');
    }

    public function domaine(): BelongsTo
    {
        return $this->belongsTo(Domaine::class, 'domaine_id');
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class, 'mention_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class, 'annee_academique_id');
    }

    public function etudiantProfile(): HasOne
    {
        return $this->hasOne(Etudiant::class, 'user_id');
    }

    public function enseignantProfile(): HasOne
    {
        return $this->hasOne(Enseignant::class, 'user_id');
    }

    public function ecs(): BelongsToMany
    {
        return $this->belongsToMany(Ec::class, 'ec_user', 'user_id', 'ec_id')->withTimestamps();
    }

    public function coursEnseignes(): HasMany
    {
        return $this->hasMany(Cours::class, 'enseignant_id');
    }

    public function horairesEnseignes(): HasMany
    {
        return $this->hasMany(Horaire::class, 'enseignant_id');
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class, 'user_id');
    }

    public function demandesCreees(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(Historique::class, 'user_id');
    }

    // Helpers pour la hiérarchie LMD
    public function belongsToFaculty(int $faculteId): bool
    {
        return (int) $this->faculte_id === $faculteId;
    }

    public function scopeForFaculty($query, int $faculteId)
    {
        return $query->where('faculte_id', $faculteId);
    }
}
