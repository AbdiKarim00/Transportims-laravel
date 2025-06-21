<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        // Get total fuel consumption and cost
        $fuelMetrics = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(total_amount) as total_cost'),
                DB::raw('AVG(price_per_liter) as avg_price_per_liter')
            )
            ->first();

        // Get fuel consumption by vehicle type
        $consumptionByType = Vehicle::join('fuel_transactions', 'vehicles.id', '=', 'fuel_transactions.vehicle_id')
            ->join('vehicle_types', 'vehicles.type_id', '=', 'vehicle_types.id')
            ->whereBetween('fuel_transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'vehicle_types.name as type',
                DB::raw('SUM(fuel_transactions.liters) as total_liters'),
                DB::raw('SUM(fuel_transactions.total_amount) as total_cost')
            )
            ->groupBy('vehicle_types.name')
            ->get();

        // Get top fuel-consuming vehicles
        $topConsumers = Vehicle::join('fuel_transactions', 'vehicles.id', '=', 'fuel_transactions.vehicle_id')
            ->whereBetween('fuel_transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'vehicles.registration_no',
                'vehicles.make',
                'vehicles.model',
                DB::raw('SUM(fuel_transactions.liters) as total_liters'),
                DB::raw('SUM(fuel_transactions.total_amount) as total_cost'),
                DB::raw('AVG(fuel_transactions.price_per_liter) as avg_price_per_liter')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_no', 'vehicles.make', 'vehicles.model')
            ->orderByDesc('total_liters')
            ->limit(10)
            ->get();

        // Get fuel consumption trend (daily)
        $consumptionTrend = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(total_amount) as total_cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get fuel efficiency by vehicle
        $fuelEfficiency = Vehicle::join('fuel_transactions', 'vehicles.id', '=', 'fuel_transactions.vehicle_id')
            ->join('trips', 'fuel_transactions.trip_id', '=', 'trips.id')
            ->whereBetween('fuel_transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'vehicles.registration_no',
                DB::raw('SUM(trips.distance) as total_distance'),
                DB::raw('SUM(fuel_transactions.liters) as total_fuel'),
                DB::raw('ROUND(SUM(trips.distance) / NULLIF(SUM(fuel_transactions.liters), 0), 2) as km_per_liter')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_no')
            ->having(DB::raw('SUM(trips.distance)'), '>', 0)
            ->orderByDesc('km_per_liter')
            ->limit(10)
            ->get();

        return view('admin.fuel-reports', compact(
            'fuelMetrics',
            'consumptionByType',
            'topConsumers',
            'consumptionTrend',
            'fuelEfficiency',
            'startDate',
            'endDate'
        ));
    }
}
