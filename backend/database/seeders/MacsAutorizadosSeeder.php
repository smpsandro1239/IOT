<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MacsAutorizadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('macs_autorizados')->insert([
            [
                'mac' => '24A160123456',
                'placa' => 'ABC1234',
                'data_adicao' => now(),
            ],
            [
                'mac' => 'AABBCCDDEEFF',
                'placa' => 'XYZ5678',
                'data_adicao' => now(),
            ],
        ]);
    }
}
