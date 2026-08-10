<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnseignantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isDecanat() || $user->isAdmin();
    }

    public function view(User $user, User $enseignant): bool
    {
        if (!$enseignant->isEnseignant()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isDecanat()) {
            return (int)$enseignant->faculte_id === (int)$user->faculte_id;
        }
        return $user->id === $enseignant->id;
    }

    public function create(User $user): bool
    {
        return $user->isDecanat();
    }

    public function update(User $user, User $enseignant): bool
    {
        if (!$enseignant->isEnseignant()) {
            return false;
        }
        if ($user->isAdmin()) {
            return false;
        }
        return $user->isDecanat() && (int)$enseignant->faculte_id === (int)$user->faculte_id;
    }

    public function delete(User $user, User $enseignant): bool
    {
        if (!$enseignant->isEnseignant()) {
            return false;
        }
        return $user->isDecanat() && (int)$enseignant->faculte_id === (int)$user->faculte_id;
    }
}
