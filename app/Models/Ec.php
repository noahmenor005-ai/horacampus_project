<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ec extends Model
{
    use HasFactory;

    protected $fillable = ['ue_id', 'code', 'nom', 'coefficient', 'volume_horaire'];

    protected $casts = [
        'coefficient' => 'integer',
        'volume_horaire' => 'integer',
    ];

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ec_user', 'ec_id', 'user_id')->withTimestamps();
    }

    public function cours(): HasMany
    {
        return $this->hasMany(Cours::class);
    }

    public function promotion(): ?Promotion
    {
        return optional($this->ue)->promotion;
    }
}
