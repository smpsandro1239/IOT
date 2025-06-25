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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_lora_id')->index(); // Mantém para referência rápida do veículo
            // $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete(); // Opcional: link direto para vehicle.id

            $table->foreignId('barrier_id')->constrained('barriers')->onDelete('cascade'); // Nova FK

            $table->timestamp('timestamp_event');
            $table->string('direction_detected');
            $table->string('base_station_id')->index()->comment('MAC da Placa Base que registrou o log'); // Mantido para referência original
            $table->json('sensor_reports')->nullable();
            $table->boolean('authorization_status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
