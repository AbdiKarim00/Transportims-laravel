@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Maintenance Reports</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Maintenance Reports</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Date Range Filter -->
    <div class="mt-4 flex justify-end">
        <form action="{{ route('admin.maintenance-reports') }}" method="GET" class="flex space-x-2">
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            <button type="submit" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <!-- Total Maintenance -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Maintenance</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($maintenanceMetrics->total_maintenance) }}</h2>
                    </div>
                    <i class="fas fa-tools text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total Services</span>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Cost</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($maintenanceMetrics->total_cost, 2) }}</h2>
                    </div>
                    <i class="fas fa-money-bill text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total Amount</span>
            </div>
        </div>

        <!-- Average Cost -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Average Cost</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($maintenanceMetrics->avg_cost, 2) }}</h2>
                    </div>
                    <i class="fas fa-chart-line text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Per Service</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Maintenance Trend -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Maintenance Trend</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="maintenanceTrendChart" width="100%" height="300"></canvas>
            </div>
        </div>

        <!-- Maintenance by Vehicle Type -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Maintenance by Vehicle Type</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="maintenanceByTypeChart" width="100%" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Maintenance Alerts Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Maintenance Alerts</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Alert Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Severity</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($maintenanceAlerts as $alert)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $alert->vehicle->registration_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $alert->alert_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $alert->due_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $alert->severity === 'high' ? 'bg-red-100 text-red-800' : 
                                   ($alert->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($alert->severity) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-muted-foreground">No active maintenance alerts</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Maintenance Vehicles Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-car text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Top Maintenance Vehicles</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Services</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Cost</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($topMaintenanceVehicles as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $vehicle->registration_no }} ({{ $vehicle->make }} {{ $vehicle->model }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $vehicle->total_maintenance }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($vehicle->total_cost, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No maintenance data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass PHP data to JavaScript
    window.chartData = {
        maintenanceTrend: {
            months: {
                !!json_encode($maintenanceTrend - > pluck('month')) !!
            },
            counts: {
                !!json_encode($maintenanceTrend - > pluck('total_maintenance')) !!
            },
            costs: {
                !!json_encode($maintenanceTrend - > pluck('total_cost')) !!
            }
        },
        maintenanceByType: {
            types: {
                !!json_encode($maintenanceByType - > pluck('type')) !!
            },
            costs: {
                !!json_encode($maintenanceByType - > pluck('total_cost')) !!
            }
        }
    };
</script>
<script src="{{ asset('js/maintenance-reports-charts.js') }}"></script>
@endpush
@endsection