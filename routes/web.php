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

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('domaines/{faculte}', [AuthController::class, 'domainesParFaculte'])->name('register.domaines');
    Route::get('filieres/{domaine}', [AuthController::class, 'filieresParDomaine'])->name('register.filieres');
    Route::get('mentions/{filiere}', [AuthController::class, 'mentionsParFiliere'])->name('register.mentions');
    Route::get('promotions/{mention}', [AuthController::class, 'promotionsParMention'])->name('register.promotions');

    Route::get('mot-de-passe/oublie', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('mot-de-passe/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('mot-de-passe/reset/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('mot-de-passe/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/mot-de-passe', [ProfileController::class, 'password'])->name('profile.password');

    // Horaires : consultation pour tous, édition pour admin / décanat
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

    // Gestion académique (admin + décanat)
    Route::middleware('role:admin,decanat')->group(function () {
        Route::resource('promotions', PromotionController::class)->except('show');
        Route::resource('cours', CoursController::class)->except('show');
        Route::resource('enseignants', EnseignantController::class)->except('show');
        Route::resource('etudiants', EtudiantController::class)->except('show');

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
});
