@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Export Reports</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Export Reports</span>
                </div>
            </li>
        </ol>
    </nav>

    @if(session('error'))
    <div class="mt-4 bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mt-4 bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded relative" role="alert">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <i class="fas fa-file-export text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Generate Report</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" form="reportForm"
                        class="inline-flex items-center px-4 py-2 text-sm text-primary hover:text-primary/90 transition-colors">
                        <i class="fas fa-download mr-2"></i> Generate Report
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.export-reports.export') }}" method="POST" id="reportForm" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="report_type" class="block text-sm font-medium text-muted-foreground mb-2">Report Type</label>
                        <select id="report_type" name="report_type" required
                            class="w-full px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary @error('report_type') border-destructive @enderror">
                            <option value="">Select Report Type</option>
                            @foreach($reportTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('report_type', $currentReportType) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('report_type')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="format" class="block text-sm font-medium text-muted-foreground mb-2">Export Format</label>
                        <select id="format" name="format" required
                            class="w-full px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary @error('format') border-destructive @enderror">
                            <option value="csv" {{ old('format') === 'csv' ? 'selected' : '' }}>CSV</option>
                            <option value="excel" {{ old('format') === 'excel' ? 'selected' : '' }}>Excel</option>
                            <option value="pdf" {{ old('format') === 'pdf' ? 'selected' : '' }}>PDF</option>
                        </select>
                        @error('format')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-muted-foreground mb-2">Start Date</label>
                        <input type="date" id="start_date" name="start_date" required
                            value="{{ old('start_date', $startDate ? $startDate->format('Y-m-d') : '') }}"
                            placeholder="Select start date"
                            class="w-full px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary @error('start_date') border-destructive @enderror">
                        @error('start_date')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-muted-foreground mb-2">End Date</label>
                        <input type="date" id="end_date" name="end_date" required
                            value="{{ old('end_date', $endDate ? $endDate->format('Y-m-d') : '') }}"
                            placeholder="Select end date"
                            class="w-full px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary @error('end_date') border-destructive @enderror">
                        @error('end_date')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="driverSelectContainer" class="hidden">
                        <label for="driver_id" class="block text-sm font-medium text-muted-foreground mb-2">Select Driver</label>
                        <select id="driver_id" name="driver_id"
                            class="w-full px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary @error('driver_id') border-destructive @enderror">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ trim($driver->first_name . ' ' . $driver->last_name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                        <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(session('error'))
                    <div class="col-span-2 mt-4 bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="col-span-2 mt-4 bg-destructive/10 border border-destructive text-destructive px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="event.preventDefault(); document.getElementById('reportForm').reset();"
                        class="inline-flex items-center px-4 py-2 text-sm bg-muted text-muted-foreground rounded-md hover:bg-muted/80 transition-colors">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sample Data Preview -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-table text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Sample Data Preview</h3>
                </div>
                <div class="flex items-center space-x-2">
                    @if($currentReportType)
                    <span class="text-sm text-muted-foreground">
                        Showing last {{ count($previewData[$currentReportType]) }} records
                    </span>
                    <button type="button" onclick="window.print()" class="px-4 py-2 text-sm bg-muted text-muted-foreground rounded-md hover:bg-muted/80 transition-colors">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    @else
                    <span class="text-sm text-muted-foreground">
                        Select a report type to see preview
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            @if($currentReportType)
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        @foreach($headers[$currentReportType] as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $header }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @foreach($previewData[$currentReportType] as $row)
                    <tr>
                        @foreach($row as $value)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $value }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-6 text-center text-muted-foreground">
                <i class="fas fa-table text-4xl mb-4"></i>
                <p>Please select a report type to see the data preview</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportTypeSelect = document.getElementById('report_type');
        const driverSelectContainer = document.getElementById('driverSelectContainer');
        const driverSelect = document.getElementById('driver_id');
        const resetButton = document.querySelector('button[type="button"]');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        function toggleDriverSelect() {
            const selectedType = reportTypeSelect.value;
            const driverReportTypes = [
                'driver_performance',
                'driver_status',
                'driver_trips',
                'driver_fuel',
                'driver_maintenance'
            ];

            if (driverReportTypes.includes(selectedType)) {
                driverSelectContainer.classList.remove('hidden');
                driverSelect.required = true;
            } else {
                driverSelectContainer.classList.add('hidden');
                driverSelect.required = false;
            }
        }

        function resetForm() {
            // Reset the form
            document.getElementById('reportForm').reset();

            // Clear report type selection
            reportTypeSelect.value = '';

            // Clear date inputs
            startDateInput.value = '';
            endDateInput.value = '';

            // Set default format to CSV
            document.getElementById('format').value = 'csv';

            // Hide driver select if it's visible
            driverSelectContainer.classList.add('hidden');
            driverSelect.required = false;

            // Clear error messages
            const errorMessages = document.querySelectorAll('.bg-destructive\\/10');
            errorMessages.forEach(error => error.remove());

            // Clear validation error classes
            const errorInputs = document.querySelectorAll('.border-destructive');
            errorInputs.forEach(input => input.classList.remove('border-destructive'));

            // Clear validation error messages
            const errorTexts = document.querySelectorAll('.text-destructive');
            errorTexts.forEach(text => text.remove());

            // Update URL to remove report_type parameter
            const url = new URL(window.location.href);
            url.searchParams.delete('report_type');
            window.history.pushState({}, '', url);

            // Reload the page to update the preview data
            window.location.reload();
        }

        reportTypeSelect.addEventListener('change', function() {
            toggleDriverSelect();
            // Update the URL with the selected report type
            const url = new URL(window.location.href);
            url.searchParams.set('report_type', this.value);
            window.history.pushState({}, '', url);
            // Reload the page to update the preview data
            window.location.reload();
        });

        resetButton.addEventListener('click', resetForm);

        // Initial setup
        toggleDriverSelect();
    });
</script>
@endpush
@endsection