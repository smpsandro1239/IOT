<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('telemetria', function (Blueprint $table) {
      $table->id();
      $table->string('mac', 12);
      $table->string('direcao', 2)->nullable();
      $table->dateTime('datahora');
      $table->string('status', 20);
      $table->timestamps();

      // Index for faster queries
      $table->index('mac');
      $table->index('datahora');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('telemetria');
  }
};
