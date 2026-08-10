<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('etudiants')) {
            Schema::create('etudiants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('matricule')->unique();
                $table->string('nom');
                $table->string('postnom')->nullable();
                $table->string('prenom');
                $table->string('sexe', 20)->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->foreignId('faculte_id')->nullable()->constrained('facultes')->nullOnDelete();
                $table->foreignId('domaine_id')->nullable()->constrained('domaines')->nullOnDelete();
                $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
                $table->foreignId('mention_id')->nullable()->constrained('mentions')->nullOnDelete();
                $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
                $table->foreignId('annee_academique_id')->nullable()->constrained('annees_academiques')->nullOnDelete();
                $table->string('statut')->default('actif');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('enseignants')) {
            Schema::create('enseignants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('matricule')->nullable()->unique();
                $table->string('nom');
                $table->string('postnom')->nullable();
                $table->string('prenom');
                $table->string('sexe', 20)->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable()->unique();
                $table->foreignId('faculte_id')->nullable()->constrained('facultes')->nullOnDelete();
                $table->string('specialite')->nullable();
                $table->string('grade')->nullable();
                $table->string('statut')->default('actif');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('enseignants');
        Schema::dropIfExists('etudiants');
    }
};
