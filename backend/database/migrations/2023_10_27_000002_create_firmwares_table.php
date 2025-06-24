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
        Schema::create('firmwares', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('filename'); // Path to the stored firmware binary
            $table->text('description')->nullable();
            $table->unsignedInteger('size')->comment('File size in bytes');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps(); // created_at can serve as uploaded_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmwares');
    }
};
