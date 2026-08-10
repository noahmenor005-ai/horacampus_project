<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcUserTable extends Migration
{
    public function up()
    {
        Schema::create('ec_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ec_id')->constrained('ecs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['ec_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ec_user');
    }
}
