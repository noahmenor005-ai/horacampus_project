<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $this->addColumnIfMissing('domaines', 'actif', function (Blueprint $table) {
            $table->boolean('actif')->default(true);
        });

        $this->addColumnIfMissing('filieres', 'actif', function (Blueprint $table) {
            $table->boolean('actif')->default(true);
        });

        $this->addColumnIfMissing('mentions', 'actif', function (Blueprint $table) {
            $table->boolean('actif')->default(true);
        });

        $this->addColumnIfMissing('promotions', 'actif', function (Blueprint $table) {
            $table->boolean('actif')->default(true);
        });

        Schema::table('ues', function (Blueprint $table) {
            if (!Schema::hasColumn('ues', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('ues', 'mention_id')) {
                $table->foreignId('mention_id')->nullable()->constrained('mentions')->nullOnDelete();
            }
            if (!Schema::hasColumn('ues', 'annee_academique_id')) {
                $table->foreignId('annee_academique_id')->nullable()->constrained('annees_academiques')->nullOnDelete();
            }
            if (!Schema::hasColumn('ues', 'statut')) {
                $table->string('statut')->default('actif');
            }
        });

        Schema::table('ecs', function (Blueprint $table) {
            if (!Schema::hasColumn('ecs', 'credit')) {
                $table->integer('credit')->nullable();
            }
            if (!Schema::hasColumn('ecs', 'enseignant_id')) {
                $table->foreignId('enseignant_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ecs', 'statut')) {
                $table->string('statut')->default('actif');
            }
        });

        Schema::table('disponibilites', function (Blueprint $table) {
            if (!Schema::hasColumn('disponibilites', 'annee_academique_id')) {
                $table->foreignId('annee_academique_id')->nullable()->constrained('annees_academiques')->nullOnDelete();
            }
        });

        Schema::table('horaires', function (Blueprint $table) {
            if (!Schema::hasColumn('horaires', 'annee_academique_id')) {
                $table->foreignId('annee_academique_id')->nullable()->constrained('annees_academiques')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'domaine_id')) {
                $table->foreignId('domaine_id')->nullable()->constrained('domaines')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'filiere_id')) {
                $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'mention_id')) {
                $table->foreignId('mention_id')->nullable()->constrained('mentions')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'ue_id')) {
                $table->foreignId('ue_id')->nullable()->constrained('ues')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'ec_id')) {
                $table->foreignId('ec_id')->nullable()->constrained('ecs')->nullOnDelete();
            }
            if (!Schema::hasColumn('horaires', 'jour')) {
                $table->string('jour')->nullable();
            }
            if (!Schema::hasColumn('horaires', 'effectif_attendu')) {
                $table->integer('effectif_attendu')->nullable();
            }
        });

        $this->makeAuditoireNullable();

        Schema::table('demandes_auditoire', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes_auditoire', 'horaire_id')) {
                $table->foreignId('horaire_id')->nullable()->constrained('horaires')->nullOnDelete();
            }
            if (!Schema::hasColumn('demandes_auditoire', 'ec_id')) {
                $table->foreignId('ec_id')->nullable()->constrained('ecs')->nullOnDelete();
            }
            if (!Schema::hasColumn('demandes_auditoire', 'commentaire')) {
                $table->text('commentaire')->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'specialite')) {
                $table->string('specialite')->nullable();
            }
            if (!Schema::hasColumn('users', 'grade')) {
                $table->string('grade')->nullable();
            }
        });
    }

    public function down()
    {
        // Colonnes ajoutées : conservées en down pour éviter la perte de données SQLite.
    }

    private function addColumnIfMissing(string $table, string $column, callable $definition): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, $definition);
    }

    private function makeAuditoireNullable(): void
    {
        if (!Schema::hasColumn('horaires', 'auditoire_id')) {
            return;
        }

        try {
            Schema::table('horaires', function (Blueprint $table) {
                $table->unsignedBigInteger('auditoire_id')->nullable()->change();
            });
        } catch (\Throwable $e) {
            if (DB::getDriverName() !== 'sqlite') {
                return;
            }

            // SQLite : si change() échoue, on laisse la contrainte et on utilisera une salle placeholder.
        }
    }
};
