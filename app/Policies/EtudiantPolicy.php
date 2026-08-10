<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EtudiantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isDecanat() || $user->isAdmin();
    }

    public function view(User $user, User $etudiant): bool
    {
        if (!$etudiant->isEtudiant()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isDecanat()) {
            return (int)$etudiant->faculte_id === (int)$user->faculte_id;
        }
        // Un étudiant ne peut voir que son propre profil via cette policy? On restreint
        return $user->id === $etudiant->id;
    }

    public function create(User $user): bool
    {
        return $user->isDecanat();
    }

    public function update(User $user, User $etudiant): bool
    {
        if (!$etudiant->isEtudiant()) {
            return false;
        }
        if ($user->isAdmin()) {
            return false; // Admin ne crée pas directement les étudiants (spec 5)
        }
        return $user->isDecanat() && (int)$etudiant->faculte_id === (int)$user->faculte_id;
    }

    public function delete(User $user, User $etudiant): bool
    {
        // Désactivation / suppression réservée au Décanat de la faculté
        if (!$etudiant->isEtudiant()) {
            return false;
        }
        return $user->isDecanat() && (int)$etudiant->faculte_id === (int)$user->faculte_id;
    }

    public function desactiver(User $user, User $etudiant): bool
    {
        return $this->update($user, $etudiant);
    }
}
