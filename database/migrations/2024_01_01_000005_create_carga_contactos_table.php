<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCargaContactosTable extends Migration
{
    public function up()
    {
        Schema::create('carga_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_id')->constrained('cargas')->onDelete('cascade');
            $table->string('rut', 20)->nullable();
            $table->string('telefono', 9);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carga_contactos');
    }
}
