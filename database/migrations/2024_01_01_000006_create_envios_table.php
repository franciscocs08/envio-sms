<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnviosTable extends Migration
{
    public function up()
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->foreignId('plantilla_id')->constrained('plantillas_sms');
            $table->foreignId('carga_id')->constrained('cargas');
            $table->foreignId('cedente_id')->constrained('cedentes');
            $table->string('estado', 20)->default('pendiente');
            $table->integer('total')->default(0);
            $table->integer('enviados')->default(0);
            $table->integer('fallidos')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('envios');
    }
}
