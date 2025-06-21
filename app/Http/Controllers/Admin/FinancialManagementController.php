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
            ->where('status', 'active')
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
            ->where('status', 'active')
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
            ->where('status', 'active')
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

        // Get fuel metrics
        $fuelMetrics = $this->getFuelMetrics($startDate, $endDate);

        // Get monthly fuel trends
        $monthlyFuelTrends = $this->getMonthlyFuelTrends($startDate, $endDate);

        // Get top fuel consuming vehicles
        $topFuelVehicles = $this->getTopFuelVehicles($startDate, $endDate);

        return view('admin.financial-management.fuel-reports', compact(
            'fuelMetrics',
            'monthlyFuelTrends',
            'topFuelVehicles',
            'startDate',
            'endDate'
        ));
    }

    public function fuelCards()
    {
        // Get date range from request or default to last 30 days
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        // Get fuel card metrics
        $fuelCardMetrics = $this->getFuelCardMetrics($startDate, $endDate);

        // Get fuel card transactions
        $fuelCardTransactions = $this->getFuelCardTransactions($startDate, $endDate);

        return view('admin.financial-management.fuel-cards', compact(
            'fuelCardMetrics',
            'fuelCardTransactions',
            'startDate',
            'endDate'
        ));
    }

    public function tripExpenses()
    {
        // Get date range from request or default to last 30 days
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        // Get trip expense metrics
        $tripExpenseMetrics = $this->getTripExpenseMetrics($startDate, $endDate);

        // Get trip expenses
        $tripExpenses = $this->getTripExpenses($startDate, $endDate);

        return view('admin.financial-management.trip-expenses', compact(
            'tripExpenseMetrics',
            'tripExpenses',
            'startDate',
            'endDate'
        ));
    }

    public function insurance()
    {
        // Get date range from request or default to last 30 days
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : $endDate->copy()->subDays(30);

        // Get insurance metrics
        $insuranceMetrics = $this->getInsuranceMetrics($startDate, $endDate);

        // Get insurance policies
        $insurancePolicies = $this->getInsurancePolicies($startDate, $endDate);

        return view('admin.financial-management.insurance', compact(
            'insuranceMetrics',
            'insurancePolicies',
            'startDate',
            'endDate'
        ));
    }

    // Helper methods for getting metrics and data
    private function getFuelMetrics($startDate, $endDate)
    {
        // Implementation
    }

    private function getMonthlyFuelTrends($startDate, $endDate)
    {
        // Implementation
    }

    private function getTopFuelVehicles($startDate, $endDate)
    {
        // Implementation
    }

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
