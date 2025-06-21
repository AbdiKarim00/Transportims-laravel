<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\MaintenanceRecord;
use App\Models\FuelRecord;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ExportException;

class ExportReportController extends Controller
{
    private const ACTIVE_STATUS_ID = 1;
    private const DEFAULT_PREVIEW_LIMIT = 5;

    public function index(Request $request)
    {
        try {
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))
                : null;

            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))
                : null;

            $reportTypes = $this->getReportTypes();

            // Get preview data for each report type to show what will be included in the export
            $previewData = [
                'trips' => $startDate && $endDate ? $this->getTripPreviewData($startDate, $endDate) : [],
                'maintenance' => $startDate && $endDate ? $this->getMaintenancePreviewData($startDate, $endDate) : [],
                'fuel' => $startDate && $endDate ? $this->getFuelPreviewData($startDate, $endDate) : [],
                'vehicles' => $this->getVehiclePreviewData(),
                'driver_performance' => [],
                'driver_status' => [],
                'driver_trips' => [],
                'driver_fuel' => [],
                'driver_maintenance' => []
            ];

            // Get all active drivers for the driver selection dropdown
            $drivers = $this->getActiveDrivers();

            // Get headers for each report type
            $headers = $this->getHeaders();

            // Get the current report type from the request or default to empty
            $currentReportType = $request->get('report_type', '');

            return view('admin.export-reports', compact(
                'reportTypes',
                'previewData',
                'startDate',
                'endDate',
                'drivers',
                'headers',
                'currentReportType'
            ));
        } catch (\Exception $e) {
            Log::error('Export report preview error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading the export preview. Please try again.');
        }
    }

    private function getTripPreviewData(Carbon $startDate, Carbon $endDate): array
    {
        return Trip::with(['vehicle', 'driver', 'status'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->latest()
            ->take(self::DEFAULT_PREVIEW_LIMIT)
            ->get()
            ->map(function ($trip) {
                return [
                    'trip_id' => $trip->id,
                    'vehicle' => $trip->vehicle?->registration_no ?? 'N/A',
                    'driver' => $trip->driver
                        ? trim($trip->driver->first_name . ' ' . $trip->driver->last_name)
                        : 'N/A',
                    'start_time' => $trip->start_time?->format('Y-m-d H:i:s') ?? 'N/A',
                    'end_time' => $trip->end_time?->format('Y-m-d H:i:s') ?? 'N/A',
                    'distance' => $trip->distance ? number_format($trip->distance, 2) . ' km' : 'N/A',
                    'status' => $trip->status?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getMaintenancePreviewData(Carbon $startDate, Carbon $endDate): array
    {
        return MaintenanceRecord::with(['vehicle', 'type', 'status'])
            ->whereBetween('service_date', [$startDate, $endDate])
            ->latest()
            ->take(self::DEFAULT_PREVIEW_LIMIT)
            ->get()
            ->map(function ($record) {
                return [
                    'record_id' => $record->id,
                    'vehicle' => $record->vehicle?->registration_no ?? 'N/A',
                    'type' => $record->type?->name ?? 'N/A',
                    'date' => $record->service_date?->format('Y-m-d') ?? 'N/A',
                    'cost' => $record->cost ? number_format($record->cost, 2) : 'N/A',
                    'description' => $record->description ?? 'N/A',
                    'status' => $record->status?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getFuelPreviewData(Carbon $startDate, Carbon $endDate): array
    {
        return FuelRecord::with(['vehicle', 'station'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->latest()
            ->take(self::DEFAULT_PREVIEW_LIMIT)
            ->get()
            ->map(function ($record) {
                return [
                    'transaction_id' => $record->id,
                    'vehicle' => $record->vehicle?->registration_no ?? 'N/A',
                    'date' => $record->transaction_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    'amount' => $record->amount ? number_format($record->amount, 2) : 'N/A',
                    'liters' => $record->liters ? number_format($record->liters, 2) : 'N/A',
                    'cost' => $record->cost ? number_format($record->cost, 2) : 'N/A',
                    'station' => $record->station?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getVehiclePreviewData(): array
    {
        return Vehicle::with(['type', 'status', 'department', 'office'])
            ->latest()
            ->take(self::DEFAULT_PREVIEW_LIMIT)
            ->get()
            ->map(function ($vehicle) {
                return [
                    'vehicle_id' => $vehicle->id,
                    'registration' => $vehicle->registration_no ?? 'N/A',
                    'make' => $vehicle->make ?? 'N/A',
                    'model' => $vehicle->model ?? 'N/A',
                    'status' => $vehicle->status?->name ?? 'N/A',
                    'department' => $vehicle->department?->name ?? 'N/A',
                    'office' => $vehicle->office?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getActiveDrivers()
    {
        return Driver::where('status_id', self::ACTIVE_STATUS_ID)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();
    }

    private function getReportTypes(): array
    {
        return [
            'trips' => 'Trip Reports',
            'maintenance' => 'Maintenance Reports',
            'fuel' => 'Fuel Reports',
            'vehicles' => 'Vehicle Reports',
            'driver_performance' => 'Driver Performance',
            'driver_status' => 'Driver Status History',
            'driver_trips' => 'Driver Trip History',
            'driver_fuel' => 'Driver Fuel Usage',
            'driver_maintenance' => 'Driver Maintenance'
        ];
    }

    private function getHeaders(): array
    {
        return [
            'trips' => ['Trip ID', 'Vehicle', 'Driver', 'Start Time', 'End Time', 'Distance', 'Status'],
            'maintenance' => ['Record ID', 'Vehicle', 'Type', 'Date', 'Cost', 'Description', 'Status'],
            'fuel' => ['Transaction ID', 'Vehicle', 'Date', 'Amount', 'Liters', 'Cost', 'Station'],
            'vehicles' => ['Vehicle ID', 'Registration', 'Make', 'Model', 'Status', 'Department', 'Office'],
            'driver_performance' => ['Driver', 'Total Trips', 'Completed Trips', 'Total Distance', 'Average Fuel Used', 'Performance Score'],
            'driver_status' => ['Driver', 'Status', 'Date', 'Reason', 'Updated By'],
            'driver_trips' => ['Trip ID', 'Vehicle', 'Start Time', 'End Time', 'Distance', 'Status', 'Purpose'],
            'driver_fuel' => ['Transaction ID', 'Vehicle', 'Date', 'Amount', 'Liters', 'Cost', 'Station'],
            'driver_maintenance' => ['Record ID', 'Vehicle', 'Type', 'Date', 'Cost', 'Description', 'Status']
        ];
    }

    public function export(Request $request)
    {
        try {
            Log::info('Export request received', [
                'report_type' => $request->report_type,
                'format' => $request->format,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'driver_id' => $request->driver_id
            ]);

            $request->validate([
                'report_type' => 'required|in:trips,maintenance,fuel,vehicles,driver_performance,driver_status,driver_trips,driver_fuel,driver_maintenance',
                'format' => 'required|in:csv,excel,pdf',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'driver_id' => [
                    'required_if:report_type,driver_performance,driver_status,driver_trips,driver_fuel,driver_maintenance',
                    'nullable',
                    'exists:drivers,id'
                ]
            ], [
                'driver_id.required_if' => 'Please select a driver for this report type.',
                'driver_id.exists' => 'The selected driver is invalid or no longer exists.'
            ]);

            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $reportType = $request->report_type;
            $format = $request->format;
            $driverId = $request->driver_id;

            // Additional validation for driver-specific reports
            if (in_array($reportType, ['driver_performance', 'driver_status', 'driver_trips', 'driver_fuel', 'driver_maintenance'])) {
                $driver = Driver::find($driverId);
                if (!$driver) {
                    Log::error('Driver not found', ['driver_id' => $driverId]);
                    return back()->withErrors(['driver_id' => 'The selected driver is invalid or no longer exists.'])->withInput();
                }
            }

            Log::info('Fetching report data', ['report_type' => $reportType]);
            $data = $this->getReportData($reportType, $startDate, $endDate, $driverId);

            if (empty($data)) {
                Log::warning('No data found for export', ['report_type' => $reportType]);
                return back()->with('error', 'No data found for the selected criteria. Please adjust your filters and try again.');
            }

            Log::info('Data fetched successfully', [
                'report_type' => $reportType,
                'record_count' => count($data)
            ]);

            $headers = $this->getHeaders()[$reportType];

            Log::info('Generating export', [
                'format' => $format,
                'report_type' => $reportType
            ]);

            return $this->generateExport($data, $headers, $reportType, $format);
        } catch (ValidationException $e) {
            Log::error('Validation error in export', [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->validator)->withInput();
        } catch (ExportException $e) {
            Log::error('Export error: ' . $e->getMessage(), [
                'report_type' => $request->report_type,
                'format' => $request->format,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'driver_id' => $request->driver_id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected export error: ' . $e->getMessage(), [
                'report_type' => $request->report_type,
                'format' => $request->format,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'driver_id' => $request->driver_id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An unexpected error occurred while generating the export. Please contact support if the problem persists.');
        }
    }

    private function getReportData($type, $startDate, $endDate, $driverId = null)
    {
        switch ($type) {
            case 'trips':
                return $this->getTripData($startDate, $endDate);
            case 'maintenance':
                return $this->getMaintenanceData($startDate, $endDate);
            case 'fuel':
                return $this->getFuelData($startDate, $endDate);
            case 'vehicles':
                return $this->getVehicleData($startDate, $endDate);
            case 'driver_performance':
                return $this->getDriverPerformanceData($driverId, $startDate, $endDate);
            case 'driver_status':
                return $this->getDriverStatusData($driverId, $startDate, $endDate);
            case 'driver_trips':
                return $this->getDriverTripData($driverId, $startDate, $endDate);
            case 'driver_fuel':
                return $this->getDriverFuelData($driverId, $startDate, $endDate);
            case 'driver_maintenance':
                return $this->getDriverMaintenanceData($driverId, $startDate, $endDate);
            default:
                throw new \InvalidArgumentException('Invalid report type');
        }
    }

    private function getTripData($startDate, $endDate)
    {
        return Trip::with(['vehicle', 'driver', 'status'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get()
            ->map(function ($trip) {
                return [
                    'trip_id' => $trip->id,
                    'vehicle' => $trip->vehicle?->registration_no ?? 'N/A',
                    'driver' => $trip->driver
                        ? trim($trip->driver->first_name . ' ' . $trip->driver->last_name)
                        : 'N/A',
                    'start_time' => $trip->start_time?->format('Y-m-d H:i:s') ?? 'N/A',
                    'end_time' => $trip->end_time?->format('Y-m-d H:i:s') ?? 'N/A',
                    'distance' => $trip->distance ? number_format($trip->distance, 2) . ' km' : 'N/A',
                    'status' => $trip->status?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getMaintenanceData($startDate, $endDate)
    {
        return MaintenanceRecord::with(['vehicle', 'type', 'status'])
            ->whereBetween('service_date', [$startDate, $endDate])
            ->get()
            ->map(function ($record) {
                return [
                    'record_id' => $record->id,
                    'vehicle' => $record->vehicle?->registration_no ?? 'N/A',
                    'type' => $record->type?->name ?? 'N/A',
                    'date' => $record->service_date?->format('Y-m-d') ?? 'N/A',
                    'cost' => $record->cost ? number_format($record->cost, 2) : 'N/A',
                    'description' => $record->description ?? 'N/A',
                    'status' => $record->status?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getFuelData($startDate, $endDate)
    {
        return FuelRecord::with(['vehicle', 'station'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get()
            ->map(function ($record) {
                return [
                    'transaction_id' => $record->id,
                    'vehicle' => $record->vehicle?->registration_no ?? 'N/A',
                    'date' => $record->transaction_date?->format('Y-m-d H:i:s') ?? 'N/A',
                    'amount' => $record->amount ? number_format($record->amount, 2) : 'N/A',
                    'liters' => $record->liters ? number_format($record->liters, 2) : 'N/A',
                    'cost' => $record->cost ? number_format($record->cost, 2) : 'N/A',
                    'station' => $record->station?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getVehicleData($startDate, $endDate)
    {
        return Vehicle::with(['type', 'status', 'department', 'office'])
            ->get()
            ->map(function ($vehicle) {
                return [
                    'vehicle_id' => $vehicle->id,
                    'registration' => $vehicle->registration_no ?? 'N/A',
                    'make' => $vehicle->make ?? 'N/A',
                    'model' => $vehicle->model ?? 'N/A',
                    'status' => $vehicle->status?->name ?? 'N/A',
                    'department' => $vehicle->department?->name ?? 'N/A',
                    'office' => $vehicle->office?->name ?? 'N/A'
                ];
            })->toArray();
    }

    private function getDriverPerformanceData($driverId, $startDate, $endDate)
    {
        $driver = \App\Models\Driver::findOrFail($driverId);

        $trips = \App\Models\Trip::where('driver_id', $driverId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();

        $totalTrips = $trips->count();
        $completedTrips = $trips->where('status_id', 3)->count(); // Assuming 3 is completed status
        $totalDistance = $trips->sum('distance');
        $averageFuelUsed = $trips->avg('fuel_used');

        return [[
            'driver' => $driver->first_name . ' ' . $driver->last_name,
            'total_trips' => $totalTrips,
            'completed_trips' => $completedTrips,
            'total_distance' => number_format($totalDistance, 2) . ' km',
            'average_fuel_used' => number_format($averageFuelUsed, 2) . ' L',
            'performance_score' => $totalTrips > 0 ? number_format(($completedTrips / $totalTrips) * 100, 1) . '%' : 'N/A'
        ]];
    }

    private function getDriverStatusData($driverId, $startDate, $endDate)
    {
        return \App\Models\DriverStatus::with(['driver', 'statusType', 'updatedBy'])
            ->where('driver_id', $driverId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($status) {
                return [
                    'driver' => $status->driver->first_name . ' ' . $status->driver->last_name,
                    'status' => $status->statusType->name,
                    'date' => $status->created_at->format('Y-m-d H:i:s'),
                    'reason' => $status->reason,
                    'updated_by' => $status->updatedBy->name
                ];
            })->toArray();
    }

    private function getDriverTripData($driverId, $startDate, $endDate)
    {
        return \App\Models\Trip::with(['vehicle', 'status', 'purpose'])
            ->where('driver_id', $driverId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get()
            ->map(function ($trip) {
                return [
                    'trip_id' => $trip->id,
                    'vehicle' => $trip->vehicle->registration_no,
                    'start_time' => $trip->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $trip->end_time ? $trip->end_time->format('Y-m-d H:i:s') : 'N/A',
                    'distance' => number_format($trip->distance, 2) . ' km',
                    'status' => $trip->status->name,
                    'purpose' => $trip->purpose->name
                ];
            })->toArray();
    }

    private function getDriverFuelData($driverId, $startDate, $endDate)
    {
        return \App\Models\FuelTransaction::with(['vehicle', 'station'])
            ->where('driver_id', $driverId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get()
            ->map(function ($transaction) {
                return [
                    'transaction_id' => $transaction->id,
                    'vehicle' => $transaction->vehicle->registration_no,
                    'date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                    'amount' => number_format($transaction->amount, 2),
                    'liters' => number_format($transaction->liters, 2),
                    'cost' => number_format($transaction->cost, 2),
                    'station' => $transaction->station->name
                ];
            })->toArray();
    }

    private function getDriverMaintenanceData($driverId, $startDate, $endDate)
    {
        return \App\Models\MaintenanceRecord::with(['vehicle', 'type', 'status'])
            ->where('driver_id', $driverId)
            ->whereBetween('maintenance_date', [$startDate, $endDate])
            ->get()
            ->map(function ($record) {
                return [
                    'record_id' => $record->id,
                    'vehicle' => $record->vehicle->registration_no,
                    'type' => $record->type->name,
                    'date' => $record->maintenance_date->format('Y-m-d'),
                    'cost' => number_format($record->cost, 2),
                    'description' => $record->description,
                    'status' => $record->status->name
                ];
            })->toArray();
    }

    private function generateExport($data, $headers, $reportType, $format)
    {
        try {
            switch ($format) {
                case 'csv':
                    return $this->generateCsv($data, $headers, $reportType);
                case 'excel':
                    return $this->generateExcel($data, $headers, $reportType);
                case 'pdf':
                    return $this->generatePdf($data, $headers, $reportType);
                default:
                    throw new ExportException('The selected export format is not supported. Please choose CSV, Excel, or PDF.');
            }
        } catch (ExportException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Export generation error: ' . $e->getMessage(), [
                'format' => $format,
                'report_type' => $reportType
            ]);
            throw new ExportException('Failed to generate export file due to a system error. Please try again or contact support.');
        }
    }

    private function generateCsv($data, $headers, $reportType)
    {
        try {
            $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
            $handle = fopen('php://temp', 'r+');

            if ($handle === false) {
                throw new ExportException('Could not create a temporary file for CSV export. Please check server permissions.');
            }

            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add headers
            if (fputcsv($handle, $headers) === false) {
                throw new ExportException('Failed to write headers to the CSV file. Please try again.');
            }

            // Add data
            foreach ($data as $row) {
                if (fputcsv($handle, array_values($row)) === false) {
                    throw new ExportException('Failed to write data to the CSV file. Please try again.');
                }
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            if ($csv === false) {
                throw new ExportException('Failed to read CSV data from the temporary file. Please try again.');
            }

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (ExportException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('CSV generation error: ' . $e->getMessage());
            throw new ExportException('Failed to generate the CSV file due to a system error. Please try again or contact support.');
        }
    }

    private function generateExcel($data, $headers, $reportType)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $column = 1;
            foreach ($headers as $header) {
                $sheet->setCellValue([$column, 1], $header);
                $column++;
            }

            // Add data
            $row = 2;
            foreach ($data as $item) {
                $column = 1;
                foreach ($item as $value) {
                    $sheet->setCellValue([$column, $row], $value);
                    $column++;
                }
                $row++;
            }

            // Auto-size columns
            foreach (range(1, count($headers)) as $column) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            }

            // Create the Excel file
            $writer = new Xlsx($spreadsheet);
            $filename = $reportType . '_report_' . now()->format('Y-m-d_His') . '.xlsx';
            $path = storage_path('app/public/' . $filename);

            // Ensure the directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $writer->save($path);

            if (!file_exists($path)) {
                throw new ExportException('Failed to save the Excel file. Please check server permissions.');
            }

            return response()->download($path)->deleteFileAfterSend();
        } catch (ExportException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Excel generation error: ' . $e->getMessage());
            throw new ExportException('Failed to generate the Excel file due to a system error. Please try again or contact support.');
        }
    }

    private function generatePdf($data, $headers, $reportType)
    {
        try {
            $pdf = PDF::loadView('admin.reports.pdf', [
                'data' => $data,
                'headers' => $headers,
                'reportType' => $reportType,
                'generatedAt' => now()->format('Y-m-d H:i:s')
            ]);

            $filename = $reportType . '_report_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            throw new ExportException('Failed to generate the PDF file. Please try again or contact support.');
        }
    }
}
