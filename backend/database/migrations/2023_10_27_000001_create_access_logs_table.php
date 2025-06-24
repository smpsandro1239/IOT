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
            $table->string('vehicle_lora_id')->index();
            $table->timestamp('timestamp_event'); // Using timestamp for flexibility with timezones
            $table->string('direction_detected'); // e.g., 'north_south', 'south_north', 'undefined'
            $table->string('base_station_id')->index();
            $table->json('sensor_reports')->nullable(); // Array of objects: {sensor_id, rssi, timestamp_sensor}
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
