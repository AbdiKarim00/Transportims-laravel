@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Trip Analytics</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Trip Analytics</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Date Range Filter -->
    <div class="mt-4 flex justify-end">
        <form action="{{ route('admin.trip-analytics') }}" method="GET" class="flex space-x-2">
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
        <!-- Total Trips -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Trips</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($tripMetrics->total_trips) }}</h2>
                    </div>
                    <i class="fas fa-route text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Total Journeys</span>
            </div>
        </div>

        <!-- Total Distance -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Distance</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($tripMetrics->total_distance, 2) }} km</h2>
                    </div>
                    <i class="fas fa-road text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Kilometers Traveled</span>
            </div>
        </div>

        <!-- Average Distance -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Average Distance</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($tripMetrics->avg_distance, 2) }} km</h2>
                    </div>
                    <i class="fas fa-chart-line text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Per Trip</span>
            </div>
        </div>

        <!-- Total Duration -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Duration</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ number_format($tripMetrics->total_duration / 60, 1) }} hrs</h2>
                    </div>
                    <i class="fas fa-clock text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Hours Traveled</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Trip Trend -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Trip Trend</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="tripTrendChart" width="100%" height="300"></canvas>
            </div>
        </div>

        <!-- Trips by Vehicle Type -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Trips by Vehicle Type</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="tripsByTypeChart" width="100%" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Trips Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Recent Trips</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Start Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Distance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($recentTrips as $trip)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->vehicle->registration_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->driver->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->start_time->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($trip->distance, 2) }} km</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $trip->status->name === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($trip->status->name === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($trip->status->name) }}
                            </span>
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

    <!-- Top Vehicles by Distance Table -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-car text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Top Vehicles by Distance</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Trips</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Distance</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($topVehiclesByDistance as $vehicle)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $vehicle->registration_no }} ({{ $vehicle->make }} {{ $vehicle->model }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $vehicle->total_trips }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ number_format($vehicle->total_distance, 2) }} km</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No vehicle data available</td>
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
        tripTrend: {
            months: {
                !!json_encode($tripTrend - > pluck('month')) !!
            },
            trips: {
                !!json_encode($tripTrend - > pluck('total_trips')) !!
            },
            distances: {
                !!json_encode($tripTrend - > pluck('total_distance')) !!
            }
        },
        tripsByType: {
            types: {
                !!json_encode($tripsByType - > pluck('type')) !!
            },
            trips: {
                !!json_encode($tripsByType - > pluck('total_trips')) !!
            },
            distances: {
                !!json_encode($tripsByType - > pluck('total_distance')) !!
            }
        }
    };
</script>
<script src="{{ asset('js/trip-analytics-charts.js') }}"></script>
@endpush
@endsection