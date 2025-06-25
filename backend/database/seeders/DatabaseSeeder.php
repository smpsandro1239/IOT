<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // Primeiro os veículos, pois as permissões dependem deles
            VehicleSeeder::class,
            // Depois as entidades de localização/empresa
            CompanySeeder::class,
            SiteSeeder::class,
            BarrierSeeder::class,
            // Por último, as permissões que ligam veículos a entidades
            VehiclePermissionSeeder::class,
            // Outros seeders podem ser adicionados aqui no futuro
            // Example: AccessLogSeeder::class, // Se quiser popular logs de acesso
        ]);
    }
}
