<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        // Get total trip metrics
        $tripMetrics = Trip::whereBetween('start_time', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(distance) as total_distance'),
                DB::raw('AVG(distance) as avg_distance'),
                DB::raw('SUM(EXTRACT(EPOCH FROM (end_time - start_time))/3600) as total_duration')
            )
            ->first();

        // Get trips by vehicle type
        $tripsByType = Vehicle::join('trips', 'vehicles.id', '=', 'trips.vehicle_id')
            ->join('vehicle_types', 'vehicles.type_id', '=', 'vehicle_types.id')
            ->whereBetween('trips.start_time', [$startDate, $endDate])
            ->select(
                'vehicle_types.name as type',
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(trips.distance) as total_distance'),
                DB::raw('AVG(trips.distance) as avg_distance')
            )
            ->groupBy('vehicle_types.name')
            ->get();

        // Get trip trend (monthly)
        $tripTrend = Trip::whereBetween('start_time', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_TRUNC(\'month\', start_time) as month'),
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(distance) as total_distance')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get top vehicles by distance
        $topVehiclesByDistance = Vehicle::join('trips', 'vehicles.id', '=', 'trips.vehicle_id')
            ->whereBetween('trips.start_time', [$startDate, $endDate])
            ->select(
                'vehicles.registration_no',
                'vehicles.make',
                'vehicles.model',
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(trips.distance) as total_distance')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_no', 'vehicles.make', 'vehicles.model')
            ->orderByDesc('total_distance')
            ->limit(10)
            ->get();

        // Get recent trips
        $recentTrips = Trip::with(['vehicle', 'driver', 'status'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.trip-analytics', compact(
            'tripMetrics',
            'tripsByType',
            'tripTrend',
            'topVehiclesByDistance',
            'recentTrips',
            'startDate',
            'endDate'
        ));
    }
}
