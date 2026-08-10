<?php

namespace App\Policies;

use App\Models\Horaire;
use App\Models\User;

class HorairePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDecanat();
    }

    public function update(User $user, Horaire $horaire): bool
    {
        return $user->isAdmin()
            || ($user->isDecanat() && $horaire->promotion->faculte()?->id === $user->faculte_id);
    }

    public function delete(User $user, Horaire $horaire): bool
    {
        return $user->isAdmin()
            || ($user->isDecanat() && $horaire->promotion->faculte()?->id === $user->faculte_id);
    }
}
