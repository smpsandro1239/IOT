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
    Schema::create('macs_autorizados', function (Blueprint $table) {
      $table->id();
      $table->string('mac', 12)->unique();
      $table->string('placa', 10);
      $table->dateTime('data_adicao');
      $table->timestamps();

      // Index for faster queries
      $table->index('mac');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('macs_autorizados');
  }
};
