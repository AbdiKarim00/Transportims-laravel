@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-foreground">Export Reports</h1>
        <p class="text-muted-foreground mt-2">Generate comprehensive reports for your transport management system</p>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-card rounded-lg p-6 border border-border">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-route text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">Total Trips</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($summaryStats['total_trips']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-lg p-6 border border-border">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-tools text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">Maintenance Records</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($summaryStats['total_maintenance']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-lg p-6 border border-border">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-gas-pump text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">Fuel Transactions</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($summaryStats['total_fuel_transactions']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-lg p-6 border border-border">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-muted-foreground">Incidents</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($summaryStats['total_incidents']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Configuration -->
    <div class="bg-card rounded-lg border border-border mb-8">
        <div class="p-6 border-b border-border">
            <h2 class="text-xl font-semibold text-foreground">Report Configuration</h2>
            <p class="text-muted-foreground mt-1">Select report type, date range, and export format</p>
        </div>

        <form action="{{ route('admin.export-reports.export') }}" method="POST" id="exportForm" class="p-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Report Type Selection -->
                <div>
                    <label for="report_type" class="block text-sm font-medium text-foreground mb-3">Report Type</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($reportTypes as $key => $report)
                        <div class="relative">
                            <input type="radio" id="report_{{ $key }}" name="report_type" value="{{ $key }}"
                                class="peer hidden" {{ $currentReportType === $key ? 'checked' : '' }}>
                            <label for="report_{{ $key }}"
                                class="block p-4 border border-border rounded-lg cursor-pointer transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                        <i class="{{ $report['icon'] }} text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-foreground">{{ $report['name'] }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ $report['description'] }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('report_type')
                    <p class="mt-2 text-sm text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Range and Format -->
                <div class="space-y-6">
                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-foreground mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date"
                                value="{{ $startDate->format('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            @error('start_date')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-foreground mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date"
                                value="{{ $endDate->format('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            @error('end_date')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Export Format -->
                    <div>
                        <label for="format" class="block text-sm font-medium text-foreground mb-2">Export Format</label>
                        <select id="format" name="format"
                            class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        @error('format')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Driver Selection (for driver-specific reports) -->
                    <div id="driverSelection" class="hidden">
                        <label for="driver_id" class="block text-sm font-medium text-foreground mb-2">Select Driver (Optional)</label>
                        <select id="driver_id" name="driver_id"
                            class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">All Drivers</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ trim($driver->first_name . ' ' . $driver->last_name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Generate Button -->
                    <button type="submit"
                        class="w-full bg-primary text-primary-foreground px-4 py-3 rounded-md hover:bg-primary/90 transition-colors font-medium">
                        <i class="fas fa-download mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Preview -->
    @if($currentReportType && !empty($previewData))
    <div class="bg-card rounded-lg border border-border">
        <div class="p-6 border-b border-border">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-foreground">Data Preview</h2>
                    <p class="text-muted-foreground mt-1">Showing first 5 records of {{ $reportTypes[$currentReportType]['name'] }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-muted-foreground">
                        {{ count($previewData) }} records
                    </span>
                    <button type="button" onclick="window.print()"
                        class="px-3 py-1 text-sm bg-muted text-muted-foreground rounded-md hover:bg-muted/80 transition-colors">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $header }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($previewData as $row)
                    <tr class="hover:bg-muted/30">
                        @foreach($row as $cell)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $cell }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($currentReportType)
    <div class="bg-card rounded-lg border border-border p-6">
        <div class="text-center">
            <i class="fas fa-inbox text-4xl text-muted-foreground mb-4"></i>
            <h3 class="text-lg font-medium text-foreground mb-2">No Data Available</h3>
            <p class="text-muted-foreground">No records found for the selected criteria. Try adjusting the date range.</p>
        </div>
    </div>
    @else
    <div class="bg-card rounded-lg border border-border p-6">
        <div class="text-center">
            <i class="fas fa-chart-bar text-4xl text-muted-foreground mb-4"></i>
            <h3 class="text-lg font-medium text-foreground mb-2">Select a Report Type</h3>
            <p class="text-muted-foreground">Choose a report type above to see a preview of the data.</p>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for dynamic behavior -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportTypeInputs = document.querySelectorAll('input[name="report_type"]');
        const driverSelection = document.getElementById('driverSelection');
        const form = document.getElementById('exportForm');

        // Show/hide driver selection based on report type
        function toggleDriverSelection() {
            const selectedReport = document.querySelector('input[name="report_type"]:checked');
            if (selectedReport && ['trips', 'fuel'].includes(selectedReport.value)) {
                driverSelection.classList.remove('hidden');
            } else {
                driverSelection.classList.add('hidden');
            }
        }

        // Update preview when report type changes
        function updatePreview() {
            const selectedReport = document.querySelector('input[name="report_type"]:checked');
            if (selectedReport) {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                // Redirect to update the page with new parameters
                const url = new URL(window.location);
                url.searchParams.set('report_type', selectedReport.value);
                if (startDate) url.searchParams.set('start_date', startDate);
                if (endDate) url.searchParams.set('end_date', endDate);

                window.location.href = url.toString();
            }
        }

        // Event listeners
        reportTypeInputs.forEach(input => {
            input.addEventListener('change', function() {
                toggleDriverSelection();
                updatePreview();
            });
        });

        // Initial setup
        toggleDriverSelection();

        // Form validation
        form.addEventListener('submit', function(e) {
            const selectedReport = document.querySelector('input[name="report_type"]:checked');
            if (!selectedReport) {
                e.preventDefault();
                alert('Please select a report type.');
                return;
            }
        });
    });
</script>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
</div>
@endif

@if(session('warning'))
<div class="fixed top-4 right-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('warning') }}
    </div>
</div>
@endif
@endsection