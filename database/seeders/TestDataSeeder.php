<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Role;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'personal_number' => 'ADM001'
        ]);
        $admin->roles()->attach($adminRole);

        // Create vehicles
        $vehicles = [
            ['plate_number' => 'ABC123', 'model' => 'Toyota Hilux', 'year' => 2020],
            ['plate_number' => 'DEF456', 'model' => 'Isuzu D-Max', 'year' => 2021],
            ['plate_number' => 'GHI789', 'model' => 'Mitsubishi L200', 'year' => 2019],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }

        // Create drivers
        $drivers = [
            ['name' => 'John Doe', 'license_number' => 'DRV001', 'status' => 'active'],
            ['name' => 'Jane Smith', 'license_number' => 'DRV002', 'status' => 'active'],
            ['name' => 'Mike Johnson', 'license_number' => 'DRV003', 'status' => 'inactive'],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }

        // Create trips
        $trips = [
            [
                'vehicle_id' => 1,
                'driver_id' => 1,
                'destination' => 'New York',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays(2),
            ],
            [
                'vehicle_id' => 2,
                'driver_id' => 2,
                'destination' => 'Los Angeles',
                'status' => 'completed',
                'start_date' => now()->subDays(5),
                'end_date' => now()->subDays(2),
            ],
        ];

        foreach ($trips as $trip) {
            Trip::create($trip);
        }

        // Create maintenance records
        $maintenanceRecords = [
            [
                'vehicle_id' => 1,
                'description' => 'Regular service due',
                'status' => 'pending',
                'due_date' => now()->addDays(7),
            ],
            [
                'vehicle_id' => 2,
                'description' => 'Oil change required',
                'status' => 'pending',
                'due_date' => now()->addDays(3),
            ],
        ];

        foreach ($maintenanceRecords as $record) {
            MaintenanceRecord::create($record);
        }
    }
}
