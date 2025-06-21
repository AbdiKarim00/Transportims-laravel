<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\MaintenanceSchedule;
use App\Models\DriverLicense;
use Illuminate\Http\Request;

class VehicleAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'overview');
        $startDate = $request->input('start_date', now()->subDays(30)->toDateTimeString());
        $endDate = $request->input('end_date', now()->toDateTimeString());

        // Overview Stats
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('asset_condition', 'Active')->count();
        $maintenanceDue = MaintenanceSchedule::where('status', 'due')->count();
        $permitsExpiring = DriverLicense::where('expiry_date', '<=', now()->addDays(30))->count();

        $recentActivity = collect();

        // Equipment Stats
        $totalEquipment = Vehicle::count();
        $activeEquipment = Vehicle::where('asset_condition', 'Active')->count();
        $equipmentInMaintenance = MaintenanceSchedule::where('status', 'in_progress')->distinct('vehicle_id')->count();
        $utilizationRate = 75; // Placeholder
    }
}
