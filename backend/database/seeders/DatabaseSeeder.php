<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash; // Import Hash

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the RBAC seeders first
        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            PermissionRoleTableSeeder::class,
        ]);

        // Create a Super Admin User
        $superAdminRole = Role::where('name', 'super-admin')->first();

        if ($superAdminRole) {
            $superAdminUser = User::firstOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'), // Use a secure password, this is just an example
                    'email_verified_at' => now(),
                ]
            );
            $superAdminUser->assignRole($superAdminRole); // AssignRole method is in User model

            $this->command->info('Super Admin user created and role assigned.');

            // You can create other default users here if needed
            // e.g., a Company Admin for a test company

        } else {
            $this->command->error('Super Admin role not found. Cannot create Super Admin user.');
        }


        // Example: Create some companies, sites, and barriers if needed for testing
        // Ensure these seeders are created and called if you uncomment them.
        // $this->call([
        //     CompanySeeder::class, // You would need to create this seeder
        //     SiteSeeder::class,    // You would need to create this seeder
        //     BarrierSeeder::class, // You would need to create this seeder
        // ]);

        // \App\Models\User::factory(10)->create(); // Default user factory

        $this->call([
            // Primeiro os veículos, pois as permissões dependem deles
            VehicleSeeder::class,
            // Depois as entidades de localização/empresa
            CompanySeeder::class,
            SiteSeeder::class,
            BarrierSeeder::class,
            // Por último, as permissões que ligam veículos a entidades
            VehiclePermissionSeeder::class,
            MacsAutorizadosSeeder::class,
            // Outros seeders podem ser adicionados aqui no futuro
            // Example: AccessLogSeeder::class, // Se quiser popular logs de acesso
        ]);
    }
}
