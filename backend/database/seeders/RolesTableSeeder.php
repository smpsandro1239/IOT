<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
  public function run(): void
  {
    $roles = ['super-admin', 'company-admin', 'site-manager'];
    foreach ($roles as $role) {
      Role::firstOrCreate(['name' => $role]);
    }
  }
}
