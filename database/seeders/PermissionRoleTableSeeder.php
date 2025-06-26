<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Detach all existing permissions from roles to start clean,
        // especially if this seeder is run multiple times.
        // DB::table('permission_role')->truncate(); // Simplest way if no other logic needed on detach/attach

        $superAdminRole = Role::where('name', 'super-admin')->first();
        $companyAdminRole = Role::where('name', 'company-admin')->first();
        $siteManagerRole = Role::where('name', 'site-manager')->first();

        if (!$superAdminRole) {
            $this->command->error("Role 'super-admin' not found. Please run RolesTableSeeder first.");
            return;
        }
        if (!$companyAdminRole) {
            $this->command->error("Role 'company-admin' not found. Please run RolesTableSeeder first.");
            return;
        }
        if (!$siteManagerRole) {
            $this->command->error("Role 'site-manager' not found. Please run RolesTableSeeder first.");
            return;
        }

        // --- Super Admin Permissions ---
        // Super admin gets all permissions
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        $this->command->info("Super Admin permissions synced.");

        // --- Company Admin Permissions ---
        $companyAdminPermissions = [
            // Companies
            'companies:view-own', 'companies:update-own',
            // Sites
            'sites:view-own-company', 'sites:create-own-company', 'sites:update-own-company', 'sites:delete-own-company',
            // Barriers
            'barriers:view-own-company', 'barriers:create-own-company-site', 'barriers:update-own-company-site', 'barriers:delete-own-company-site',
            // Vehicles (assuming per-company model for these actions)
            'vehicles:view-own-company', 'vehicles:create-own-company', 'vehicles:update-own-company', 'vehicles:delete-own-company',
            // Vehicle Permissions
            'vehicle-permissions:assign-to-own-company', 'vehicle-permissions:view-own-company',
            // Users
            'users:view-own-company', 'users:create-site-manager-own-company',
            'users:update-own', // All users can update their own profile
            'users:update-own-company-user', 'users:delete-own-company-user', 'users:assign-roles-own-company', // Assign SiteManager
            // Access Logs
            'access-logs:view-own-company',
            // Firmwares
            'firmwares:view',
        ];
        $companyAdminPermIds = Permission::whereIn('name', $companyAdminPermissions)->pluck('id');
        $companyAdminRole->permissions()->sync($companyAdminPermIds);
        $this->command->info("Company Admin permissions synced.");

        // --- Site Manager Permissions ---
        $siteManagerPermissions = [
            // Sites
            'sites:view-assigned', 'sites:update-assigned', // Note: 'update-assigned' should be limited in policy/controller
            // Barriers
            'barriers:view-assigned-site', 'barriers:create-assigned-site', 'barriers:update-assigned-site', 'barriers:delete-assigned-site',
            // Vehicle Permissions
            'vehicle-permissions:assign-to-assigned-site', 'vehicle-permissions:view-assigned-site',
            // Users
            'users:update-own', // All users can update their own profile
            // Access Logs
            'access-logs:view-assigned-site',
            // Firmwares (View only is fine)
            'firmwares:view',
        ];
        $siteManagerPermIds = Permission::whereIn('name', $siteManagerPermissions)->pluck('id');
        $siteManagerRole->permissions()->sync($siteManagerPermIds);
        $this->command->info("Site Manager permissions synced.");

        // If you had a 'base-user' role, assign its permissions here
        // $baseUserRole = Role::where('name', 'base-user')->first();
        // if ($baseUserRole) {
        //     $baseUserPermissions = ['users:update-own'];
        //     $baseUserPermIds = Permission::whereIn('name', $baseUserPermissions)->pluck('id');
        //     $baseUserRole->permissions()->sync($baseUserPermIds);
        //     $this->command->info("Base User permissions synced.");
        // }
    }
}
