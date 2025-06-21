<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverLicense;
use App\Models\DriverStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DriverAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'overview');

        // Define default license stats to prevent undefined variable error
        $licenseStatsQuery = DriverLicense::whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('driver_licenses')
                ->groupBy('driver_id');
        })
            ->select(
                DB::raw('COUNT(*) as total_licenses'),
                DB::raw('COUNT(CASE WHEN expiry_date > now() THEN 1 END) as valid'),
                DB::raw('COUNT(CASE WHEN expiry_date <= now() + interval \'30 days\' AND expiry_date > now() THEN 1 END) as expiring_soon'),
                DB::raw('COUNT(CASE WHEN expiry_date <= now() THEN 1 END) as expired')
            )
            ->first();

        // Global licenseStats variable
        view()->share('licenseStats', [
            'valid' => $licenseStatsQuery->valid ?? 0,
            'expiring_soon' => $licenseStatsQuery->expiring_soon ?? 0,
            'expired' => $licenseStatsQuery->expired ?? 0,
            'total' => $licenseStatsQuery->total_licenses ?? 0
        ]);

        // Get licenses with pagination to prevent undefined variable
        $licenses = DriverLicense::with('driver')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('driver_licenses')
                    ->groupBy('driver_id');
            })
            ->latest()
            ->paginate(10);
        view()->share('licenses', $licenses);

        // Get renewal reminders to prevent undefined variable
        $renewalReminders = DriverLicense::with('driver')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($license) {
                return [
                    'driver' => [
                        'name' => $license->driver ? $license->driver->first_name . ' ' . $license->driver->last_name : 'Unknown Driver',
                        'employee_id' => $license->driver ? $license->driver->employee_id : 'N/A',
                        'avatar_url' => $license->driver ? $license->driver->avatar_url : asset('images/default-avatar.png'),
                    ],
                    'days_until_expiry' => now()->diffInDays($license->expiry_date),
                    'license_number' => $license->license_number,
                    'expiry_date' => $license->expiry_date,
                    'status' => $license->status,
                ];
            });
        view()->share('renewalReminders', $renewalReminders);

        // Add date range variables to prevent undefined variable errors
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());
        view()->share('startDate', $startDate);
        view()->share('endDate', $endDate);

        // Add status statistics to prevent undefined variable error
        $statusStats = [
            'active' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Active');
            })->count(),
            'on_leave' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'On Leave');
            })->count(),
            'suspended' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Suspended');
            })->count(),
            'terminated' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Interdiction');
            })->count(),
        ];
        view()->share('statusStats', $statusStats);

        // Add overview statistics to prevent undefined variable error
        $statistics = [
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Active');
            })->count(),
            'drivers_on_leave' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'On Leave');
            })->count(),
            'expiring_licenses' => DriverLicense::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count(),
        ];
        view()->share('statistics', $statistics);

        // Get status distribution for charts
        $statusDistribution = DriverStatus::select('status as name', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        view()->share('statusDistribution', $statusDistribution);

        // Get license status for charts
        $licenseStatus = DriverLicense::select('status', DB::raw('count(*) as count'))
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('driver_licenses')
                    ->groupBy('driver_id');
            })
            ->groupBy('status')
            ->get();
        view()->share('licenseStatus', $licenseStatus);

        // Get status history to prevent undefined variable error
        // Use an empty collection for status history to avoid showing mock data
        $statusHistory = collect();
        view()->share('statusHistory', $statusHistory);

        // Use an empty collection for current status to avoid showing mock data
        $currentStatus = collect();
        view()->share('currentStatus', $currentStatus);

        // Always use an empty collection for recent activity to avoid showing mock data
        $recentActivity = collect();
        view()->share('recentActivity', $recentActivity);

        // Add performance statistics with zero values to prevent undefined variable error
        $performanceStats = [
            'average_rating' => 0,
            'total_trips' => 0,
            'safety_score' => 0,
            'fuel_efficiency' => 0,
        ];

        // No need to query the database, just use the default values
        view()->share('performanceStats', $performanceStats);

        // Get performance metrics
        // Use an empty collection for performance metrics
        $performanceMetrics = collect();
        view()->share('performanceMetrics', $performanceMetrics);

        // Use an empty collection for recent ratings
        $recentRatings = collect();
        view()->share('recentRatings', $recentRatings);

        // Add empty collections for charts
        $ratingTrends = collect();
        view()->share('ratingTrends', $ratingTrends);

        $performanceDistribution = [];
        view()->share('performanceDistribution', $performanceDistribution);

        // Share all the empty collections with the view
        view()->share('performanceStats', $performanceStats);
        view()->share('performanceMetrics', $performanceMetrics);
        view()->share('recentRatings', $recentRatings);

        switch ($tab) {
            case 'overview':
                return $this->overview($request);
            case 'licenses':
                return $this->licenses($request);
            case 'status':
                return $this->status($request);
            case 'performance':
                return $this->performance($request);
            default:
                return $this->overview($request);
        }
    }

    protected function overview(Request $request)
    {
        $tab = 'overview';

        // Get date range from request or use default
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get statistics
        $statistics = [
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'Active');
            })->count(),
            'drivers_on_leave' => Driver::whereHas('currentStatus', function ($query) {
                $query->where('status', 'On Leave');
            })->count(),
            'expiring_licenses' => DriverLicense::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count(),
        ];

        // Get status distribution
        $statusDistribution = DriverStatus::select('status as name', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Get license status
        $licenseStatus = DriverLicense::select('status', DB::raw('count(*) as count'))
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('driver_licenses')
                    ->groupBy('driver_id');
            })
            ->groupBy('status')
            ->get();

        // We'll use the recentActivity that was already defined in the index method
        // This prevents duplicating the data and ensures consistency

        return view('admin.driver-analytics', compact(
            'tab',
            'statistics',
            'statusDistribution',
            'licenseStatus'
            // 'recentActivity' is already shared with the view in the index method
        ));
    }

    protected function licenses(Request $request)
    {
        $tab = 'licenses';
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        // Use the empty collections that were already defined in the index method
        // This prevents duplicating the data and ensures consistency

        return view('admin.driver-analytics', compact(
            'tab'
            // 'licenseStats', 'licenses', 'renewalReminders', 'startDate', and 'endDate' are already shared with the view in the index method
        ));
    }

    protected function status(Request $request)
    {
        $tab = 'status';

        // Use the empty collections that were already defined in the index method
        // This prevents duplicating the data and ensures consistency

        return view('admin.driver-analytics', compact(
            'tab'
            // 'statusStats', 'statusHistory', and 'currentStatus' are already shared with the view in the index method
        ));
    }

    protected function performance(Request $request)
    {
        $tab = 'performance';

        // Use the empty collections that were already defined in the index method
        // This prevents duplicating the data and ensures consistency

        return view('admin.driver-analytics', compact(
            'tab'
            // 'performanceStats', 'performanceMetrics', and 'recentRatings' are already shared with the view in the index method
        ));
    }
}
