<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetAllocation;
use App\Models\CostCenter;
use App\Models\CostCenterAllocation;
use App\Models\ExpenseApproval;
use App\Models\FuelCard;
use App\Models\FuelTransaction;
use App\Models\InsurancePolicy;
use App\Models\TripExpense;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialManagementController extends Controller
{
    /**
     * Display the main financial management dashboard
     */
    public function index()
    {
        // Get date range from request or default to last 30 days
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        // Get summary metrics
        $summaryMetrics = $this->getSummaryMetrics($startDate, $endDate);

        // Get monthly trends
        $monthlyTrends = $this->getMonthlyTrends($startDate, $endDate);

        // Get top expense vehicles
        $topExpenseVehicles = $this->getTopExpenseVehicles($startDate, $endDate);

        return view('admin.financial-management.overview', compact(
            'summaryMetrics',
            'monthlyTrends',
            'topExpenseVehicles',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get summary metrics for financial dashboard
     */
    private function getSummaryMetrics($startDate, $endDate)
    {
        // Total fuel costs
        $fuelCosts = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('total_amount') ?? 0;

        // Total trip expenses
        $tripExpenses = TripExpense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') ?? 0;

        // Active insurance policies
        $activeInsurancePolicies = InsurancePolicy::where('end_date', '>=', now())
            ->where('start_date', '<=', now())
            ->where('status', true)
            ->count();

        // Insurance costs (monthly premiums during period)
        $monthDiff = Carbon::parse($startDate)->diffInMonths(Carbon::parse($endDate)) + 1;
        $insuranceCosts = InsurancePolicy::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })
            ->where('status', true)
            ->sum('premium_amount') / 12 * $monthDiff;

        // Active fuel cards
        $activeFuelCards = FuelCard::where('expiry_date', '>=', now())
            ->where('status', 'active')
            ->count();

        // Total trips during period
        $totalTrips = Trip::whereBetween('start_time', [$startDate, $endDate])
            ->count();

        // Total vehicles
        $totalVehicles = Vehicle::count();

        // Calculate cost per vehicle
        $totalCosts = $fuelCosts + $tripExpenses + $insuranceCosts;
        $costPerVehicle = $totalVehicles > 0 ? $totalCosts / $totalVehicles : 0;

        // Get budget information
        $currentFiscalYear = Carbon::now()->year;
        $totalBudget = Budget::where('fiscal_year', $currentFiscalYear)
            ->where('status', true)
            ->sum('amount');

        // Get pending expense approvals
        $pendingApprovals = ExpenseApproval::where('status', 'pending')->count();

        // Calculate total revenue (placeholder - would need a revenue table)
        $totalRevenue = 0;

        // Calculate ROI
        $roiPercentage = $totalRevenue > 0 ? (($totalRevenue - $totalCosts) / $totalCosts) * 100 : 0;

        // Calculate total asset value (placeholder - would need vehicle values)
        $totalAssetValue = 0;

        return [
            'fuel_costs' => $fuelCosts,
            'trip_expenses' => $tripExpenses,
            'insurance_costs' => $insuranceCosts,
            'total_costs' => $totalCosts,
            'total_expenses' => $totalCosts,
            'cost_per_vehicle' => $costPerVehicle,
            'active_policies' => $activeInsurancePolicies,
            'active_fuel_cards' => $activeFuelCards,
            'total_trips' => $totalTrips,
            'total_vehicles' => $totalVehicles,
            'total_budget' => $totalBudget,
            'budget_utilization' => $totalBudget > 0 ? ($totalCosts / $totalBudget) * 100 : 0,
            'pending_approvals' => $pendingApprovals,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'fuel_cost_percentage' => $totalCosts > 0 ? ($fuelCosts / $totalCosts) * 100 : 0,
            'trip_expense_percentage' => $totalCosts > 0 ? ($tripExpenses / $totalCosts) * 100 : 0,
            'insurance_cost_percentage' => $totalCosts > 0 ? ($insuranceCosts / $totalCosts) * 100 : 0,
            'total_revenue' => $totalRevenue,
            'roi_percentage' => $roiPercentage,
            'total_asset_value' => $totalAssetValue
        ];
    }

    /**
     * Get monthly cost trends
     */
    private function getMonthlyTrends($startDate, $endDate)
    {
        // Fuel costs by month
        $fuelCosts = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                DB::raw("to_char(transaction_date, 'YYYY-MM') as month"),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy(DB::raw("to_char(transaction_date, 'YYYY-MM')"))
            ->orderBy('month')
            ->pluck('total_amount', 'month')
            ->toArray();

        // Trip expenses by month
        $tripExpenses = TripExpense::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("to_char(created_at, 'YYYY-MM') as month"),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy(DB::raw("to_char(created_at, 'YYYY-MM')"))
            ->orderBy('month')
            ->pluck('total_amount', 'month')
            ->toArray();

        // Combine all months from both datasets
        $allMonths = array_unique(array_merge(array_keys($fuelCosts), array_keys($tripExpenses)));
        sort($allMonths);

        $trends = [];
        foreach ($allMonths as $month) {
            $trends[] = [
                'month' => $month,
                'fuel_costs' => $fuelCosts[$month] ?? 0,
                'trip_expenses' => $tripExpenses[$month] ?? 0,
                'total' => ($fuelCosts[$month] ?? 0) + ($tripExpenses[$month] ?? 0)
            ];
        }

        return $trends;
    }

    /**
     * Get top expense vehicles
     */
    private function getTopExpenseVehicles($startDate, $endDate)
    {
        $topVehicles = Vehicle::join('fuel_transactions', 'vehicles.id', '=', 'fuel_transactions.vehicle_id')
            ->whereBetween('fuel_transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'vehicles.id',
                'vehicles.registration_no',
                'vehicles.make',
                'vehicles.model',
                DB::raw('SUM(fuel_transactions.total_amount) as fuel_costs')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_no', 'vehicles.make', 'vehicles.model')
            ->orderByDesc('fuel_costs')
            ->limit(10)
            ->get();

        // Add trip expenses to the same vehicles
        foreach ($topVehicles as $vehicle) {
            $tripExpenses = Trip::where('vehicle_id', $vehicle->id)
                ->join('trip_expenses', 'trips.id', '=', 'trip_expenses.trip_id')
                ->whereBetween('trip_expenses.created_at', [$startDate, $endDate])
                ->sum('trip_expenses.amount');

            $vehicle->trip_expenses = $tripExpenses;

            // Calculate maintenance costs (placeholder - would need maintenance records)
            $vehicle->maintenance_costs = 0;

            $vehicle->total_expenses = $vehicle->fuel_costs + $tripExpenses + $vehicle->maintenance_costs;

            // Calculate insurance cost for this vehicle
            $insurancePolicy = InsurancePolicy::where('vehicle_id', $vehicle->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->first();

            if ($insurancePolicy) {
                $monthDiff = Carbon::parse($startDate)->diffInMonths(Carbon::parse($endDate)) + 1;
                $vehicle->insurance_cost = $insurancePolicy->premium_amount / 12 * $monthDiff;
                $vehicle->total_expenses += $vehicle->insurance_cost;
            } else {
                $vehicle->insurance_cost = 0;
            }

            // Calculate cost to value ratio (placeholder - would need vehicle values)
            $vehicle->cost_to_value_ratio = 0;
        }

        // Re-sort by total cost
        return $topVehicles->sortByDesc('total_expenses')->values();
    }

    public function fuelReports()
    {
        // Get date range from request or default to last 30 days
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        // Get fuel report metrics
        $summaryMetrics = $this->getFuelSummaryMetrics($startDate, $endDate);
        $consumptionByType = $this->getConsumptionByType($startDate, $endDate);
        $consumptionTrend = $this->getConsumptionTrend($startDate, $endDate);
        $topConsumers = $this->getTopFuelConsumers($startDate, $endDate);
        $fuelEfficiency = $this->getFuelEfficiency($startDate, $endDate);

        return view('admin.financial-management.fuel-reports', compact(
            'summaryMetrics',
            'consumptionByType',
            'consumptionTrend',
            'topConsumers',
            'fuelEfficiency',
            'startDate',
            'endDate'
        ));
    }

    private function getFuelSummaryMetrics($startDate, $endDate)
    {
        $query = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate]);

        $totalCost = $query->sum('total_amount');
        $totalLiters = $query->sum('liters');
        $totalTransactions = $query->count();
        $avgPricePerLiter = $totalLiters > 0 ? $totalCost / $totalLiters : 0;

        return [
            'total_cost' => $totalCost,
            'total_liters' => $totalLiters,
            'total_transactions' => $totalTransactions,
            'avg_price_per_liter' => $avgPricePerLiter,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    private function getConsumptionByType($startDate, $endDate)
    {
        return FuelTransaction::join('vehicles', 'fuel_transactions.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.type_id', '=', 'vehicle_types.id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select('vehicle_types.name as type', DB::raw('SUM(fuel_transactions.liters) as total_liters'))
            ->groupBy('vehicle_types.name')
            ->get();
    }

    private function getConsumptionTrend($startDate, $endDate)
    {
        return FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                DB::raw("to_char(transaction_date, 'YYYY-MM') as month"),
                DB::raw('SUM(liters) as total_liters')
            )
            ->groupBy(DB::raw("to_char(transaction_date, 'YYYY-MM')"))
            ->orderBy('month')
            ->get();
    }

    private function getTopFuelConsumers($startDate, $endDate)
    {
        return Vehicle::join('fuel_transactions', 'vehicles.id', '=', 'fuel_transactions.vehicle_id')
            ->whereBetween('fuel_transactions.transaction_date', [$startDate, $endDate])
            ->select(
                'vehicles.registration_no',
                'vehicles.make',
                'vehicles.model',
                DB::raw('SUM(fuel_transactions.liters) as total_liters'),
                DB::raw('SUM(fuel_transactions.total_amount) as total_cost'),
                DB::raw('CASE WHEN SUM(fuel_transactions.liters) > 0 THEN SUM(fuel_transactions.total_amount) / SUM(fuel_transactions.liters) ELSE 0 END as avg_price_per_liter')
            )
            ->groupBy('vehicles.registration_no', 'vehicles.make', 'vehicles.model')
            ->orderByDesc('total_liters')
            ->limit(10)
            ->get();
    }

    private function getFuelEfficiency($startDate, $endDate)
    {
        // This requires odometer readings from trips or vehicle logs
        // This is a placeholder implementation
        return collect([]);
    }


    public function fuelCards()
    {
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        $activeCards = FuelCard::where('status', 'active')->count();
        $expiringSoonQuery = FuelCard::where('status', 'active')->whereBetween('expiry_date', [now(), now()->addDays(30)]);
        $totalTransactions = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])->count();
        $totalSpent = FuelTransaction::whereBetween('transaction_date', [$startDate, $endDate])->sum('total_amount');

        // Define placeholder for high utilization cards
        $highUtilizationCards = collect([]);

        $summaryMetrics = [
            'active_fuel_cards' => $activeCards,
            'expiring_soon' => $expiringSoonQuery->count(),
            'total_transactions' => $totalTransactions,
            'total_spent' => $totalSpent,
            'high_utilization' => $highUtilizationCards->count(),
        ];

        $fuelCardsQuery = FuelCard::with('provider');

        if (request('search')) {
            $searchTerm = request('search');
            $fuelCardsQuery->where(function ($query) use ($searchTerm) {
                $query->where('card_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('holder_name', 'like', '%' . $searchTerm . '%');
            });
        }

        $fuelCards = $fuelCardsQuery->paginate(10);
        $fuelCards->appends(request()->query());

        $expiringCards = $expiringSoonQuery->with('provider')->get();
        $recentTransactions = FuelTransaction::with('vehicle', 'fuelCard')->orderBy('transaction_date', 'desc')->limit(10)->get();

        return view('admin.financial-management.fuel-cards', compact(
            'summaryMetrics',
            'fuelCards',
            'expiringCards',
            'highUtilizationCards',
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }

    public function tripExpenses()
    {
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        $totalExpenses = TripExpense::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $totalTripsWithExpenses = TripExpense::whereBetween('created_at', [$startDate, $endDate])->distinct('trip_id')->count();
        $averageExpensePerTrip = $totalTripsWithExpenses > 0 ? $totalExpenses / $totalTripsWithExpenses : 0;

        $summaryMetrics = [
            'total_expenses' => $totalExpenses,
            'total_trips_with_expenses' => $totalTripsWithExpenses,
            'average_expense_per_trip' => $averageExpensePerTrip,
        ];

        $expenseBreakdown = TripExpense::join('trips', 'trip_expenses.trip_id', '=', 'trips.id')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->whereBetween('trip_expenses.created_at', [$startDate, $endDate])
            ->select('vehicles.registration_no', DB::raw('SUM(trip_expenses.amount) as total_amount'))
            ->groupBy('vehicles.registration_no')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $expenseCategories = TripExpense::whereBetween('created_at', [$startDate, $endDate])
            ->select('category', DB::raw('count(*) as count, sum(amount) as total_amount'))
            ->groupBy('category')
            ->get();

        $topSpendingTrips = TripExpense::with('trip')
            ->select('trip_id', DB::raw('COUNT(*) as expense_count'), DB::raw('SUM(amount) as total_amount'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('trip_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $recentExpenses = TripExpense::with('trip.vehicle')->orderBy('created_at', 'desc')->paginate(10);

        // Find expenses that do not have an approval entry or where the approval status is 'pending'
        $pendingExpenses = TripExpense::whereDoesntHave('expenseApproval')
            ->orWhereHas('expenseApproval', function ($query) {
                $query->where('status', 'pending');
            })
            ->with('trip.vehicle')
            ->get();

        return view('admin.financial-management.trip-expenses', compact(
            'summaryMetrics',
            'expenseBreakdown',
            'expenseCategories',
            'topSpendingTrips',
            'recentExpenses',
            'pendingExpenses',
            'startDate',
            'endDate'
        ));
    }

    public function insurance()
    {
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        $activePolicies = InsurancePolicy::where('status', true)->where('end_date', '>=', now())->count();
        $expiringSoon = InsurancePolicy::where('status', true)->whereBetween('end_date', [now(), now()->addDays(30)])->count();
        $totalPremiums = InsurancePolicy::where('status', true)->sum('premium_amount');
        $averagePremium = $activePolicies > 0 ? $totalPremiums / $activePolicies : 0;

        $insuranceCosts = InsurancePolicy::where('status', true)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->sum('premium_amount');

        $summaryMetrics = [
            'active_policies' => $activePolicies,
            'expiring_soon' => $expiringSoon,
            'total_premiums' => $totalPremiums,
            'average_premium' => $averagePremium,
            'insurance_costs' => $insuranceCosts,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];

        $insurancePolicies = InsurancePolicy::with('vehicle', 'provider')->paginate(10);

        $claims = collect([]); // Placeholder for claims data
        $expiringPolicies = InsurancePolicy::where('status', true)
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->get();
        $uninsuredVehicles = Vehicle::whereDoesntHave('insurancePolicies', function ($query) {
            $query->where('status', true)
                ->where('end_date', '>=', now());
        })->get();
        $expiredPolicies = collect([]);
        $activeClaims = collect([]);

        return view('admin.financial-management.insurance', compact(
            'summaryMetrics',
            'insurancePolicies',
            'claims',
            'expiringPolicies',
            'uninsuredVehicles',
            'expiredPolicies',
            'activeClaims'
        ));
    }

    // Helper methods for getting metrics and data
    private function getFuelCardMetrics($startDate, $endDate)
    {
        // Implementation
    }

    private function getFuelCardTransactions($startDate, $endDate)
    {
        // Implementation
    }

    private function getTripExpenseMetrics($startDate, $endDate)
    {
        // Implementation
    }

    private function getTripExpenses($startDate, $endDate)
    {
        // Implementation
    }

    private function getInsuranceMetrics($startDate, $endDate)
    {
        // Implementation
    }

    private function getInsurancePolicies($startDate, $endDate)
    {
        // Implementation
    }
}
