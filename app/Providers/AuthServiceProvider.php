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
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Horaire::class => HorairePolicy::class,
        DemandeAuditoire::class => DemandeAuditoirePolicy::class,
        Disponibilite::class => DisponibilitePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }

            return null;
        });
    }
}
