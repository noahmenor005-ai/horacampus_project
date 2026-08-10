<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Ue;

trait ScopesFaculty
{
    protected function facultyId()
    {
        return auth()->user()->faculte_id;
    }

    protected function isScoped(): bool
    {
        return auth()->user()->isDecanat();
    }

    protected function scopeDomaines()
    {
        return $this->isScoped() ? Domaine::where('faculte_id', $this->facultyId()) : Domaine::query();
    }

    protected function scopeFilieres()
    {
        return $this->isScoped()
            ? Filiere::whereHas('domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()))
            : Filiere::query();
    }

    protected function scopeMentions()
    {
        return $this->isScoped()
            ? Mention::whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()))
            : Mention::query();
    }

    protected function scopePromotions()
    {
        return $this->isScoped()
            ? Promotion::whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()))
            : Promotion::query();
    }

    protected function scopeUes()
    {
        return $this->isScoped()
            ? Ue::whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()))
            : Ue::query();
    }
}
