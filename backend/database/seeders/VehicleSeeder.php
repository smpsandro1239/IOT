<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle; // Certifique-se que o namespace do Model está correto
use Illuminate\Support\Facades\DB; // Para usar DB::table se preferir ou para desabilitar/habilitar foreign key checks

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opção 1: Usando o Eloquent Model (preferível)
        Vehicle::create([
            'lora_id' => '24A160123456', // Exemplo de MAC ID usado no firmware da base
            'name' => 'Veiculo Alpha (Autorizado)',
            'is_authorized' => true,
        ]);

        Vehicle::create([
            'lora_id' => '24A160654321', // Exemplo de MAC ID usado no firmware da base
            'name' => 'Veiculo Bravo (Autorizado)',
            'is_authorized' => true,
        ]);

        Vehicle::create([
            'lora_id' => '102B22286F24', // Exemplo de MAC ID usado no firmware da base
            'name' => 'Veiculo Charlie (Autorizado)',
            'is_authorized' => true,
        ]);

        Vehicle::create([
            'lora_id' => 'AABBCCDDEEFF',
            'name' => 'Veiculo Delta (Nao Autorizado)',
            'is_authorized' => false,
        ]);

        Vehicle::create([
            'lora_id' => '112233445566',
            'name' => 'Veiculo Echo (Autorizado)',
            'is_authorized' => true,
        ]);

        // Opção 2: Usando DB Facade (se não quiser usar o Model por algum motivo)
        // DB::table('vehicles')->insert([
        //     'lora_id' => 'FFEEDDCCBBAA',
        //     'name' => 'Veiculo Foxtrot (Autorizado com DB Facade)',
        //     'is_authorized' => true,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
