<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditoiresTable extends Migration
{
    public function up()
    {
        Schema::create('auditoires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batiment_id')->nullable()->constrained('batiments')->nullOnDelete();
            $table->string('nom');
            $table->integer('capacite')->default(0);
            $table->string('type')->default('cours');
            $table->text('equipements')->nullable();
            $table->boolean('disponibilite')->default(true);
            $table->string('etat')->default('disponible');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoires');
    }
}
