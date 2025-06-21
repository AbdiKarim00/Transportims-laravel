<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceAlert;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        // Get total maintenance costs and counts
        $maintenanceMetrics = MaintenanceRecord::whereBetween('service_date', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_maintenance'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('AVG(cost) as avg_cost')
            )
            ->first();

        // Get maintenance by vehicle type
        $maintenanceByType = Vehicle::join('maintenance_records', 'vehicles.id', '=', 'maintenance_records.vehicle_id')
            ->join('vehicle_types', 'vehicles.type_id', '=', 'vehicle_types.id')
            ->whereBetween('maintenance_records.service_date', [$startDate, $endDate])
            ->select(
                'vehicle_types.name as type',
                DB::raw('COUNT(*) as total_maintenance'),
                DB::raw('SUM(maintenance_records.cost) as total_cost')
            )
            ->groupBy('vehicle_types.name')
            ->get();

        // Get active maintenance alerts
        $maintenanceAlerts = MaintenanceAlert::with(['vehicle'])
            ->where('status', 'active')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Get maintenance trend (monthly)
        $maintenanceTrend = MaintenanceRecord::whereBetween('service_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_TRUNC(\'month\', service_date) as month'),
                DB::raw('COUNT(*) as total_maintenance'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get top vehicles by maintenance cost
        $topMaintenanceVehicles = Vehicle::join('maintenance_records', 'vehicles.id', '=', 'maintenance_records.vehicle_id')
            ->whereBetween('maintenance_records.service_date', [$startDate, $endDate])
            ->select(
                'vehicles.registration_no',
                'vehicles.make',
                'vehicles.model',
                DB::raw('COUNT(*) as total_maintenance'),
                DB::raw('SUM(maintenance_records.cost) as total_cost')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_no', 'vehicles.make', 'vehicles.model')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();

        return view('admin.maintenance-reports', compact(
            'maintenanceMetrics',
            'maintenanceByType',
            'maintenanceAlerts',
            'maintenanceTrend',
            'topMaintenanceVehicles',
            'startDate',
            'endDate'
        ));
    }
}
