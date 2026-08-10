<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculte extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'nom', 'description'];

    public function domaines(): HasMany
    {
        return $this->hasMany(Domaine::class);
    }

    public function filieres(): HasMany
    {
        return $this->hasManyThrough(Filiere::class, Domaine::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasManyThrough(Mention::class, Filiere::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasManyThrough(Promotion::class, Mention::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function membres(): HasMany
    {
        return $this->hasMany(User::class, 'faculte_id');
    }

    public function decanat(): HasMany
    {
        return $this->users()->where('role', User::ROLE_DECANAT);
    }
}
