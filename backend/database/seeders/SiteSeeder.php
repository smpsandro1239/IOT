<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Site;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyAlpha = Company::where('name', 'Empresa Exemplo Alpha')->first();
        $companyBeta = Company::where('name', 'Cliente Beta Soluções')->first();

        if ($companyAlpha) {
            Site::create([
                'company_id' => $companyAlpha->id,
                'name' => 'Sede Alpha',
                'address' => 'Rua Principal Alpha, 123',
                'is_active' => true,
            ]);
            Site::create([
                'company_id' => $companyAlpha->id,
                'name' => 'Filial Alpha Zona Industrial',
                'address' => 'Av. Industrial Alpha, 456',
                'is_active' => true,
            ]);
        }

        if ($companyBeta) {
            Site::create([
                'company_id' => $companyBeta->id,
                'name' => 'Escritório Central Beta',
                'address' => 'Praça Beta, 789',
                'is_active' => true,
            ]);
        }
    }
}
