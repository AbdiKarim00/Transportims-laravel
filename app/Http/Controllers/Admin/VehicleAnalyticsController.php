<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\DriverLicense;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;

class VehicleAnalyticsController extends Controller
{
    /**
     * Display the vehicle analytics dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

        // Permits/Licenses Stats
        $totalLicenses = DriverLicense::count();
        $validLicenses = DriverLicense::where('expiry_date', '>', now())->count();
        $expiringLicenses = DriverLicense::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();
        $expiredLicenses = DriverLicense::where('expiry_date', '<', now())->count();
        $expiringLicensesDetails = DriverLicense::with('driver')->where('expiry_date', '<', now()->addDays(30))->get();

        // Odometer Stats
        $averageOdometer = 0;
        $highestOdometer = 0;
        $lowestOdometer = 0;
        $maintenanceDueCount = MaintenanceSchedule::where('status', 'due')->count();
        $odometerTrends = collect();
        $maintenanceSchedule = MaintenanceSchedule::with('vehicle')->whereIn('status', ['due', 'upcoming'])->orderBy('scheduled_date')->limit(10)->get();

        // Equipment Stats
        $totalEquipment = Vehicle::count();
        $activeEquipment = Vehicle::where('asset_condition', 'Active')->count();
        $equipmentInMaintenance = MaintenanceSchedule::where('status', 'in_progress')->distinct('vehicle_id')->count();
        $utilizationRate = 75; // Placeholder

        $fuelUsage = Trip::with('vehicle')->select('vehicle_id', DB::raw('sum(fuel_used) as liters, 0 as cost, 0 as efficiency'))->groupBy('vehicle_id')->limit(5)->get();

        $maintenanceCounts = MaintenanceSchedule::with('vehicle')->select('vehicle_id', DB::raw('count(*) as total_maintenance, max(scheduled_date) as last_service_date, status'))->groupBy('vehicle_id', 'status')->limit(5)->get();

        $equipmentAssignments = Trip::with(['vehicle', 'driver'])->latest()->limit(5)->get();

        // Service Providers Stats
        $totalProviders = ServiceProvider::count();
        $activeProviders = ServiceProvider::count();

        $providerPerformance = ServiceProvider::withCount('maintenanceSchedules')->orderBy('maintenance_schedules_count', 'desc')->limit(10)->get();
        $totalCost = MaintenanceSchedule::sum('estimated_cost');
        $averageRating = 0; // Placeholder
        $serviceHistory = MaintenanceSchedule::with('vehicle')->latest()->limit(10)->get();


        return view('admin.vehicle-analytics', compact(
            'tab',
            'totalVehicles',
            'activeVehicles',
            'maintenanceDue',
            'permitsExpiring',
            'recentActivity',
            'totalLicenses',
            'validLicenses',
            'expiringLicenses',
            'expiredLicenses',
            'expiringLicensesDetails',
            'averageOdometer',
            'highestOdometer',
            'lowestOdometer',
            'maintenanceDueCount',
            'odometerTrends',
            'maintenanceSchedule',
            'totalEquipment',
            'activeEquipment',
            'equipmentInMaintenance',
            'utilizationRate',
            'fuelUsage',
            'maintenanceCounts',
            'equipmentAssignments',
            'totalProviders',
            'activeProviders',
            'providerPerformance',
            'totalCost',
            'averageRating',
            'serviceHistory'
        ));
    }
}
