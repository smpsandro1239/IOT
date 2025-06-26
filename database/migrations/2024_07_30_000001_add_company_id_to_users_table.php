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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            // onDelete('set null') means if a company is deleted, the company_id for the user becomes null.
            // Alternatively, onDelete('restrict') would prevent company deletion if users are assigned.
            // Consider the desired behavior. For now, 'set null' allows company deletion.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first if it was explicitly named, otherwise Laravel handles it by convention.
            // For safety, let's assume it might need explicit dropping if column name differs from table name.
            // $table->dropForeign(['company_id']); // Or $table->dropForeign('users_company_id_foreign');
            $table->dropConstrainedForeignId('company_id'); // Simpler way if convention is followed
        });
    }
};
