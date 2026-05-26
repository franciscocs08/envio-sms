<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnvioDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('envio_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('envios')->onDelete('cascade');
            $table->string('telefono', 9);
            $table->string('rut', 20)->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->string('ops_sms_id', 100)->nullable();
            $table->text('respuesta_gateway')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('envio_detalles');
    }
}
