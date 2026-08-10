<?php

namespace App\Providers;

use App\Models\DemandeAuditoire;
use App\Models\Disponibilite;
use App\Models\Horaire;
use App\Models\User;
use App\Policies\DemandeAuditoirePolicy;
use App\Policies\DisponibilitePolicy;
use App\Policies\HorairePolicy;
use App\Policies\UserPolicy;
use App\Policies\EtudiantPolicy;
use App\Policies\EnseignantPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Horaire::class => HorairePolicy::class,
        DemandeAuditoire::class => DemandeAuditoirePolicy::class,
        Disponibilite::class => DisponibilitePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability, $arguments) {
            if (!$user->isAdmin()) {
                return null;
            }
            // Admin est super-admin SAUF pour la gestion des étudiants/enseignants
            // qui est réservée strictement au Décanat (spec section 5)
            if (!empty($arguments)) {
                $target = $arguments[0] ?? null;
                // Si la cible est un User étudiant/enseignant, ne pas bypass
                if ($target instanceof User && in_array($target->role, [User::ROLE_ETUDIANT, User::ROLE_ENSEIGNANT], true)) {
                    return null;
                }
                // Si l'ability concerne la création de User avec role etudiant/enseignant
                if ($ability === 'create' && $target === User::class) {
                    // On laisse la policy décider; mais on ne bypass pas pour creation etudiant/enseignant
                    // Le contrôle sera fait dans les contrôleurs via middleware role:decanat
                    return null;
                }
            }
            // Pour tout le reste, admin bypass
            return true;
        });
    }
}
