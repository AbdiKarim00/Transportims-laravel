@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Insurance Management</h1>
            <p class="text-sm text-muted-foreground">Manage vehicle insurance policies and track coverage</p>
        </div>

        <!-- Date Range Picker Form -->
        <form action="{{ route('admin.financial-management.insurance') }}" method="GET" class="flex space-x-2">
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
            <a href="{{ route('admin.financial-management.fuel-reports') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-gas-pump mr-2"></i> Fuel Reports
            </a>
            <a href="{{ route('admin.financial-management.fuel-cards') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i> Fuel Cards
            </a>
            <a href="{{ route('admin.financial-management.trip-expenses') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-receipt mr-2"></i> Trip Expenses
            </a>
            <a href="{{ route('admin.financial-management.insurance') }}" class="border-primary text-primary whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-file-contract mr-2"></i> Insurance
            </a>
        </nav>
    </div>

    <!-- Insurance Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Active Policies -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Active Policies</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $summaryMetrics['active_policies'] }}</h2>
                    </div>
                    <i class="fas fa-shield-alt text-2xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Currently active insurance policies</span>
            </div>
        </div>

        <!-- Insurance Costs -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Insurance Costs</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($summaryMetrics['insurance_costs'], 2) }}</h2>
                    </div>
                    <i class="fas fa-money-bill text-2xl text-green-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total insurance costs for period</span>
            </div>
        </div>

        <!-- Expiring Policies -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Expiring Soon</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $expiringPolicies->count() }}</h2>
                    </div>
                    <i class="fas fa-clock text-2xl text-yellow-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Policies expiring in 30 days</span>
            </div>
        </div>

        <!-- Uninsured Vehicles -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Uninsured Vehicles</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $uninsuredVehicles->count() }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Vehicles without active insurance</span>
            </div>
        </div>
    </div>

    <!-- Insurance Policies Table -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-file-contract text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Insurance Policies</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Policy Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Premium</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @foreach($insurancePolicies as $policy)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $policy->vehicle->registration_no }}</div>
                            <div class="text-sm text-muted-foreground">{{ $policy->vehicle->make }} {{ $policy->vehicle->model }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->insurance_company }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->policy_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->start_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->end_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            KSh {{ number_format($policy->premium_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $policy->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($policy->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-border">
                {{ $insurancePolicies->links() }}
            </div>
        </div>
    </div>

    <!-- Insurance Costs by Provider -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Insurance Costs by Provider</h3>
                </div>
            </div>
        </div>
        <div class="p-6">
            <canvas id="insuranceCostsChart" height="300"></canvas>
        </div>
    </div>

    <!-- Expiring Policies -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Policies Expiring Soon</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Days Left</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @foreach($expiringPolicies as $policy)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $policy->vehicle->registration_no }}</div>
                            <div class="text-sm text-muted-foreground">{{ $policy->vehicle->make }} {{ $policy->vehicle->model }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->insurance_company }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->end_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $policy->end_date->diffInDays(now()) }} days
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-sync-alt mr-1"></i> Renew
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Uninsured Vehicles -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Uninsured Vehicles</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <form action="{{ route('admin.financial-management.insurance') }}" method="GET" class="flex items-center">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vehicles..."
                            class="border-border rounded-md text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary">
                        <button type="submit" class="bg-primary text-primary-foreground px-3 py-1.5 rounded-md text-sm ml-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($uninsuredVehicles->take(5) as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $vehicle->registration_no }}</div>
                            <div class="text-sm text-muted-foreground">{{ $vehicle->type ? $vehicle->type->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $vehicle->status }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Uninsured
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <a href="{{ route('admin.vehicle-analytics') }}" class="text-primary hover:text-primary/80">
                                <i class="fas fa-chart-line mr-1"></i> View Analytics
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground text-center">
                            No uninsured vehicles found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($uninsuredVehicles->count() > 5)
            <div class="px-6 py-4 border-t border-border text-center">
                <p class="text-sm text-muted-foreground">
                    Showing 5 of {{ $uninsuredVehicles->count() }} uninsured vehicles
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Insurance Costs by Provider Chart
        const insuranceCostsCtx = document.getElementById('insuranceCostsChart').getContext('2d');
        new Chart(insuranceCostsCtx, {
            type: 'doughnut',
            data: {
                labels: {
                    !!json_encode($insuranceCostsByProvider - > pluck('insurance_company')) !!
                },
                datasets: [{
                    data: {
                        !!json_encode($insuranceCostsByProvider - > pluck('total_premium')) !!
                    },
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)',
                        'rgb(239, 68, 68)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'KSh ' + context.raw.toLocaleString();
                                return label;
                            }
                        }
                    }
                },
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
</script>
@endpush
@endsection