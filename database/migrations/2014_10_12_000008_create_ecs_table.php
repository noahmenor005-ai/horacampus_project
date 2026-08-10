<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcsTable extends Migration
{
    public function up()
    {
        Schema::create('ecs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ue_id')->constrained('ues')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->integer('coefficient')->default(1);
            $table->integer('volume_horaire')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ecs');
    }
}
