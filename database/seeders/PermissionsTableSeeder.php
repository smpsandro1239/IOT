<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permission::truncate(); // Careful with truncate

        $permissions = [
            // Companies
            ['name' => 'companies:view-any', 'display_name' => 'View Any Company', 'group_name' => 'Companies'],
            ['name' => 'companies:view-own', 'display_name' => 'View Own Company', 'group_name' => 'Companies'],
            ['name' => 'companies:create', 'display_name' => 'Create Company', 'group_name' => 'Companies'],
            ['name' => 'companies:update-any', 'display_name' => 'Update Any Company', 'group_name' => 'Companies'],
            ['name' => 'companies:update-own', 'display_name' => 'Update Own Company', 'group_name' => 'Companies'],
            ['name' => 'companies:delete-any', 'display_name' => 'Delete Any Company', 'group_name' => 'Companies'],
            // ['name' => 'companies:delete-own', 'display_name' => 'Delete Own Company', 'group_name' => 'Companies'], // Decidimos não dar esta por agora

            // Sites
            ['name' => 'sites:view-any', 'display_name' => 'View Any Site', 'group_name' => 'Sites'],
            ['name' => 'sites:view-own-company', 'display_name' => 'View Own Company Sites', 'group_name' => 'Sites'],
            ['name' => 'sites:view-assigned', 'display_name' => 'View Assigned Sites', 'group_name' => 'Sites'],
            ['name' => 'sites:create-own-company', 'display_name' => 'Create Site for Own Company', 'group_name' => 'Sites'],
            ['name' => 'sites:update-any', 'display_name' => 'Update Any Site', 'group_name' => 'Sites'],
            ['name' => 'sites:update-own-company', 'display_name' => 'Update Own Company Site', 'group_name' => 'Sites'],
            ['name' => 'sites:update-assigned', 'display_name' => 'Update Assigned Site', 'group_name' => 'Sites'],
            ['name' => 'sites:delete-any', 'display_name' => 'Delete Any Site', 'group_name' => 'Sites'],
            ['name' => 'sites:delete-own-company', 'display_name' => 'Delete Own Company Site', 'group_name' => 'Sites'],
            ['name' => 'sites:delete-assigned', 'display_name' => 'Delete Assigned Site', 'group_name' => 'Sites'],

            // Barriers
            ['name' => 'barriers:view-any', 'display_name' => 'View Any Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:view-own-company', 'display_name' => 'View Own Company Barriers', 'group_name' => 'Barriers'],
            ['name' => 'barriers:view-assigned-site', 'display_name' => 'View Assigned Site Barriers', 'group_name' => 'Barriers'],
            ['name' => 'barriers:create-own-company-site', 'display_name' => 'Create Barrier for Own Company Site', 'group_name' => 'Barriers'],
            ['name' => 'barriers:create-assigned-site', 'display_name' => 'Create Barrier for Assigned Site', 'group_name' => 'Barriers'],
            ['name' => 'barriers:update-any', 'display_name' => 'Update Any Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:update-own-company-site', 'display_name' => 'Update Own Company Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:update-assigned-site', 'display_name' => 'Update Assigned Site Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:delete-any', 'display_name' => 'Delete Any Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:delete-own-company-site', 'display_name' => 'Delete Own Company Barrier', 'group_name' => 'Barriers'],
            ['name' => 'barriers:delete-assigned-site', 'display_name' => 'Delete Assigned Site Barrier', 'group_name' => 'Barriers'],

            // Vehicles (Registration - assuming vehicles are global or managed by super-admin mainly for registration)
            // If vehicles are per-company, these need adjustment. Our current Vehicle model is global.
            ['name' => 'vehicles:view-any', 'display_name' => 'View Any Vehicle', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:create-any', 'display_name' => 'Create Any Vehicle', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:update-any', 'display_name' => 'Update Any Vehicle', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:delete-any', 'display_name' => 'Delete Any Vehicle', 'group_name' => 'Vehicles'],
            // Company specific vehicle management if needed:
            ['name' => 'vehicles:view-own-company', 'display_name' => 'View Own Company Vehicles', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:create-own-company', 'display_name' => 'Create Vehicle for Own Company', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:update-own-company', 'display_name' => 'Update Own Company Vehicle', 'group_name' => 'Vehicles'],
            ['name' => 'vehicles:delete-own-company', 'display_name' => 'Delete Own Company Vehicle', 'group_name' => 'Vehicles'],


            // Vehicle Permissions (Assigning access)
            ['name' => 'vehicle-permissions:assign-to-any-company', 'display_name' => 'Assign Vehicle to Any Company', 'group_name' => 'Vehicle Permissions'],
            ['name' => 'vehicle-permissions:assign-to-own-company', 'display_name' => 'Assign Vehicle to Own Company/Sites/Barriers', 'group_name' => 'Vehicle Permissions'],
            ['name' => 'vehicle-permissions:assign-to-assigned-site', 'display_name' => 'Assign Vehicle to Assigned Site/Barriers', 'group_name' => 'Vehicle Permissions'],
            ['name' => 'vehicle-permissions:view-any', 'display_name' => 'View Any Vehicle Permissions', 'group_name' => 'Vehicle Permissions'],
            ['name' => 'vehicle-permissions:view-own-company', 'display_name' => 'View Own Company Vehicle Permissions', 'group_name' => 'Vehicle Permissions'],
            ['name' => 'vehicle-permissions:view-assigned-site', 'display_name' => 'View Assigned Site Vehicle Permissions', 'group_name' => 'Vehicle Permissions'],

            // Users
            ['name' => 'users:view-any', 'display_name' => 'View Any User', 'group_name' => 'Users'],
            ['name' => 'users:view-own-company', 'display_name' => 'View Own Company Users', 'group_name' => 'Users'],
            ['name' => 'users:create-super-admin', 'display_name' => 'Create Super Admin', 'group_name' => 'Users'],
            ['name' => 'users:create-company-admin', 'display_name' => 'Create Company Admin', 'group_name' => 'Users'],
            ['name' => 'users:create-site-manager-own-company', 'display_name' => 'Create Site Manager for Own Company', 'group_name' => 'Users'],
            ['name' => 'users:update-any', 'display_name' => 'Update Any User Profile', 'group_name' => 'Users'],
            ['name' => 'users:update-own', 'display_name' => 'Update Own Profile', 'group_name' => 'Users'], // For all users
            ['name' => 'users:update-own-company-user', 'display_name' => 'Update Own Company User', 'group_name' => 'Users'],
            ['name' => 'users:delete-any', 'display_name' => 'Delete Any User', 'group_name' => 'Users'],
            ['name' => 'users:delete-own-company-user', 'display_name' => 'Delete Own Company User', 'group_name' => 'Users'],
            ['name' => 'users:assign-roles-any', 'display_name' => 'Assign Roles to Any User', 'group_name' => 'Users'],
            ['name' => 'users:assign-roles-own-company', 'display_name' => 'Assign SiteManager Role to Own Company User', 'group_name' => 'Users'],

            // Access Logs
            ['name' => 'access-logs:view-any', 'display_name' => 'View Any Access Logs', 'group_name' => 'Access Logs'],
            ['name' => 'access-logs:view-own-company', 'display_name' => 'View Own Company Access Logs', 'group_name' => 'Access Logs'],
            ['name' => 'access-logs:view-assigned-site', 'display_name' => 'View Assigned Site Access Logs', 'group_name' => 'Access Logs'],

            // Firmwares
            ['name' => 'firmwares:manage', 'display_name' => 'Manage Firmwares', 'group_name' => 'Firmwares'],
            ['name' => 'firmwares:view', 'display_name' => 'View Firmwares', 'group_name' => 'Firmwares'],

            // API Tokens
            ['name' => 'api-tokens:manage', 'display_name' => 'Manage API Tokens', 'group_name' => 'API Tokens'],

            // RBAC Management (for SuperAdmin to manage roles/permissions themselves if UI is built)
            ['name' => 'roles:manage', 'display_name' => 'Manage Roles', 'group_name' => 'RBAC'],
            ['name' => 'permissions:manage', 'display_name' => 'Manage Permissions', 'group_name' => 'RBAC'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(['name' => $permissionData['name']], $permissionData);
        }
    }
}
