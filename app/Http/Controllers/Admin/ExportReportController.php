<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

// Models
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\MaintenanceRecord;
use App\Models\FuelRecord;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\FuelCard;
use App\Models\InsurancePolicy;
use App\Models\VehicleAssignment;

class ExportReportController extends Controller
{
    /**
     * Display the export reports page
     */
    public function index(Request $request)
    {
        try {
            // Get date range from request or default to last 30 days
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))
                : now()->subDays(30);
            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))
                : now();

            // Get report types
            $reportTypes = $this->getReportTypes();

            // Get current report type
            $currentReportType = $request->get('report_type', '');

            // Get preview data for selected report type
            $previewData = [];
            $headers = [];

            if ($currentReportType) {
                $previewData = $this->getPreviewData($currentReportType, $startDate, $endDate);
                $headers = $this->getHeaders($currentReportType);
            }

            // Get drivers for driver-specific reports - Fixed to use correct column names
            $drivers = Driver::select('id', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get();

            // If no drivers found, create an empty collection to prevent errors
            if ($drivers->isEmpty()) {
                $drivers = collect([]);
            }

            // Get summary statistics
            $summaryStats = $this->getSummaryStatistics($startDate, $endDate);

            return view('admin.export-reports', compact(
                'reportTypes',
                'currentReportType',
                'previewData',
                'headers',
                'startDate',
                'endDate',
                'drivers',
                'summaryStats'
            ));
        } catch (\Exception $e) {
            Log::error('Export reports error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading the export reports page.');
        }
    }

    /**
     * Generate and download the selected report
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'report_type' => 'required|string',
                'format' => 'required|in:csv,excel,pdf',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'driver_id' => 'nullable|exists:drivers,id'
            ]);

            $reportType = $request->report_type;
            $format = $request->format;
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $driverId = $request->driver_id;

            // Get report data
            $data = $this->getReportData($reportType, $startDate, $endDate, $driverId);
            $headers = $this->getHeaders($reportType);

            if (empty($data)) {
                return back()->with('warning', 'No data found for the selected criteria.');
            }

            // Generate filename
            $filename = $this->generateFilename($reportType, $startDate, $endDate, $format);

            // Generate and return the file
            return $this->generateFile($data, $headers, $filename, $format, $reportType);
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while generating the report.');
        }
    }

    /**
     * Get available report types
     */
    private function getReportTypes(): array
    {
        return [
            'trips' => [
                'name' => 'Trip Reports',
                'description' => 'Detailed trip information including routes, drivers, and vehicles',
                'icon' => 'fas fa-route'
            ],
            'maintenance' => [
                'name' => 'Maintenance Reports',
                'description' => 'Vehicle maintenance records and service history',
                'icon' => 'fas fa-tools'
            ],
            'fuel' => [
                'name' => 'Fuel Reports',
                'description' => 'Fuel consumption and transaction records',
                'icon' => 'fas fa-gas-pump'
            ],
            'vehicles' => [
                'name' => 'Vehicle Reports',
                'description' => 'Vehicle inventory and status information',
                'icon' => 'fas fa-car'
            ],
            'drivers' => [
                'name' => 'Driver Reports',
                'description' => 'Driver information and performance metrics',
                'icon' => 'fas fa-user-tie'
            ],
            'incidents' => [
                'name' => 'Incident Reports',
                'description' => 'Safety incidents and accident reports',
                'icon' => 'fas fa-exclamation-triangle'
            ],
            'financial' => [
                'name' => 'Financial Reports',
                'description' => 'Cost analysis and financial summaries',
                'icon' => 'fas fa-chart-line'
            ],
            'fuel_cards' => [
                'name' => 'Fuel Card Reports',
                'description' => 'Fuel card usage and transaction history',
                'icon' => 'fas fa-credit-card'
            ],
            'insurance' => [
                'name' => 'Insurance Reports',
                'description' => 'Insurance policies and coverage information',
                'icon' => 'fas fa-shield-alt'
            ],
            'assignments' => [
                'name' => 'Assignment Reports',
                'description' => 'Vehicle and driver assignment history',
                'icon' => 'fas fa-clipboard-list'
            ]
        ];
    }

    /**
     * Get headers for specific report type
     */
    private function getHeaders(string $reportType): array
    {
        $headers = [
            'trips' => [
                'Trip ID',
                'Vehicle',
                'Driver',
                'Start Date',
                'End Date',
                'Distance (km)',
                'Duration',
                'Status',
                'Purpose',
                'Cost'
            ],
            'maintenance' => [
                'Record ID',
                'Vehicle',
                'Type',
                'Service Date',
                'Cost',
                'Description',
                'Status',
                'Provider',
                'Next Service'
            ],
            'fuel' => [
                'Transaction ID',
                'Vehicle',
                'Date',
                'Liters',
                'Cost',
                'Station',
                'Fuel Type',
                'Driver'
            ],
            'vehicles' => [
                'Vehicle ID',
                'Registration',
                'Make',
                'Model',
                'Year',
                'Status',
                'Department',
                'Office',
                'Current Driver'
            ],
            'drivers' => [
                'Driver ID',
                'Name',
                'License Number',
                'Status',
                'Department',
                'Total Trips',
                'Total Distance',
                'Performance Rating'
            ],
            'incidents' => [
                'Incident ID',
                'Date',
                'Vehicle',
                'Driver',
                'Type',
                'Severity',
                'Status',
                'Description',
                'Cost'
            ],
            'financial' => [
                'Period',
                'Category',
                'Total Cost',
                'Vehicle Count',
                'Driver Count',
                'Average Cost',
                'Budget vs Actual'
            ],
            'fuel_cards' => [
                'Card ID',
                'Card Number',
                'Vehicle',
                'Driver',
                'Provider',
                'Status',
                'Credit Limit',
                'Current Balance',
                'Last Transaction'
            ],
            'insurance' => [
                'Policy ID',
                'Vehicle',
                'Provider',
                'Policy Number',
                'Start Date',
                'End Date',
                'Premium',
                'Coverage Type',
                'Status'
            ],
            'assignments' => [
                'Assignment ID',
                'Vehicle',
                'Driver',
                'Start Date',
                'End Date',
                'Status',
                'Department',
                'Notes'
            ]
        ];

        return $headers[$reportType] ?? [];
    }

    /**
     * Get preview data for selected report type
     */
    private function getPreviewData(string $reportType, Carbon $startDate, Carbon $endDate): array
    {
        $data = $this->getReportData($reportType, $startDate, $endDate);

        // Return only first 5 records for preview
        return array_slice($data, 0, 5);
    }

    /**
     * Get full report data
     */
    private function getReportData(string $reportType, Carbon $startDate, Carbon $endDate, $driverId = null): array
    {
        switch ($reportType) {
            case 'trips':
                return $this->getTripData($startDate, $endDate, $driverId);
            case 'maintenance':
                return $this->getMaintenanceData($startDate, $endDate);
            case 'fuel':
                return $this->getFuelData($startDate, $endDate, $driverId);
            case 'vehicles':
                return $this->getVehicleData();
            case 'drivers':
                return $this->getDriverData($startDate, $endDate);
            case 'incidents':
                return $this->getIncidentData($startDate, $endDate);
            case 'financial':
                return $this->getFinancialData($startDate, $endDate);
            case 'fuel_cards':
                return $this->getFuelCardData();
            case 'insurance':
                return $this->getInsuranceData();
            case 'assignments':
                return $this->getAssignmentData($startDate, $endDate);
            default:
                return [];
        }
    }

    /**
     * Get trip data
     */
    private function getTripData(Carbon $startDate, Carbon $endDate, $driverId = null): array
    {
        $query = Trip::with(['vehicle', 'driver', 'status', 'purpose'])
            ->whereBetween('start_time', [$startDate, $endDate]);

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        return $query->get()->map(function ($trip) {
            $duration = $trip->start_time && $trip->end_time
                ? $trip->start_time->diffInHours($trip->end_time) . ' hours'
                : 'N/A';

            return [
                $trip->id,
                $trip->vehicle?->registration_no ?? 'N/A',
                $trip->driver ? trim($trip->driver->first_name . ' ' . $trip->driver->last_name) : 'N/A',
                $trip->start_time?->format('Y-m-d H:i') ?? 'N/A',
                $trip->end_time?->format('Y-m-d H:i') ?? 'N/A',
                $trip->distance ? number_format($trip->distance, 2) : 'N/A',
                $duration,
                $trip->status?->name ?? 'N/A',
                $trip->purpose?->name ?? 'N/A',
                $trip->cost ? number_format($trip->cost, 2) : 'N/A'
            ];
        })->toArray();
    }

    /**
     * Get maintenance data
     */
    private function getMaintenanceData(Carbon $startDate, Carbon $endDate): array
    {
        return MaintenanceRecord::with(['vehicle', 'type', 'status', 'provider'])
            ->whereBetween('service_date', [$startDate, $endDate])
            ->get()
            ->map(function ($record) {
                return [
                    $record->id,
                    $record->vehicle?->registration_no ?? 'N/A',
                    $record->type?->name ?? 'N/A',
                    $record->service_date?->format('Y-m-d') ?? 'N/A',
                    $record->estimated_cost ? number_format($record->estimated_cost, 2) : 'N/A',
                    $record->description ?? 'N/A',
                    $record->status?->name ?? 'N/A',
                    $record->provider?->name ?? 'N/A',
                    $record->next_service_date?->format('Y-m-d') ?? 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get fuel data
     */
    private function getFuelData(Carbon $startDate, Carbon $endDate, $driverId = null): array
    {
        $query = FuelRecord::with(['vehicle', 'station', 'fuel_type', 'driver'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        return $query->get()->map(function ($record) {
            return [
                $record->id,
                $record->vehicle?->registration_no ?? 'N/A',
                $record->transaction_date?->format('Y-m-d H:i') ?? 'N/A',
                $record->liters ? number_format($record->liters, 2) : 'N/A',
                $record->cost ? number_format($record->cost, 2) : 'N/A',
                $record->station?->name ?? 'N/A',
                $record->fuel_type?->name ?? 'N/A',
                $record->driver ? trim($record->driver->first_name . ' ' . $record->driver->last_name) : 'N/A'
            ];
        })->toArray();
    }

    /**
     * Get vehicle data
     */
    private function getVehicleData(): array
    {
        return Vehicle::with(['type', 'status', 'department', 'office'])
            ->get()
            ->map(function ($vehicle) {
                return [
                    $vehicle->id,
                    $vehicle->registration_no ?? 'N/A',
                    $vehicle->make ?? 'N/A',
                    $vehicle->model ?? 'N/A',
                    $vehicle->year ?? 'N/A',
                    $vehicle->status?->name ?? 'N/A',
                    $vehicle->department?->name ?? 'N/A',
                    $vehicle->office?->name ?? 'N/A',
                    'N/A' // Current driver placeholder
                ];
            })->toArray();
    }

    /**
     * Get driver data
     */
    private function getDriverData(Carbon $startDate, Carbon $endDate): array
    {
        return Driver::with(['status', 'department', 'license'])
            ->get()
            ->map(function ($driver) use ($startDate, $endDate) {
                // Get trip statistics
                $trips = Trip::where('driver_id', $driver->id)
                    ->whereBetween('start_time', [$startDate, $endDate]);

                $totalTrips = $trips->count();
                $totalDistance = $trips->sum('distance');

                return [
                    $driver->id,
                    trim($driver->first_name . ' ' . $driver->last_name),
                    $driver->license?->license_number ?? 'N/A',
                    $driver->status?->name ?? 'N/A',
                    $driver->department?->name ?? 'N/A',
                    $totalTrips,
                    $totalDistance ? number_format($totalDistance, 2) . ' km' : 'N/A',
                    'N/A' // Performance rating placeholder
                ];
            })->toArray();
    }

    /**
     * Get incident data
     */
    private function getIncidentData(Carbon $startDate, Carbon $endDate): array
    {
        return Incident::with(['vehicle', 'driver', 'type', 'severity', 'status'])
            ->whereBetween('incident_date', [$startDate, $endDate])
            ->get()
            ->map(function ($incident) {
                return [
                    $incident->id,
                    $incident->incident_date?->format('Y-m-d') ?? 'N/A',
                    $incident->vehicle?->registration_no ?? 'N/A',
                    $incident->driver ? trim($incident->driver->first_name . ' ' . $incident->driver->last_name) : 'N/A',
                    $incident->type?->name ?? 'N/A',
                    $incident->severity?->name ?? 'N/A',
                    $incident->status?->name ?? 'N/A',
                    $incident->description ?? 'N/A',
                    $incident->estimated_cost ? number_format($incident->estimated_cost, 2) : 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get financial data
     */
    private function getFinancialData(Carbon $startDate, Carbon $endDate): array
    {
        $data = [];

        // Fuel costs
        $fuelCosts = FuelRecord::whereBetween('transaction_date', [$startDate, $endDate])->sum('cost');
        $data[] = [
            $startDate->format('Y-m') . ' - ' . $endDate->format('Y-m'),
            'Fuel Costs',
            number_format($fuelCosts, 2),
            Vehicle::count(),
            Driver::count(),
            number_format($fuelCosts / max(Vehicle::count(), 1), 2),
            'N/A'
        ];

        // Maintenance costs
        $maintenanceCosts = MaintenanceRecord::whereBetween('service_date', [$startDate, $endDate])->sum('estimated_cost');
        $data[] = [
            $startDate->format('Y-m') . ' - ' . $endDate->format('Y-m'),
            'Maintenance Costs',
            number_format($maintenanceCosts, 2),
            Vehicle::count(),
            Driver::count(),
            number_format($maintenanceCosts / max(Vehicle::count(), 1), 2),
            'N/A'
        ];

        // Trip costs
        $tripCosts = Trip::whereBetween('start_time', [$startDate, $endDate])->sum('cost');
        $data[] = [
            $startDate->format('Y-m') . ' - ' . $endDate->format('Y-m'),
            'Trip Costs',
            number_format($tripCosts, 2),
            Vehicle::count(),
            Driver::count(),
            number_format($tripCosts / max(Vehicle::count(), 1), 2),
            'N/A'
        ];

        return $data;
    }

    /**
     * Get fuel card data
     */
    private function getFuelCardData(): array
    {
        return FuelCard::with(['vehicle', 'driver', 'provider', 'status'])
            ->get()
            ->map(function ($card) {
                return [
                    $card->id,
                    $card->card_number ?? 'N/A',
                    $card->vehicle?->registration_no ?? 'N/A',
                    $card->driver ? trim($card->driver->first_name . ' ' . $card->driver->last_name) : 'N/A',
                    $card->provider?->name ?? 'N/A',
                    $card->status?->name ?? 'N/A',
                    $card->credit_limit ? number_format($card->credit_limit, 2) : 'N/A',
                    $card->current_balance ? number_format($card->current_balance, 2) : 'N/A',
                    $card->last_transaction_date?->format('Y-m-d') ?? 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get insurance data
     */
    private function getInsuranceData(): array
    {
        return InsurancePolicy::with(['vehicle', 'provider'])
            ->get()
            ->map(function ($policy) {
                return [
                    $policy->id,
                    $policy->vehicle?->registration_no ?? 'N/A',
                    $policy->provider?->name ?? 'N/A',
                    $policy->policy_number ?? 'N/A',
                    $policy->start_date?->format('Y-m-d') ?? 'N/A',
                    $policy->end_date?->format('Y-m-d') ?? 'N/A',
                    $policy->premium ? number_format($policy->premium, 2) : 'N/A',
                    $policy->coverage_type ?? 'N/A',
                    $policy->status ?? 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get assignment data
     */
    private function getAssignmentData(Carbon $startDate, Carbon $endDate): array
    {
        return VehicleAssignment::with(['vehicle', 'driver', 'department'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get()
            ->map(function ($assignment) {
                return [
                    $assignment->id,
                    $assignment->vehicle?->registration_no ?? 'N/A',
                    $assignment->driver ? trim($assignment->driver->first_name . ' ' . $assignment->driver->last_name) : 'N/A',
                    $assignment->start_date?->format('Y-m-d') ?? 'N/A',
                    $assignment->end_date?->format('Y-m-d') ?? 'N/A',
                    $assignment->status ?? 'N/A',
                    $assignment->department?->name ?? 'N/A',
                    $assignment->notes ?? 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_trips' => Trip::whereBetween('start_time', [$startDate, $endDate])->count(),
            'total_maintenance' => MaintenanceRecord::whereBetween('service_date', [$startDate, $endDate])->count(),
            'total_fuel_transactions' => FuelRecord::whereBetween('transaction_date', [$startDate, $endDate])->count(),
            'total_incidents' => Incident::whereBetween('incident_date', [$startDate, $endDate])->count(),
            'active_vehicles' => Vehicle::count(),
            'active_drivers' => Driver::where('status', 'Active')->count(),
            'total_fuel_cost' => FuelRecord::whereBetween('transaction_date', [$startDate, $endDate])->sum('cost'),
            'total_maintenance_cost' => MaintenanceRecord::whereBetween('service_date', [$startDate, $endDate])->sum('estimated_cost')
        ];
    }

    /**
     * Generate filename for export
     */
    private function generateFilename(string $reportType, Carbon $startDate, Carbon $endDate, string $format): string
    {
        $reportName = str_replace('_', '-', $reportType);
        $dateRange = $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d');
        return "{$reportName}_report_{$dateRange}.{$format}";
    }

    /**
     * Generate and return the export file
     */
    private function generateFile(array $data, array $headers, string $filename, string $format, string $reportType)
    {
        switch ($format) {
            case 'csv':
                return $this->generateCsv($data, $headers, $filename);
            case 'excel':
                return $this->generateExcel($data, $headers, $filename, $reportType);
            case 'pdf':
                return $this->generatePdf($data, $headers, $filename, $reportType);
            default:
                throw new \InvalidArgumentException('Unsupported format');
        }
    }

    /**
     * Generate CSV file
     */
    private function generateCsv(array $data, array $headers, string $filename)
    {
        $handle = fopen('php://temp', 'r+');

        // Add headers
        fputcsv($handle, $headers);

        // Add data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate Excel file
     */
    private function generateExcel(array $data, array $headers, string $filename, string $reportType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add title
        $sheet->setCellValue('A1', strtoupper(str_replace('_', ' ', $reportType)) . ' REPORT');
        $sheet->mergeCells('A1:' . chr(65 + count($headers) - 1) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Add headers
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '3', $header);
            $sheet->getStyle(chr(65 + $index) . '3')->getFont()->setBold(true);
        }

        // Add data
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . ($rowIndex + 4), $value);
            }
        }

        // Auto-size columns
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return Response::download($tempFile, $filename)->deleteFileAfterSend();
    }

    /**
     * Generate PDF file
     */
    private function generatePdf(array $data, array $headers, string $filename, string $reportType)
    {
        $reportTitle = strtoupper(str_replace('_', ' ', $reportType)) . ' REPORT';

        $pdf = PDF::loadView('admin.reports.pdf-template', [
            'title' => $reportTitle,
            'headers' => $headers,
            'data' => $data,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ]);

        return $pdf->download($filename);
    }
}
