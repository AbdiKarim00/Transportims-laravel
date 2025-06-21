<!-- Time Period Filter -->
<div class="mt-4 flex justify-end">
    <form action="{{ route('admin.vehicle-analytics') }}" method="GET" class="flex space-x-2">
        <input type="date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
            class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}"
            class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        <button type="submit" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
            <i class="fas fa-filter fa-sm mr-2"></i> Apply Filter
        </button>
    </form>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
    <!-- Average Reading -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Average Reading</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($averageOdometer ?? 0) }}</h2>
                </div>
                <i class="fas fa-tachometer-alt text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Current Average</span>
        </div>
    </div>

    <!-- Highest Reading -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Highest Reading</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($highestOdometer ?? 0) }}</h2>
                </div>
                <i class="fas fa-arrow-up text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Peak Value</span>
        </div>
    </div>

    <!-- Lowest Reading -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Lowest Reading</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($lowestOdometer ?? 0) }}</h2>
                </div>
                <i class="fas fa-arrow-down text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Minimum Value</span>
        </div>
    </div>

    <!-- Maintenance Due -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Maintenance Due</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $maintenanceDueCount ?? 0 }}</h2>
                </div>
                <i class="fas fa-tools text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Requires Service</span>
        </div>
    </div>
</div>

<!-- Odometer Trends -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    <!-- Odometer Trends Chart -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Odometer Trends</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'odometer_trends']) }}" class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="p-4">
            <canvas id="odometerTrendsChart" height="300"></canvas>
        </div>
    </div>

    <!-- Maintenance Schedule -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Maintenance Schedule</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'maintenance_schedule']) }}" class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Reading</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Maintenance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($maintenanceSchedule ?? [] as $schedule)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule->vehicle->registration_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($schedule->current_reading) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($schedule->next_maintenance_at) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $schedule->status === 'due' ? 'bg-red-100 text-red-800' : 
                                   ($schedule->status === 'upcoming' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-green-100 text-green-800') }}">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No maintenance schedule available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Odometer Trends Chart
        const ctx = document.getElementById('odometerTrendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: JSON.parse('{!! json_encode($odometerTrends->pluck("date")) !!}'),
                datasets: [{
                    label: 'Odometer Reading',
                    data: JSON.parse('{!! json_encode($odometerTrends->pluck("reading")) !!}'),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush