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
        Schema::create('barriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name');
            $table->string('base_station_mac_address', 17)->unique()->nullable()->comment('MAC da Placa Base ESP32. Formato XX:XX:XX:XX:XX:XX');
            // Opcional: adicionar um campo 'type' se quisermos diferenciar tipos de barreiras no futuro
            // $table->string('type')->default('bidirectional_point'); // ex: 'entry_only', 'exit_only', 'bidirectional_point'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'name']); // Nome da barreira deve ser único por site
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barriers');
    }
};
