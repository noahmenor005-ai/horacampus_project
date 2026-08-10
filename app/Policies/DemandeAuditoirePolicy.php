<?php

namespace App\Policies;

use App\Models\DemandeAuditoire;
use App\Models\User;

class DemandeAuditoirePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDecanat();
    }

    public function update(User $user, DemandeAuditoire $demande): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isDecanat()
            && $demande->created_by === $user->id
            && $demande->estModifiable();
    }

    public function delete(User $user, DemandeAuditoire $demande): bool
    {
        return $user->isAdmin()
            || ($user->isDecanat() && $demande->created_by === $user->id && $demande->estModifiable());
    }

    public function attribuerSalle(User $user): bool
    {
        return $user->isAdmin();
    }
}
