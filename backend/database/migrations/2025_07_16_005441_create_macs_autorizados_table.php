<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMacsAutorizadosTable extends Migration
{
    public function up()
    {
        Schema::create('macs_autorizados', function (Blueprint $table) {
            $table->id();
            $table->string('mac', 12)->unique();
            $table->string('placa', 10);
            $table->timestamp('data_adicao')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('macs_autorizados');
    }
}
