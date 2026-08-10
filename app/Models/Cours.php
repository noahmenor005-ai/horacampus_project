<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cours extends Model
{
    use HasFactory;

    public const TYPES = ['CM' => 'Cours magistral', 'TD' => 'Travaux dirigés', 'TP' => 'Travaux pratiques'];

    protected $fillable = ['ec_id', 'enseignant_id', 'promotion_id', 'type', 'volume_horaire'];

    protected $casts = ['volume_horaire' => 'integer'];

    public function ec(): BelongsTo
    {
        return $this->belongsTo(Ec::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class);
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(Horaire::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIntituleAttribute(): string
    {
        return optional($this->ec)->nom . ' (' . $this->type . ')';
    }
}
