<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Trip;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\MaintenanceType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\IncidentSeverity;
use App\Models\IncidentType;
use App\Models\IncidentStatus;
use App\Models\InsurancePolicy;
use App\Models\DriverLicense;
use App\Models\VehicleDocument;
use App\Models\Route;

class SafetyDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or use default
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get statistics
        $stats = [
            'total_incidents' => Incident::whereBetween('incident_date', [$startDate, $endDate])->count(),
            'active_investigations' => Incident::whereHas('status', function ($query) {
                $query->where('name', 'Under Investigation');
            })->count(),
            'resolved_incidents' => Incident::whereHas('status', function ($query) {
                $query->where('name', 'Resolved');
            })->count(),
            'safety_score' => $this->calculateSafetyScore($startDate, $endDate)
        ];

        // Get incident distribution data
        $incidentDistribution = $this->getIncidentDistribution($startDate, $endDate);

        // Get severity analysis data
        $severityAnalysis = $this->getSeverityAnalysis($startDate, $endDate);

        // Get recent incidents
        $recentIncidents = Incident::with(['type', 'severity', 'status'])
            ->latest('incident_date')
            ->take(10)
            ->get();

        return view('admin.safety-dashboard', compact(
            'stats',
            'incidentDistribution',
            'severityAnalysis',
            'recentIncidents'
        ));
    }

    protected function calculateSafetyScore($startDate, $endDate)
    {
        $totalIncidents = Incident::whereBetween('incident_date', [$startDate, $endDate])->count();
        $highSeverityIncidents = Incident::whereBetween('incident_date', [$startDate, $endDate])
            ->whereHas('severity', function ($query) {
                $query->where('level', 'High');
            })
            ->count();

        // Calculate safety score based on total incidents and high severity incidents
        // This is a simple example - you might want to adjust the formula
        $baseScore = 100;
        $totalDeduction = ($totalIncidents * 2) + ($highSeverityIncidents * 5);
        $safetyScore = max(0, $baseScore - $totalDeduction);

        return round($safetyScore);
    }

    protected function getIncidentDistribution($startDate, $endDate)
    {
        $distribution = IncidentType::withCount(['incidents' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('incident_date', [$startDate, $endDate]);
        }])
            ->having('incidents_count', '>', 0)
            ->get();

        return [
            'labels' => $distribution->pluck('name'),
            'data' => $distribution->pluck('incidents_count')
        ];
    }

    protected function getSeverityAnalysis($startDate, $endDate)
    {
        $analysis = IncidentSeverity::withCount(['incidents' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('incident_date', [$startDate, $endDate]);
        }])
            ->having('incidents_count', '>', 0)
            ->orderBy('level')
            ->get();

        return [
            'labels' => $analysis->pluck('level'),
            'data' => $analysis->pluck('incidents_count')
        ];
    }

    public function incidents(Request $request)
    {
        $query = Incident::with(['vehicle', 'driver', 'type', 'severity', 'status']);

        // Apply filters
        if ($request->filled('severity')) {
            $query->where('severity_id', $request->severity);
        }

        if ($request->filled('type')) {
            $query->where('incident_type_id', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('incident_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('incident_date', '<=', $request->date_to);
        }

        $incidents = $query->latest()->paginate(10);
        $severities = IncidentSeverity::all();
        $types = IncidentType::all();
        $statuses = IncidentStatus::all();

        return view('admin.safety.incidents', compact('incidents', 'severities', 'types', 'statuses'));
    }

    public function compliance(Request $request)
    {
        // Get compliance statistics
        $complianceStats = [
            'total_vehicles' => Vehicle::count(),
            'vehicles_with_valid_insurance' => Vehicle::whereHas('insurancePolicies', function ($query) {
                $query->where('end_date', '>', now());
            })->count(),
            'vehicles_with_valid_inspection' => Vehicle::whereHas('maintenanceRecords', function ($query) {
                $query->where('maintenance_type_id', function ($q) {
                    $q->select('id')
                        ->from('maintenance_types')
                        ->where('name', 'like', '%inspection%')
                        ->limit(1);
                })
                    ->where('next_service_date', '>', now());
            })->count(),
            'drivers_with_valid_licenses' => Driver::whereHas('licenses', function ($query) {
                $query->where('expiry_date', '>', now());
            })->count()
        ];

        // Get upcoming compliance deadlines
        $upcomingDeadlines = [
            'insurance_expirations' => InsurancePolicy::where('end_date', '>', now())
                ->where('end_date', '<=', now()->addMonths(3))
                ->with('vehicle')
                ->get(),
            'license_expirations' => DriverLicense::where('expiry_date', '>', now())
                ->where('expiry_date', '<=', now()->addMonths(3))
                ->with('driver')
                ->get(),
            'inspection_expirations' => MaintenanceRecord::whereHas('maintenanceType', function ($query) {
                $query->where('name', 'like', '%inspection%');
            })
                ->where('next_service_date', '>', now())
                ->where('next_service_date', '<=', now()->addMonths(3))
                ->with('vehicle')
                ->get()
        ];

        return view('admin.safety.compliance', compact('complianceStats', 'upcomingDeadlines'));
    }

    public function riskAssessment(Request $request)
    {
        // Get date range from request or use default (last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        // Get risk metrics
        $riskMetrics = [
            'high_risk_vehicles' => Vehicle::whereHas('incidents', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('incident_date', [$startDate, $endDate])
                    ->whereHas('severity', function ($q) {
                        $q->where('name', 'Critical');
                    });
            })->count(),
            'high_risk_drivers' => Driver::whereHas('incidents', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('incident_date', [$startDate, $endDate])
                    ->whereHas('severity', function ($q) {
                        $q->where('name', 'Critical');
                    });
            })->count(),
            'total_incidents' => Incident::whereBetween('incident_date', [$startDate, $endDate])->count(),
            'critical_incidents' => Incident::whereBetween('incident_date', [$startDate, $endDate])
                ->whereHas('severity', function ($q) {
                    $q->where('name', 'Critical');
                })->count()
        ];

        // Get risk trends
        $riskTrends = [
            'incidents_by_severity' => Incident::whereBetween('incident_date', [$startDate, $endDate])
                ->select('severity_id', DB::raw('count(*) as count'))
                ->groupBy('severity_id')
                ->get(),
            'incidents_by_type' => Incident::whereBetween('incident_date', [$startDate, $endDate])
                ->select('incident_type_id', DB::raw('count(*) as count'))
                ->groupBy('incident_type_id')
                ->get()
        ];

        return view('admin.safety.risk-assessment', compact('riskMetrics', 'riskTrends'));
    }

    public function createIncident()
    {
        $vehicles = Vehicle::orderBy('registration_no')->get();
        $drivers = Driver::orderBy('first_name')->orderBy('last_name')->get();
        $types = IncidentType::orderBy('name')->get();
        $severities = IncidentSeverity::orderBy('name')->get();
        $statuses = IncidentStatus::orderBy('name')->get();

        return view('admin.safety.incidents.create', compact('vehicles', 'drivers', 'types', 'severities', 'statuses'));
    }

    public function storeIncident(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'incident_type_id' => 'required|exists:incident_types,id',
            'severity_id' => 'required|exists:incident_severities,id',
            'status_id' => 'required|exists:incident_statuses,id',
            'incident_date' => 'required|date',
            'incident_time' => 'required',
            'location' => 'required|string',
            'description' => 'required|string',
            'injuries' => 'nullable|string',
            'damage_cost' => 'nullable|numeric',
            'insurance_claim_number' => 'nullable|string',
            'police_report_number' => 'nullable|string',
        ]);

        $incident = Incident::create($validated);

        return redirect()->route('admin.safety.incidents')
            ->with('success', 'Incident created successfully.');
    }

    public function showIncident(Incident $incident)
    {
        $incident->load(['vehicle', 'driver', 'type', 'severity', 'status']);
        return view('admin.safety.incidents.show', compact('incident'));
    }

    public function editIncident(Incident $incident)
    {
        $vehicles = Vehicle::orderBy('registration_no')->get();
        $drivers = Driver::orderBy('first_name')->orderBy('last_name')->get();
        $types = IncidentType::orderBy('name')->get();
        $severities = IncidentSeverity::orderBy('name')->get();
        $statuses = IncidentStatus::orderBy('name')->get();

        return view('admin.safety.incidents.edit', compact('incident', 'vehicles', 'drivers', 'types', 'severities', 'statuses'));
    }

    public function updateIncident(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'incident_type_id' => 'required|exists:incident_types,id',
            'severity_id' => 'required|exists:incident_severities,id',
            'status_id' => 'required|exists:incident_statuses,id',
            'incident_date' => 'required|date',
            'incident_time' => 'required',
            'location' => 'required|string',
            'description' => 'required|string',
            'injuries' => 'nullable|string',
            'damage_cost' => 'nullable|numeric',
            'insurance_claim_number' => 'nullable|string',
            'police_report_number' => 'nullable|string',
        ]);

        $incident->update($validated);

        return redirect()->route('admin.safety.incidents')
            ->with('success', 'Incident updated successfully.');
    }
}
