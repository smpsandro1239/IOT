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
        Schema::create('vehicle_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->morphs('permissible'); // Cria permissible_id (unsignedBigInteger) e permissible_type (string)
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Índice único para evitar permissões duplicadas
            $table->unique(['vehicle_id', 'permissible_id', 'permissible_type'], 'vehicle_permissible_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_permissions');
    }
};
