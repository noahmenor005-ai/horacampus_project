<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batiment extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'nom', 'adresse'];

    public function auditoires(): HasMany
    {
        return $this->hasMany(Auditoire::class);
    }
}
