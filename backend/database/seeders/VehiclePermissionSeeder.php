<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Site;
use App\Models\Barrier;
use App\Models\VehiclePermission;

class VehiclePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pegar veículos de exemplo (do VehicleSeeder original)
        $veiculoAlpha = Vehicle::where('lora_id', '24A160123456')->first();
        $veiculoBravo = Vehicle::where('lora_id', '24A160654321')->first();
        $veiculoDeltaNaoAut = Vehicle::where('lora_id', 'AABBCCDDEEFF')->first(); // Este não era autorizado antes

        // Pegar entidades
        $companyAlpha = Company::where('name', 'Empresa Exemplo Alpha')->first();
        $siteAlphaSede = Site::where('name', 'Sede Alpha')->first();
        $barrierAlphaSedePrincipal = Barrier::where('name', 'Barreira Principal (Sede Alpha)')->first();

        $companyBeta = Company::where('name', 'Cliente Beta Soluções')->first();
        $siteBetaCentral = Site::where('name', 'Escritório Central Beta')->first();


        // Permissões para Veiculo Alpha
        if ($veiculoAlpha && $companyAlpha) {
            VehiclePermission::create([
                'vehicle_id' => $veiculoAlpha->id,
                'permissible_type' => Company::class, // Permissão para a empresa inteira Alpha
                'permissible_id' => $companyAlpha->id,
            ]);
        }

        // Permissões para Veiculo Bravo
        if ($veiculoBravo && $siteAlphaSede) {
             VehiclePermission::create([
                'vehicle_id' => $veiculoBravo->id,
                'permissible_type' => Site::class, // Permissão apenas para o Site Sede Alpha
                'permissible_id' => $siteAlphaSede->id,
            ]);
        }
        if ($veiculoBravo && $companyBeta) { // Veiculo Bravo também tem acesso à Empresa Beta
            VehiclePermission::create([
               'vehicle_id' => $veiculoBravo->id,
               'permissible_type' => Company::class,
               'permissible_id' => $companyBeta->id,
           ]);
       }


        // Veiculo Delta (que antes era não autorizado) agora terá permissão específica para uma barreira
        if ($veiculoDeltaNaoAut && $barrierAlphaSedePrincipal) {
            VehiclePermission::create([
                'vehicle_id' => $veiculoDeltaNaoAut->id,
                'permissible_type' => Barrier::class,
                'permissible_id' => $barrierAlphaSedePrincipal->id,
                'expires_at' => now()->addMonths(3) // Exemplo de permissão temporária
            ]);
        }
    }
}
