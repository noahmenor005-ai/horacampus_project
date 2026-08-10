<?php

use App\Http\Controllers\AuditoireController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeAuditoireController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\FaculteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HoraireController;
use App\Http\Controllers\LmdCrudController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\UserController;
use App\Models\Horaire;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentification publique : uniquement login, pas d'inscription publique
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    Route::get('mot-de-passe/oublie', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('mot-de-passe/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('mot-de-passe/reset/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('mot-de-passe/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Routes pour les selects dépendants
Route::middleware('auth')->group(function () {
    Route::get('api/domaines/{faculte}', [AuthController::class, 'domainesParFaculte'])->name('api.domaines');
    Route::get('api/filieres/{domaine}', [AuthController::class, 'filieresParDomaine'])->name('api.filieres');
    Route::get('api/mentions/{filiere}', [AuthController::class, 'mentionsParFiliere'])->name('api.mentions');
    Route::get('api/promotions/{mention}', [AuthController::class, 'promotionsParMention'])->name('api.promotions');
    // Compatibilité anciennes routes register
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

    // Horaires : consultation pour tous, édition pour admin / décanat uniquement
    Route::get('horaires', [HoraireController::class, 'index'])->name('horaires.index');
    Route::get('horaires-impression', [HoraireController::class, 'print'])->name('horaires.print');
    Route::get('horaires-export', [HoraireController::class, 'export'])->name('horaires.export');
    Route::get('horaires-pdf', [HoraireController::class, 'pdf'])->name('horaires.pdf');
    Route::middleware('can:create,' . Horaire::class)->group(function () {
        Route::get('horaires/creer', [HoraireController::class, 'create'])->name('horaires.create');
        Route::post('horaires', [HoraireController::class, 'store'])->name('horaires.store');
    });
    Route::middleware('can:update,horaire')->group(function () {
        Route::get('horaires/{horaire}/modifier', [HoraireController::class, 'edit'])->name('horaires.edit');
        Route::put('horaires/{horaire}', [HoraireController::class, 'update'])->name('horaires.update');
    });
    Route::middleware('can:delete,horaire')->group(function () {
        Route::delete('horaires/{horaire}', [HoraireController::class, 'destroy'])->name('horaires.destroy');
    });

    // Demandes de salles
    Route::resource('demandes', DemandeAuditoireController::class);
    Route::get('demandes/{demande}/salles', [DemandeAuditoireController::class, 'sallesDisponibles'])->name('demandes.salles');
    Route::middleware('role:admin')->group(function () {
        Route::patch('demandes/{demande}/statut', [DemandeAuditoireController::class, 'updateStatus'])->name('demandes.status');
    });

    // Disponibilités des enseignants
    Route::resource('disponibilites', DisponibiliteController::class);
    Route::middleware('role:admin,decanat')->group(function () {
        Route::patch('disponibilites/{disponibilite}/statut', [DisponibiliteController::class, 'updateStatus'])->name('disponibilites.status');
    });

    // ============ GESTION DECANAT : Étudiants & Enseignants (réservé au Décanat uniquement) ============
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

        // Aliases pour la spec qui mentionne /decanat/etudiants (doit retourner 403 pour étudiant, 200 pour décanat)
        Route::get('decanat/etudiants', [EtudiantController::class, 'index'])->name('decanat.etudiants.index');
        Route::get('decanat/etudiants/creer', [EtudiantController::class, 'create'])->name('decanat.etudiants.create');
        Route::get('decanat/enseignants', [EnseignantController::class, 'index'])->name('decanat.enseignants.index');
    });

    // Gestion académique restante (admin + décanat)
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

    // Administration (admin uniquement)
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

        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('rapports/pdf', [RapportController::class, 'pdf'])->name('rapports.pdf');
    });

    // Bloquer l'accès aux routes du Décanat pour les étudiants même en direct URL (double sécurité)
    Route::get('acces-refuse', function () {
        abort(403, 'Accès réservé au Décanat.');
    })->name('acces.refuse');
});
