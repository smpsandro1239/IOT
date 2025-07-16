<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelemetriaTable extends Migration
{
    public function up()
    {
        Schema::create('telemetria', function (Blueprint $table) {
            $table->id();
            $table->string('mac', 12);
            $table->string('direcao', 2)->nullable(); // NS ou SN
            $table->timestamp('datahora');
            $table->string('status', 20); // AUTORIZADO, NEGADO, JSON INVÁLIDO
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('telemetria');
    }
}
