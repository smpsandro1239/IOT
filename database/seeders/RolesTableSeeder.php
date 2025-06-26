<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset table if needed, or use updateOrCreate
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks for certain DBs
        // Role::truncate(); // Careful with truncate in production or if other tables depend on it without cascade
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable

        $roles = [
            [
                'name' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'User with full access to all system features and data.'
            ],
            [
                'name' => 'company-admin',
                'display_name' => 'Company Administrator',
                'description' => 'User managing a specific company, its sites, barriers, vehicles, and users.'
            ],
            [
                'name' => 'site-manager',
                'display_name' => 'Site Manager',
                'description' => 'User managing specific sites within a company, including barriers and vehicle permissions for those sites.'
            ],
            // Add a basic user role if needed in the future, e.g., for viewing only
            // [
            //     'name' => 'base-user',
            //     'display_name' => 'Base User',
            //     'description' => 'User with basic access, e.g., view own profile.'
            // ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}
