<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MacsAutorizados;
use Carbon\Carbon;

class MacsAutorizadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns MACs autorizados de exemplo
        $macs = [
            ['mac' => '24A160123456', 'placa' => 'ABC1234'],
            ['mac' => 'AABBCCDDEEFF', 'placa' => 'XYZ5678'],
            ['mac' => '102B22286F24', 'placa' => 'DEF9012'],
            ['mac' => '24A160654321', 'placa' => 'GHI3456'],
            ['mac' => '5C6B4F3E2D1C', 'placa' => 'JKL7890'],
        ];

        foreach ($macs as $mac) {
            MacsAutorizados::create([
                'mac' => $mac['mac'],
                'placa' => $mac['placa'],
                'data_adicao' => Carbon::now(),
            ]);
        }
    }
}
