@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Financial & Resource Management</h1>
            <p class="text-sm text-muted-foreground">Monitor costs, fuel usage, and financial aspects of fleet operations</p>
        </div>

        <!-- Date Range Picker Form -->
        <form action="{{ route('admin.financial-management.index', ['tab' => 'overview']) }}" method="GET" class="flex space-x-2">
            <input type="hidden" name="tab" value="overview">
            <div class="flex items-center space-x-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded-lg px-3 py-2">
                <span class="text-gray-500">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded-lg px-3 py-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-border mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.financial-management.index', ['tab' => 'overview']) }}" class="border-primary text-primary whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-chart-pie mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.financial-management.index', ['tab' => 'fuel-reports']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-gas-pump mr-2"></i> Fuel Reports
            </a>
            <a href="{{ route('admin.financial-management.index', ['tab' => 'fuel-cards']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i> Fuel Cards
            </a>
            <a href="{{ route('admin.financial-management.index', ['tab' => 'trip-expenses']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-receipt mr-2"></i> Trip Expenses
            </a>
            <a href="{{ route('admin.financial-management.index', ['tab' => 'insurance']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-file-contract mr-2"></i> Insurance
            </a>
        </nav>
    </div>

    <!-- Overview Content -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Costs -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Total Costs</h6>
                    <h2 class="text-2xl font-bold text-gray-900">KSh {{ number_format($summaryMetrics['total_expenses'], 2) }}</h2>
                </div>
                <i class="fas fa-money-bill text-2xl text-green-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">All operational expenses</span>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Total Revenue</h6>
                    <h2 class="text-2xl font-bold text-gray-900">KSh {{ number_format($summaryMetrics['total_revenue'], 2) }}</h2>
                </div>
                <i class="fas fa-chart-line text-2xl text-blue-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Period revenue</span>
            </div>
        </div>

        <!-- ROI -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">ROI</h6>
                    <h2 class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['roi_percentage'], 2) }}%</h2>
                </div>
                <i class="fas fa-percentage text-2xl text-purple-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Return on investment</span>
            </div>
        </div>

        <!-- Asset Value -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Total Asset Value</h6>
                    <h2 class="text-2xl font-bold text-gray-900">KSh {{ number_format($summaryMetrics['total_asset_value'], 2) }}</h2>
                </div>
                <i class="fas fa-truck text-2xl text-orange-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Current fleet value</span>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-medium text-gray-900">Monthly Cost Trends</h3>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="monthlyTrendsChart" height="300"></canvas>
            </div>
        </div>

        <!-- Cost Distribution -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-medium text-gray-900">Cost Distribution</h3>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="costDistributionChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Expense Vehicles -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-truck text-blue-500 mr-2"></i>
                    <h3 class="text-lg font-medium text-gray-900">Top Expense Vehicles</h3>
                </div>
                <button class="text-sm text-blue-500 hover:text-blue-600">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip Expenses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fuel Costs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maintenance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost/Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($topExpenseVehicles as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $vehicle->registration_no }}</div>
                            <div class="text-sm text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            KSh {{ number_format($vehicle->trip_expenses, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            KSh {{ number_format($vehicle->fuel_costs, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            KSh {{ number_format($vehicle->maintenance_costs, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            KSh {{ number_format($vehicle->total_expenses, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($vehicle->cost_to_value_ratio, 2) }}%
                        </td>
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
        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: {
                    !!json_encode(collect($monthlyTrends) - > pluck('month')) !!
                },
                datasets: [{
                    label: 'Total Costs',
                    data: {
                        !!json_encode(collect($monthlyTrends) - > pluck('total')) !!
                    },
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }, {
                    label: 'Fuel Costs',
                    data: {
                        !!json_encode(collect($monthlyTrends) - > pluck('fuel_costs')) !!
                    },
                    borderColor: 'rgb(16, 185, 129)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Cost Distribution Chart
        const costDistributionCtx = document.getElementById('costDistributionChart').getContext('2d');
        new Chart(costDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Trip Expenses', 'Fuel Costs', 'Maintenance', 'Insurance'],
                datasets: [{
                    data: [{
                            {
                                $summaryMetrics['trip_expenses']
                            }
                        },
                        {
                            {
                                $summaryMetrics['fuel_costs']
                            }
                        },
                        {
                            {
                                $summaryMetrics['maintenance_costs'] ?? 0
                            }
                        },
                        {
                            {
                                $summaryMetrics['insurance_costs']
                            }
                        }
                    ],
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
    });
</script>
@endpush
@endsection