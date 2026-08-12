<?php

namespace App\Policies;

use App\Models\Horaire;
use App\Models\User;
use App\Support\FacultyGuard;

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
        if ($user->isAdmin()) {
            return true;
        }

        $facultyId = FacultyGuard::facultyIdOf($horaire);

        return $user->isDecanat() && $facultyId && (int) $facultyId === (int) $user->faculte_id;
    }

    public function delete(User $user, Horaire $horaire): bool
    {
        return $this->update($user, $horaire);
    }
}
