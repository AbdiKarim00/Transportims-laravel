@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Fuel Reports</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Fuel Reports</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Date Range Filter -->
    <div class="mt-4 flex justify-end">
        <form action="{{ route('admin.fuel-reports') }}" method="GET" class="flex space-x-2">
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <!-- Total Fuel Consumption -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Fuel Consumption</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($fuelMetrics->total_liters, 2) }} L</h2>
                    </div>
                    <i class="fas fa-gas-pump text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total Liters</span>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Cost</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($fuelMetrics->total_cost, 2) }}</h2>
                    </div>
                    <i class="fas fa-money-bill text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total Amount</span>
            </div>
        </div>

        <!-- Average Price -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Average Price</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($fuelMetrics->avg_price_per_liter, 2) }}</h2>
                    </div>
                    <i class="fas fa-tag text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Per Liter</span>
            </div>
        </div>

        <!-- Average Efficiency -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Average Efficiency</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($fuelEfficiency->avg('km_per_liter'), 1) }}</h2>
                    </div>
                    <i class="fas fa-tachometer-alt text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">km/L</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Fuel Consumption Trend -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Fuel Consumption Trend</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="consumptionTrendChart" width="100%" height="300"></canvas>
            </div>
        </div>

        <!-- Consumption by Vehicle Type -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Consumption by Vehicle Type</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="consumptionByTypeChart" width="100%" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Fuel Consumers Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-table text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Top Fuel Consumers</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Liters</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Avg. Price/L</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($topConsumers as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $vehicle->registration_no }} ({{ $vehicle->make }} {{ $vehicle->model }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($vehicle->total_liters, 2) }} L</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($vehicle->total_cost, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($vehicle->avg_price_per_liter, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-muted-foreground">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fuel Efficiency Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-table text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Fuel Efficiency by Vehicle</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Distance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Fuel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Efficiency</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($fuelEfficiency as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $vehicle->registration_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($vehicle->total_distance ?? 0, 1) }} km</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($vehicle->total_fuel, 2) }} L</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($vehicle->km_per_liter, 1) }} km/L</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-muted-foreground">No data available</td>
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
        consumptionTrend: {
            dates: {
                !!json_encode($consumptionTrend - > pluck('date')) !!
            },
            liters: {
                !!json_encode($consumptionTrend - > pluck('total_liters')) !!
            }
        },
        consumptionByType: {
            types: {
                !!json_encode($consumptionByType - > pluck('type')) !!
            },
            liters: {
                !!json_encode($consumptionByType - > pluck('total_liters')) !!
            }
        }
    };
</script>
<script src="{{ asset('js/fuel-reports-charts.js') }}"></script>
@endpush
@endsection