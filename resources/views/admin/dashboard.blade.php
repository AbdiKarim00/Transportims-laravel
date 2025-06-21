@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Management Dashboard</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="#" class="text-muted-foreground hover:text-primary">Executive Overview</a>
            </li>
        </ol>
    </nav>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <!-- Total Vehicles Card -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Vehicles</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['total_vehicles'] }}</h2>
                    </div>
                    <i class="fas fa-truck text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Fleet Oversight</span>
            </div>
        </div>

        <!-- Active Drivers Card -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Active Drivers</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['active_drivers'] }}</h2>
                    </div>
                    <i class="fas fa-user-tie text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Personnel Management</span>
            </div>
        </div>

        <!-- Pending Maintenance Card -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Pending Maintenance</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['pending_maintenance'] }}</h2>
                    </div>
                    <i class="fas fa-tools text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Maintenance Oversight</span>
            </div>
        </div>

        <!-- Active Trips Card -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Active Trips</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['active_trips'] }}</h2>
                    </div>
                    <i class="fas fa-route text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Operations Monitoring</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Vehicle Utilization Chart -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Fleet Utilization Metrics</h3>
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm bg-muted rounded-md hover:bg-muted/80">Daily</button>
                        <button class="px-3 py-1 text-sm bg-primary text-white rounded-md">Monthly</button>
                        <button class="px-3 py-1 text-sm bg-muted rounded-md hover:bg-muted/80">Yearly</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="vehicleUtilizationChart" width="100%" height="40"></canvas>
            </div>
        </div>

        <!-- Fuel Consumption Chart -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-gas-pump text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Expense Analysis: Fuel</h3>
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm bg-muted rounded-md hover:bg-muted/80">Daily</button>
                        <button class="px-3 py-1 text-sm bg-primary text-white rounded-md">Monthly</button>
                        <button class="px-3 py-1 text-sm bg-muted rounded-md hover:bg-muted/80">Yearly</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="fuelConsumptionChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <!-- Recent Trips -->
        <div class="lg:col-span-2 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-table text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Active Operations Monitor</h3>
                    </div>
                    <a href="{{ route('admin.export-reports') }}" class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export Report
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($recent_trips as $trip)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->vehicle->plate_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->driver->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->end_location ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $trip->status->name === 'active' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground' }}">
                                    {{ $trip->status->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                <button class="text-primary hover:text-primary/80">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-muted-foreground">No recent trips found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">High-Priority Maintenance</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export Report
                    </button>
                </div>
            </div>
            <div class="p-4">
                <div class="space-y-4">
                    @forelse($maintenance_alerts as $alert)
                    <div class="block p-4 bg-muted/50 border border-border rounded-lg">
                        <div class="flex justify-between items-start">
                            <h6 class="text-sm font-medium text-foreground">{{ $alert->vehicle->plate_number ?? 'Unknown Vehicle' }}</h6>
                            <small class="text-muted-foreground">{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">{{ Str::limit($alert->description, 100) }}</p>
                        <div class="mt-2 flex items-center text-sm text-destructive">
                            <i class="fas fa-clock mr-1"></i>
                            <span>Due: {{ $alert->due_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6">
                        <i class="fas fa-check-circle text-primary text-3xl mb-2"></i>
                        <p class="text-muted-foreground">No pending maintenance alerts</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Vehicle Utilization Chart - Management KPIs
    const vehicleUtilizationCtx = document.getElementById('vehicleUtilizationChart').getContext('2d');
    new Chart(vehicleUtilizationCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Fleet Efficiency Rate',
                data: [65, 59, 80, 81, 56, 55],
                fill: false,
                borderColor: '#C5A830',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#1f2937'
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        color: '#6b7280'
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                },
                x: {
                    ticks: {
                        color: '#6b7280'
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                }
            }
        }
    });

    // Fuel Expense Analysis Chart - Executive View
    const fuelConsumptionCtx = document.getElementById('fuelConsumptionChart').getContext('2d');
    new Chart(fuelConsumptionCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Fuel Expenses (Liters)',
                data: [1200, 1900, 1500, 2100, 1800, 2300],
                backgroundColor: '#C5A830',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#1f2937'
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        color: '#6b7280'
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                },
                x: {
                    ticks: {
                        color: '#6b7280'
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection