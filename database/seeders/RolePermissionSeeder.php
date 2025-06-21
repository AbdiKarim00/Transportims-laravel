<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_dashboard',
            'manage_vehicles',
            'manage_drivers',
            'manage_trips',
            'manage_maintenance',
            'manage_fuel',
            'manage_users',
            'manage_roles',
            'view_reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $driverRole = Role::create(['name' => 'driver']);
        $managerRole = Role::create(['name' => 'manager']);

        // Assign all permissions to admin
        $adminRole->permissions()->attach(Permission::all());

        // Assign specific permissions to manager
        $managerRole->permissions()->attach(
            Permission::whereIn('name', [
                'view_dashboard',
                'manage_vehicles',
                'manage_drivers',
                'manage_trips',
                'view_reports'
            ])->get()
        );

        // Assign specific permissions to driver
        $driverRole->permissions()->attach(
            Permission::whereIn('name', [
                'view_dashboard',
                'view_reports'
            ])->get()
        );
    }
}
