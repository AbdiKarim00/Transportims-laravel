@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Fuel Reports</h1>
            <p class="text-sm text-muted-foreground">Monitor and analyze fuel consumption and costs</p>
        </div>

        <!-- Date Range Picker Form -->
        <form action="{{ route('admin.financial-management.fuel-reports') }}" method="GET" class="flex space-x-2">
            <input type="hidden" name="tab" value="fuel-reports">
            <div class="flex items-center space-x-2">
                <input type="date" name="start_date" value="{{ $summaryMetrics['start_date'] }}" class="border-border rounded-md text-sm px-3 py-1.5">
                <span class="text-muted-foreground">to</span>
                <input type="date" name="end_date" value="{{ $summaryMetrics['end_date'] }}" class="border-border rounded-md text-sm px-3 py-1.5">
                <button type="submit" class="bg-primary text-primary-foreground px-3 py-1.5 rounded-md text-sm">Apply</button>
            </div>
        </form>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-border mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.financial-management.index') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-chart-pie mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.financial-management.fuel-reports') }}" class="border-primary text-primary whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-gas-pump mr-2"></i> Fuel Reports
            </a>
            <a href="{{ route('admin.financial-management.fuel-cards') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i> Fuel Cards
            </a>
            <a href="{{ route('admin.financial-management.trip-expenses') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-receipt mr-2"></i> Trip Expenses
            </a>
            <a href="{{ route('admin.financial-management.insurance') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-file-contract mr-2"></i> Insurance
            </a>
        </nav>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Fuel Cost -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Fuel Cost</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($summaryMetrics['total_cost'], 2) }}</h2>
                    </div>
                    <i class="fas fa-gas-pump text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total fuel expenses</span>
            </div>
        </div>

        <!-- Total Liters -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Liters</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($summaryMetrics['total_liters'], 2) }}</h2>
                    </div>
                    <i class="fas fa-tint text-2xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total fuel volume</span>
            </div>
        </div>

        <!-- Average Price -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Avg Price/Liter</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($summaryMetrics['avg_price_per_liter'], 2) }}</h2>
                    </div>
                    <i class="fas fa-tag text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Average fuel price</span>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Transactions</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($summaryMetrics['total_transactions']) }}</h2>
                    </div>
                    <i class="fas fa-exchange-alt text-2xl text-purple-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Number of fuel purchases</span>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Consumption by Vehicle Type -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Consumption by Vehicle Type</h3>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="consumptionByTypeChart" height="300"></canvas>
            </div>
        </div>

        <!-- Consumption Trend -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Consumption Trend</h3>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="consumptionTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Consumers Table -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-truck text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Top Fuel Consumers</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'top_fuel_consumers']) }}" class="btn-primary text-sm">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Liters</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Avg Price/Liter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($topConsumers as $consumer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $consumer->registration_no }}</div>
                            <div class="text-sm text-muted-foreground">{{ $consumer->make }} {{ $consumer->model }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($consumer->total_liters, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($consumer->total_cost, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($consumer->avg_price_per_liter, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fuel Efficiency Table -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Fuel Efficiency by Vehicle</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'fuel_efficiency']) }}" class="btn-primary text-sm">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Distance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Fuel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Km/Liter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Cost/Km</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($fuelEfficiency as $efficiency)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $efficiency->registration_no }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($efficiency->total_distance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($efficiency->total_fuel, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($efficiency->km_per_liter, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($efficiency->cost_per_km, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Consumption by Vehicle Type Chart
        const consumptionByTypeCtx = document.getElementById('consumptionByTypeChart').getContext('2d');
        new Chart(consumptionByTypeCtx, {
            type: 'doughnut',
            data: {
                labels: {
                    !!json_encode($consumptionByType - > pluck('type')) !!
                },
                datasets: [{
                    data: {
                        !!json_encode($consumptionByType - > pluck('total_liters')) !!
                    },
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Consumption Trend Chart
        const consumptionTrendCtx = document.getElementById('consumptionTrendChart').getContext('2d');
        new Chart(consumptionTrendCtx, {
            type: 'line',
            data: {
                labels: {
                    !!json_encode($consumptionTrend - > pluck('month')) !!
                },
                datasets: [{
                    label: 'Fuel Consumption (Liters)',
                    data: {
                        !!json_encode($consumptionTrend - > pluck('total_liters')) !!
                    },
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection