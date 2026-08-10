<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'nom',
        'postnom',
        'prenom',
        'sexe',
        'telephone',
        'email',
        'faculte_id',
        'specialite',
        'grade',
        'statut',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculte(): BelongsTo
    {
        return $this->belongsTo(Faculte::class);
    }

    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom . ' ' : '') . $this->nom . ($this->postnom ? ' ' . $this->postnom : ''));
    }
}
