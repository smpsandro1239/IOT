<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
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
            'sites:update-assigned',
            'sites:delete-assigned',
            'barriers:view-assigned-site',
            'barriers:create-assigned-site',
            'barriers:update-assigned-site',
            'barriers:delete-assigned-site',
            'vehicle-permissions:assign-to-assigned-site',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}