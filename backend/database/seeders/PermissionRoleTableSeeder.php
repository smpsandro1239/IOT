<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleTableSeeder extends Seeder
{
  public function run(): void
  {
    $superAdmin = Role::where('name', 'super-admin')->first();
    $companyAdmin = Role::where('name', 'company-admin')->first();
    $siteManager = Role::where('name', 'site-manager')->first();

    $allPermissions = Permission::all();

    if ($superAdmin) {
      $superAdmin->permissions()->sync($allPermissions->pluck('id'));
    }

    if ($companyAdmin) {
      $companyAdminPermissions = Permission::whereIn('name', [
        'sites:view-own-company',
        'sites:create-own-company',
        'sites:update-own-company',
        'sites:delete-own-company',
        'barriers:view-own-company',
        'barriers:create-own-company-site',
        'barriers:update-own-company-site',
        'barriers:delete-own-company-site',
        'vehicle-permissions:assign-to-own-company',
        'companies:view-any',
        'companies:update-own',
        'users:update-own-company-user',
        'users:delete-own-company-user',
      ])->get();
      $companyAdmin->permissions()->sync($companyAdminPermissions->pluck('id'));
    }

    if ($siteManager) {
      $siteManagerPermissions = Permission::whereIn('name', [
        'sites:update-assigned',
        'sites:delete-assigned',
        'barriers:view-assigned-site',
        'barriers:create-assigned-site',
        'barriers:update-assigned-site',
        'barriers:delete-assigned-site',
        'vehicle-permissions:assign-to-assigned-site',
      ])->get();
      $siteManager->permissions()->sync($siteManagerPermissions->pluck('id'));
    }
  }
}
