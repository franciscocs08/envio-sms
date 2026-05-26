<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantillasSmsTable extends Migration
{
    public function up()
    {
        Schema::create('plantillas_sms', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('contenido', 150);
            $table->foreignId('cedente_id')->constrained('cedentes');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plantillas_sms');
    }
}
