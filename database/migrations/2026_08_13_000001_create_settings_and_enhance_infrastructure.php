<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('cle')->unique();
                $table->text('valeur')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('batiments', function (Blueprint $table) {
            if (!Schema::hasColumn('batiments', 'localisation')) {
                $table->string('localisation')->nullable();
            }
            if (!Schema::hasColumn('batiments', 'nombre_etages')) {
                $table->unsignedTinyInteger('nombre_etages')->nullable();
            }
            if (!Schema::hasColumn('batiments', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('batiments', 'statut')) {
                $table->string('statut')->default('actif');
            }
        });

        Schema::table('auditoires', function (Blueprint $table) {
            if (!Schema::hasColumn('auditoires', 'numero')) {
                $table->string('numero')->nullable();
            }
            if (!Schema::hasColumn('auditoires', 'statut')) {
                $table->string('statut')->default('actif');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
