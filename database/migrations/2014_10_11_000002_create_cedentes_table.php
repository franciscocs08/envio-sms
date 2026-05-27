<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCedentesTable extends Migration
{
    public function up()
    {
        Schema::create('cedentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('ops_token', 500)->nullable();
            $table->string('ops_from', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cedentes');
    }
}
