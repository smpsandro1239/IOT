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
        Schema::table('barriers', function (Blueprint $table) {
            $table->string('pending_command')->nullable()->after('is_active');
            $table->timestamp('command_last_updated_at')->nullable()->after('pending_command');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barriers', function (Blueprint $table) {
            $table->dropColumn('pending_command');
            $table->dropColumn('command_last_updated_at');
        });
    }
};
