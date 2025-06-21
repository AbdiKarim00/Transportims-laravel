<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\MaintenanceRecord;
use App\Models\DriverStatus;
use App\Models\TripStatus;
use App\Models\MaintenanceStatus;

class DashboardController extends Controller
{
    public function index()
    {
        // Get management statistics for the dashboard
        $stats = [
            'total_vehicles' => Vehicle::count(),
            'active_drivers' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Active');
            })->count(),
            'pending_maintenance' => MaintenanceRecord::where('status', 'pending')->count(),
            // Use status_id for Trip queries, assuming 1 = active (adjust as needed)
            'active_trips' => Trip::where('status_id', 1)->count(),
        ];

        // Get vehicle utilization data for management reporting
        $vehicleUtilization = [
            'total_fleet' => Vehicle::count(),
            'in_use' => Vehicle::whereHas('trips', function ($query) {
                $query->where('status_id', 1);
            })->count(),
            'maintenance' => Vehicle::whereHas('maintenanceRecords', function ($query) {
                $query->where('status', 'pending')->orWhere('status', 'in_progress');
            })->count(),
            'available' => Vehicle::count() -
                (Vehicle::whereHas('trips', function ($query) {
                    $query->where('status_id', 1);
                })->count() +
                    Vehicle::whereHas('maintenanceRecords', function ($query) {
                        $query->where('status', 'pending')->orWhere('status', 'in_progress');
                    })->count())
        ];

        // Get high-priority maintenance alerts for management oversight
        $maintenance_alerts = MaintenanceRecord::with(['vehicle'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Get active trips data for operational oversight
        $recent_trips = Trip::with(['vehicle', 'driver'])
            ->where('status_id', 1)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_trips', 'maintenance_alerts', 'vehicleUtilization'));
    }
}
