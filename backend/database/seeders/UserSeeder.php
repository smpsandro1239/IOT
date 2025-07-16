<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Criar usuário admin
    User::create([
      'name' => 'Admin',
      'email' => 'admin@example.com',
      'password' => Hash::make('password'),
    ]);

    // Criar usuário operador
    User::create([
      'name' => 'Operador',
      'email' => 'operador@example.com',
      'password' => Hash::make('password'),
    ]);
  }
}
