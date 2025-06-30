<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
  public function run(): void
  {
    $permissions = [
      'sites:view-any',
      'sites:create-any',
      'sites:update-any',
      'sites:delete-any',
      'sites:view-own-company',
      'sites:create-own-company',
      'sites:update-own-company',
      'sites:delete-own-company',
      'sites:update-assigned',
      'sites:delete-assigned',
      'barriers:view-any',
      'barriers:create-any',
      'barriers:update-any',
      'barriers:delete-any',
      'barriers:view-own-company',
      'barriers:create-own-company-site',
      'barriers:update-own-company-site',
      'barriers:delete-own-company-site',
      'barriers:view-assigned-site',
      'barriers:create-assigned-site',
      'barriers:update-assigned-site',
      'barriers:delete-assigned-site',
      'vehicles:view-any',
      'vehicles:create-any',
      'vehicles:update-any',
      'vehicles:delete-any',
      'vehicle-permissions:assign-to-any-company',
      'vehicle-permissions:assign-to-own-company',
      'vehicle-permissions:assign-to-assigned-site',
      'companies:view-any',
      'companies:update-own',
      'companies:delete-any',
      'users:view-any',
      'users:update-own',
      'users:delete-any',
      'users:update-own-company-user',
      'users:delete-own-company-user',
    ];
    foreach ($permissions as $permission) {
      Permission::firstOrCreate(['name' => $permission]);
    }
  }
}
