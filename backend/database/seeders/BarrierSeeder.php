<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\Barrier;

class BarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siteAlphaSede = Site::where('name', 'Sede Alpha')->first();
        $siteAlphaFilial = Site::where('name', 'Filial Alpha Zona Industrial')->first();
        $siteBetaCentral = Site::where('name', 'Escritório Central Beta')->first();

        if ($siteAlphaSede) {
            Barrier::create([
                'site_id' => $siteAlphaSede->id,
                'name' => 'Barreira Principal (Sede Alpha)',
                // MAC da Placa Base deve ser único. Usar placeholders para exemplo.
                'base_station_mac_address' => 'AA:BB:CC:00:00:01',
                'is_active' => true,
            ]);
        }

        if ($siteAlphaFilial) {
            Barrier::create([
                'site_id' => $siteAlphaFilial->id,
                'name' => 'Entrada Cargas (Filial Alpha)',
                'base_station_mac_address' => 'AA:BB:CC:00:00:02',
                'is_active' => true,
            ]);
            Barrier::create([
                'site_id' => $siteAlphaFilial->id,
                'name' => 'Saída Funcionários (Filial Alpha)',
                'base_station_mac_address' => 'AA:BB:CC:00:00:03',
                'is_active' => false, // Exemplo de barreira inativa
            ]);
        }

        if ($siteBetaCentral) {
            Barrier::create([
                'site_id' => $siteBetaCentral->id,
                'name' => 'Acesso Garagem (Beta Central)',
                'base_station_mac_address' => 'AA:BB:CC:00:00:04',
                'is_active' => true,
            ]);
        }
    }
}
