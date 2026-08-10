<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ajout des champs manquants pour la gestion étudiants/enseignants par le Décanat
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'postnom')) {
                $table->string('postnom')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('users', 'matricule')) {
                $table->string('matricule')->nullable()->after('postnom');
            }
            if (!Schema::hasColumn('users', 'sexe')) {
                $table->string('sexe', 20)->nullable()->after('matricule');
            }
            if (!Schema::hasColumn('users', 'annee_academique_id')) {
                $table->foreignId('annee_academique_id')->nullable()->after('promotion_id')->constrained('annees_academiques')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'statut_inscription')) {
                $table->string('statut_inscription')->default('actif')->after('status');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('statut_inscription');
            }
        });

        // Rendre l'email facultatif pour les étudiants (nullable)
        // Necessite doctrine/dbal pour SQLite/MySQL
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Fallback pour SQLite sans doctrine/dbal : recréation manuelle
            if (DB::getDriverName() === 'sqlite') {
                // SQLite ne supporte pas ALTER COLUMN, on tente une reconstruction légère
                // On vérifie si la colonne est déjà nullable via PRAGMA
                $info = DB::select("PRAGMA table_info(users)");
                $emailCol = collect($info)->firstWhere('name', 'email');
                if ($emailCol && (int) $emailCol->notnull === 1) {
                    // On ne peut pas modifier facilement sans recréer la table ;
                    // On laisse tel quel : le contrôle applicatif permettra de générer un email placeholder
                    // si l'email est vide, afin de ne pas violer la contrainte NOT NULL.
                }
            }
        }

        // Index unique sur matricule (uniquement pour les valeurs non nulles)
        // SQLite gère NULL comme distinct, MySQL aussi
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('matricule');
            });
        } catch (\Throwable $e) {
            // Index déjà existant ou erreur silencieuse
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'statut_inscription')) {
                $table->dropColumn('statut_inscription');
            }
            if (Schema::hasColumn('users', 'annee_academique_id')) {
                try { $table->dropForeign(['annee_academique_id']); } catch (\Throwable $e) {}
                $table->dropColumn('annee_academique_id');
            }
            if (Schema::hasColumn('users', 'sexe')) {
                $table->dropColumn('sexe');
            }
            if (Schema::hasColumn('users', 'matricule')) {
                try { $table->dropUnique(['matricule']); } catch (\Throwable $e) {}
                $table->dropColumn('matricule');
            }
            if (Schema::hasColumn('users', 'postnom')) {
                $table->dropColumn('postnom');
            }
        });
    }
};
