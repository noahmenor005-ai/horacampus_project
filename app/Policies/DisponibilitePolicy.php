<?php

namespace App\Policies;

use App\Models\Disponibilite;
use App\Models\User;

class DisponibilitePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isEnseignant() || $user->isAdmin() || $user->isDecanat();
    }

    public function update(User $user, Disponibilite $disponibilite): bool
    {
        if ($user->isAdmin() || $user->isDecanat()) {
            return true;
        }

        return $user->id === $disponibilite->user_id
            && $disponibilite->statut === Disponibilite::STATUT_EN_ATTENTE;
    }

    public function delete(User $user, Disponibilite $disponibilite): bool
    {
        return $user->id === $disponibilite->user_id
            || $user->isAdmin()
            || $user->isDecanat();
    }

    public function valider(User $user): bool
    {
        return $user->isAdmin() || $user->isDecanat();
    }
}
