<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Empresa Exemplo Alpha',
            'contact_email' => 'contact@alpha.example.com',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Cliente Beta Soluções',
            'contact_email' => 'admin@beta.example.com',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Empresa Gamma (Inativa)',
            'contact_email' => 'support@gamma.example.com',
            'is_active' => false,
        ]);
    }
}
