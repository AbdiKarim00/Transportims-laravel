@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Fuel Cards Management</h1>
            <p class="text-sm text-muted-foreground">Manage and monitor fuel card usage and transactions</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-border mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.financial-management', ['tab' => 'overview']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-chart-pie mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.financial-management', ['tab' => 'fuel-reports']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-gas-pump mr-2"></i> Fuel Reports
            </a>
            <a href="{{ route('admin.financial-management', ['tab' => 'fuel-cards']) }}" class="border-primary text-primary whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i> Fuel Cards
            </a>
            <a href="{{ route('admin.financial-management', ['tab' => 'trip-expenses']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-receipt mr-2"></i> Trip Expenses
            </a>
            <a href="{{ route('admin.financial-management', ['tab' => 'insurance']) }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-file-contract mr-2"></i> Insurance
            </a>
        </nav>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Active Fuel Cards -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Active Fuel Cards</h6>
                    <h2 class="text-2xl font-bold text-gray-900">{{ number_format($summaryMetrics['active_fuel_cards']) }}</h2>
                </div>
                <i class="fas fa-credit-card text-2xl text-blue-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Total active cards</span>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Expiring Soon</h6>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $expiringCards->count() }}</h2>
                </div>
                <i class="fas fa-clock text-2xl text-yellow-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Next 30 days</span>
            </div>
        </div>

        <!-- High Utilization -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">High Utilization</h6>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $highUtilizationCards->count() }}</h2>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">>80% usage</span>
            </div>
        </div>

        <!-- Total Fuel Costs -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-gray-500">Total Fuel Costs</h6>
                    <h2 class="text-2xl font-bold text-gray-900">KSh {{ number_format($summaryMetrics['fuel_costs'], 2) }}</h2>
                </div>
                <i class="fas fa-gas-pump text-2xl text-green-500"></i>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Current period</span>
            </div>
        </div>
    </div>

    <!-- All Fuel Cards -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">All Fuel Cards</h3>
            <form action="{{ route('admin.financial-management', ['tab' => 'fuel-cards']) }}" method="GET" class="flex items-center space-x-2">
                <input type="hidden" name="tab" value="fuel-cards">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search cards..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                @if(request('search'))
                <a href="{{ route('admin.financial-management', ['tab' => 'fuel-cards']) }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Card Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Fuel Limit (Liters)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($fuelCards as $card)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->card_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->provider->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->cardType->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->vehicle->registration_no ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->driver->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($card->monthly_limit, 2) }} L</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->expiry_date ? $card->expiry_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $card->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($card->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border">
            {{ $fuelCards->appends(['tab' => 'fuel-cards'])->links() }}
        </div>
    </div>

    <!-- Expiring Cards -->
    @if($expiringCards->count() > 0)
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Expiring Soon (Next 30 Days)</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Card Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Days Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($expiringCards as $card)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->card_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->provider->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->expiry_date ? $card->expiry_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->expiry_date ? $card->expiry_date->diffInDays(now()) : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <button class="btn-primary text-sm" data-toggle="modal" data-target="#renewCard{{ $card->id }}">
                                Renew
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- High Utilization Cards -->
    @if($highUtilizationCards->count() > 0)
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">High Utilization Cards (>80%)</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Card Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Monthly Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Utilization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($highUtilizationCards as $card)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->card_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $card->provider->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($card->monthly_limit, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($card->total_spent, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="w-full bg-muted rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full" style="width: {{ min(100, $card->utilization_percentage) }}%"></div>
                            </div>
                            <span class="text-xs text-muted-foreground mt-1">{{ number_format($card->utilization_percentage, 1) }}%</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <button class="btn-primary text-sm" data-toggle="modal" data-target="#increaseLimit{{ $card->id }}">
                                Increase Limit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Transaction Trends -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Transaction Trends by Provider</h3>
                </div>
            </div>
        </div>
        <div class="p-6">
            <canvas id="transactionTrendsChart" height="300"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare data for the chart
        const providers = {
            !!json_encode($cardTransactionTrends - > keys()) !!
        };
        const months = {
            !!json_encode(collect($cardTransactionTrends - > first()) - > pluck('month')) !!
        };

        const datasets = providers.map(provider => {
            const data = {
                !!json_encode($cardTransactionTrends - > map(function($transactions) {
                    return $transactions - > sum('total_amount');
                })) !!
            };

            return {
                label: provider,
                data: data,
                borderColor: getRandomColor(),
                tension: 0.1
            };
        });

        // Transaction Trends Chart
        const transactionTrendsCtx = document.getElementById('transactionTrendsChart').getContext('2d');
        new Chart(transactionTrendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
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
    });

    // Helper function to generate random colors
    function getRandomColor() {
        const colors = [
            'rgb(59, 130, 246)', // blue
            'rgb(16, 185, 129)', // green
            'rgb(245, 158, 11)', // yellow
            'rgb(139, 92, 246)', // purple
            'rgb(239, 68, 68)', // red
            'rgb(14, 165, 233)' // sky
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
</script>
@endpush
@endsection