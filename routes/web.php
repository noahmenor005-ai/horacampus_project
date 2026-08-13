<?php

use App\Http\Controllers\AnneeAcademiqueController;
use App\Http\Controllers\AuditoireController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\CascadeController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecanatDashboardController;
use App\Http\Controllers\DemandeAuditoireController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\DomaineController;
use App\Http\Controllers\EcController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\FaculteController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HoraireController;
use App\Http\Controllers\LmdCrudController;
use App\Http\Controllers\MentionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SemestreController;
use App\Http\Controllers\UeController;
use App\Http\Controllers\UserController;
use App\Models\Horaire;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    Route::get('mot-de-passe/oublie', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('mot-de-passe/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('mot-de-passe/reset/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('mot-de-passe/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('api/domaines/{faculte?}', [CascadeController::class, 'domaines'])->name('api.domaines');
    Route::get('api/filieres/{domaine}', [CascadeController::class, 'filieres'])->name('api.filieres');
    Route::get('api/mentions/{filiere}', [CascadeController::class, 'mentions'])->name('api.mentions');
    Route::get('api/promotions/{mention}', [CascadeController::class, 'promotions'])->name('api.promotions');
    Route::get('api/semestres/{annee?}', [CascadeController::class, 'semestres'])->name('api.semestres');
    Route::get('api/ues', [CascadeController::class, 'ues'])->name('api.ues');
    Route::get('api/ecs/{ue}', [CascadeController::class, 'ecs'])->name('api.ecs');
    Route::get('api/enseignants', [CascadeController::class, 'enseignants'])->name('api.enseignants');
    Route::get('api/annees', [CascadeController::class, 'annees'])->name('api.annees');

    Route::get('domaines/{faculte}', [AuthController::class, 'domainesParFaculte'])->name('register.domaines');
    Route::get('filieres/{domaine}', [AuthController::class, 'filieresParDomaine'])->name('register.filieres');
    Route::get('mentions/{filiere}', [AuthController::class, 'mentionsParFiliere'])->name('register.mentions');
    Route::get('promotions/{mention}', [AuthController::class, 'promotionsParMention'])->name('register.promotions');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/mot-de-passe', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/lire', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/tout-lire', [\App\Http\Controllers\NotificationController::class, 'markAll'])->name('notifications.read-all');

    Route::get('horaires', [HoraireController::class, 'index'])->name('horaires.index');
    Route::get('horaires-impression', [HoraireController::class, 'print'])->name('horaires.print');
    Route::get('horaires-export', [HoraireController::class, 'export'])->name('horaires.export');
    Route::get('horaires-pdf', [HoraireController::class, 'pdf'])->name('horaires.pdf');
    Route::middleware('can:create,' . Horaire::class)->group(function () {
        Route::get('horaires/creer', [HoraireController::class, 'create'])->name('horaires.create');
        Route::post('horaires', [HoraireController::class, 'store'])->name('horaires.store');
        Route::post('horaires/{horaire}/demander-salle', [HoraireController::class, 'demanderSalle'])->name('horaires.demander-salle');
    });
    Route::get('horaires/{horaire}', [HoraireController::class, 'show'])->name('horaires.show');
    Route::middleware('can:update,horaire')->group(function () {
        Route::get('horaires/{horaire}/modifier', [HoraireController::class, 'edit'])->name('horaires.edit');
        Route::put('horaires/{horaire}', [HoraireController::class, 'update'])->name('horaires.update');
    });
    Route::middleware('can:delete,horaire')->group(function () {
        Route::delete('horaires/{horaire}', [HoraireController::class, 'destroy'])->name('horaires.destroy');
    });

    Route::resource('demandes', DemandeAuditoireController::class);
    Route::get('demandes/{demande}/salles', [DemandeAuditoireController::class, 'sallesDisponibles'])->name('demandes.salles');
    Route::middleware('role:admin')->group(function () {
        Route::patch('demandes/{demande}/statut', [DemandeAuditoireController::class, 'updateStatus'])->name('demandes.status');
    });

    Route::resource('disponibilites', DisponibiliteController::class);
    Route::middleware('role:admin,decanat')->group(function () {
        Route::patch('disponibilites/{disponibilite}/statut', [DisponibiliteController::class, 'updateStatus'])->name('disponibilites.status');
    });

    Route::middleware('role:decanat')->prefix('decanat')->name('decanat.')->group(function () {
        Route::get('dashboard', [DecanatDashboardController::class, 'index'])->name('dashboard');
        Route::get('faculte', [DecanatDashboardController::class, 'faculte'])->name('faculte.show');

        Route::resource('domaines', DomaineController::class);
        Route::patch('domaines/{domaine}/toggle', [DomaineController::class, 'toggle'])->name('domaines.toggle');

        Route::resource('filieres', FiliereController::class);
        Route::patch('filieres/{filiere}/toggle', [FiliereController::class, 'toggle'])->name('filieres.toggle');

        Route::resource('mentions', MentionController::class);
        Route::patch('mentions/{mention}/toggle', [MentionController::class, 'toggle'])->name('mentions.toggle');

        Route::resource('promotions', PromotionController::class);
        Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');

        Route::resource('annees-academiques', AnneeAcademiqueController::class)->parameters(['annees-academiques' => 'annee']);
        Route::patch('annees-academiques/{annee}/toggle', [AnneeAcademiqueController::class, 'toggle'])->name('annees-academiques.toggle');

        Route::resource('semestres', SemestreController::class);
        Route::patch('semestres/{semestre}/toggle', [SemestreController::class, 'toggle'])->name('semestres.toggle');

        Route::resource('ues', UeController::class)->parameters(['ues' => 'ue']);
        Route::patch('ues/{ue}/toggle', [UeController::class, 'toggle'])->name('ues.toggle');

        Route::resource('ecs', EcController::class)->parameters(['ecs' => 'ec']);
        Route::patch('ecs/{ec}/toggle', [EcController::class, 'toggle'])->name('ecs.toggle');

        Route::get('enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
        Route::get('enseignants/creer', [EnseignantController::class, 'create'])->name('enseignants.create');
        Route::post('enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
        Route::get('enseignants/{enseignant}', [EnseignantController::class, 'show'])->name('enseignants.show');
        Route::get('enseignants/{enseignant}/modifier', [EnseignantController::class, 'edit'])->name('enseignants.edit');
        Route::put('enseignants/{enseignant}', [EnseignantController::class, 'update'])->name('enseignants.update');
        Route::delete('enseignants/{enseignant}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy');
        Route::patch('enseignants/{enseignant}/desactiver', [EnseignantController::class, 'desactiver'])->name('enseignants.desactiver');
        Route::patch('enseignants/{enseignant}/reactiver', [EnseignantController::class, 'reactiver'])->name('enseignants.reactiver');

        Route::get('etudiants', [EtudiantController::class, 'index'])->name('etudiants.index');
        Route::get('etudiants/creer', [EtudiantController::class, 'create'])->name('etudiants.create');
        Route::post('etudiants', [EtudiantController::class, 'store'])->name('etudiants.store');
        Route::get('etudiants/{etudiant}', [EtudiantController::class, 'show'])->name('etudiants.show');
        Route::get('etudiants/{etudiant}/modifier', [EtudiantController::class, 'edit'])->name('etudiants.edit');
        Route::put('etudiants/{etudiant}', [EtudiantController::class, 'update'])->name('etudiants.update');
        Route::delete('etudiants/{etudiant}', [EtudiantController::class, 'destroy'])->name('etudiants.destroy');
        Route::patch('etudiants/{etudiant}/desactiver', [EtudiantController::class, 'desactiver'])->name('etudiants.desactiver');
        Route::patch('etudiants/{etudiant}/reactiver', [EtudiantController::class, 'reactiver'])->name('etudiants.reactiver');

        Route::get('disponibilites', [DisponibiliteController::class, 'index'])->name('disponibilites.index');
        Route::get('disponibilites/create', [DisponibiliteController::class, 'create'])->name('disponibilites.create');
        Route::post('disponibilites', [DisponibiliteController::class, 'store'])->name('disponibilites.store');
        Route::get('disponibilites/{disponibilite}/edit', [DisponibiliteController::class, 'edit'])->name('disponibilites.edit');
        Route::put('disponibilites/{disponibilite}', [DisponibiliteController::class, 'update'])->name('disponibilites.update');
        Route::delete('disponibilites/{disponibilite}', [DisponibiliteController::class, 'destroy'])->name('disponibilites.destroy');

        Route::get('horaires', [HoraireController::class, 'index'])->name('horaires.index');
        Route::get('horaires/creer', [HoraireController::class, 'create'])->name('horaires.create');
        Route::post('horaires', [HoraireController::class, 'store'])->name('horaires.store');
        Route::get('horaires/{horaire}', [HoraireController::class, 'show'])->name('horaires.show')->whereNumber('horaire');
        Route::get('horaires/{horaire}/modifier', [HoraireController::class, 'edit'])->name('horaires.edit');
        Route::put('horaires/{horaire}', [HoraireController::class, 'update'])->name('horaires.update');
        Route::delete('horaires/{horaire}', [HoraireController::class, 'destroy'])->name('horaires.destroy');
        Route::post('horaires/{horaire}/demander-salle', [HoraireController::class, 'demanderSalle'])->name('horaires.demander-salle');

        Route::get('demandes-salles', [DemandeAuditoireController::class, 'index'])->name('demandes-salles.index');
        Route::get('demandes-salles/create', [DemandeAuditoireController::class, 'create'])->name('demandes-salles.create');
        Route::post('demandes-salles', [DemandeAuditoireController::class, 'store'])->name('demandes-salles.store');
        Route::get('demandes-salles/{demande}', [DemandeAuditoireController::class, 'show'])->name('demandes-salles.show');
        Route::get('demandes-salles/{demande}/edit', [DemandeAuditoireController::class, 'edit'])->name('demandes-salles.edit');
        Route::put('demandes-salles/{demande}', [DemandeAuditoireController::class, 'update'])->name('demandes-salles.update');
        Route::delete('demandes-salles/{demande}', [DemandeAuditoireController::class, 'destroy'])->name('demandes-salles.destroy');
    });

    Route::middleware('role:decanat')->group(function () {
        Route::get('etudiants', [EtudiantController::class, 'index'])->name('etudiants.index');
        Route::get('etudiants/creer', [EtudiantController::class, 'create'])->name('etudiants.create');
        Route::post('etudiants', [EtudiantController::class, 'store'])->name('etudiants.store');
        Route::get('etudiants/{etudiant}', [EtudiantController::class, 'show'])->name('etudiants.show');
        Route::get('etudiants/{etudiant}/modifier', [EtudiantController::class, 'edit'])->name('etudiants.edit');
        Route::put('etudiants/{etudiant}', [EtudiantController::class, 'update'])->name('etudiants.update');
        Route::delete('etudiants/{etudiant}', [EtudiantController::class, 'destroy'])->name('etudiants.destroy');
        Route::patch('etudiants/{etudiant}/desactiver', [EtudiantController::class, 'desactiver'])->name('etudiants.desactiver');
        Route::patch('etudiants/{etudiant}/reactiver', [EtudiantController::class, 'reactiver'])->name('etudiants.reactiver');

        Route::get('enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
        Route::get('enseignants/creer', [EnseignantController::class, 'create'])->name('enseignants.create');
        Route::post('enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
        Route::get('enseignants/{enseignant}', [EnseignantController::class, 'show'])->name('enseignants.show');
        Route::get('enseignants/{enseignant}/modifier', [EnseignantController::class, 'edit'])->name('enseignants.edit');
        Route::put('enseignants/{enseignant}', [EnseignantController::class, 'update'])->name('enseignants.update');
        Route::delete('enseignants/{enseignant}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy');
        Route::patch('enseignants/{enseignant}/desactiver', [EnseignantController::class, 'desactiver'])->name('enseignants.desactiver');
        Route::patch('enseignants/{enseignant}/reactiver', [EnseignantController::class, 'reactiver'])->name('enseignants.reactiver');
    });

    Route::middleware('role:admin,decanat')->group(function () {
        Route::resource('promotions', PromotionController::class)->except('show');
        Route::resource('cours', CoursController::class)->except('show');

        Route::get('gestion/{resource}', [LmdCrudController::class, 'index'])->name('lmd.index');
        Route::get('gestion/{resource}/ajouter', [LmdCrudController::class, 'create'])->name('lmd.create');
        Route::post('gestion/{resource}', [LmdCrudController::class, 'store'])->name('lmd.store');
        Route::get('gestion/{resource}/{id}/modifier', [LmdCrudController::class, 'edit'])->name('lmd.edit');
        Route::put('gestion/{resource}/{id}', [LmdCrudController::class, 'update'])->name('lmd.update');
        Route::delete('gestion/{resource}/{id}', [LmdCrudController::class, 'destroy'])->name('lmd.destroy');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('facultes', FaculteController::class)->except('show');
        Route::resource('batiments', BatimentController::class)->except('show');
        Route::resource('auditoires', AuditoireController::class)->except('show');

        Route::get('utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('utilisateurs/creer', [UserController::class, 'create'])->name('users.create');
        Route::post('utilisateurs', [UserController::class, 'store'])->name('users.store');
        Route::get('utilisateurs/{user}/modifier', [UserController::class, 'edit'])->name('users.edit');
        Route::put('utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('utilisateurs/{user}/statut', [UserController::class, 'updateStatus'])->name('users.status');
        Route::delete('utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('decanats', [UserController::class, 'decanats'])->name('decanats.index');
        Route::get('attributions', [\App\Http\Controllers\AttributionController::class, 'index'])->name('attributions.index');
        Route::get('parametres', [\App\Http\Controllers\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('parametres', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('rapports/pdf', [RapportController::class, 'pdf'])->name('rapports.pdf');
    });

    Route::get('acces-refuse', function () {
        abort(403, 'Accès réservé au Décanat.');
    })->name('acces.refuse');
});
