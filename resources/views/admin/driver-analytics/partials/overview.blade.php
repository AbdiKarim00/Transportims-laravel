<!-- Time Period Filter -->
<div class="mt-4 flex justify-end">
    <form action="{{ route('admin.driver-analytics') }}" method="GET" class="flex space-x-2">
        <input type="date" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
            class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}"
            class="px-4 py-2 text-sm border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        <button type="submit" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
            <i class="fas fa-filter fa-sm mr-2"></i> Apply Filter
        </button>
    </form>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
    <!-- Total Drivers -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Drivers</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statistics['total_drivers'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-users text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">All Drivers</span>
        </div>
    </div>

    <!-- Active Drivers -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Active Drivers</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statistics['active_drivers'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-check text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Currently Active</span>
        </div>
    </div>

    <!-- Drivers on Leave -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Drivers on Leave</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statistics['drivers_on_leave'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-clock text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">On Leave</span>
        </div>
    </div>

    <!-- Expiring Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Expiring Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statistics['expiring_licenses'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-id-card text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Due Soon</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    <!-- Status Distribution Chart -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Driver Status Distribution</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="p-6">
            <canvas id="statusDistributionChart" width="100%" height="300"></canvas>
        </div>
    </div>

    <!-- License Status Chart -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">License Status Overview</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="p-6">
            <canvas id="licenseStatusChart" width="100%" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-history text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Recent Activity</h3>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @if(isset($recentActivity) && count($recentActivity) > 0)
                @foreach($recentActivity as $activity)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $activity['driver_name'] ?? 'Unknown Driver' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $activity['description'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ isset($activity['date']) && $activity['date'] instanceof \DateTime ? $activity['date']->format('M d, Y H:i') : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ isset($activity['status']) && $activity['status'] === 'Active' ? 'bg-green-100 text-green-800' : 
                                   (isset($activity['status']) && $activity['status'] === 'On Leave' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $activity['status'] ?? 'Unknown' }}
                        </span>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-muted-foreground">
                        No recent activity found.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Distribution Chart
        const statusDistributionElement = document.getElementById('statusDistributionChart');
        if (statusDistributionElement) {
            const statusCtx = statusDistributionElement.getContext('2d');
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: @if(isset($statusDistribution) && count($statusDistribution) > 0) {
                        !!json_encode($statusDistribution - > pluck('name')) !!
                    }
                    @else['No Data'] @endif,
                    datasets: [{
                        data: @if(isset($statusDistribution) && count($statusDistribution) > 0) {
                            !!json_encode($statusDistribution - > pluck('count')) !!
                        }
                        @else[1] @endif,
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // License Status Chart
        const licenseStatusElement = document.getElementById('licenseStatusChart');
        if (licenseStatusElement) {
            const licenseCtx = licenseStatusElement.getContext('2d');
            new Chart(licenseCtx, {
                type: 'doughnut',
                data: {
                    labels: @if(isset($licenseStatus) && count($licenseStatus) > 0) {
                        !!json_encode($licenseStatus - > pluck('status')) !!
                    }
                    @else['No Data'] @endif,
                    datasets: [{
                        data: @if(isset($licenseStatus) && count($licenseStatus) > 0) {
                            !!json_encode($licenseStatus - > pluck('count')) !!
                        }
                        @else[1] @endif,
                        backgroundColor: [
                            '#1cc88a', // Valid
                            '#f6c23e', // Expiring Soon
                            '#e74a3b' // Expired
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

<div id="no-charts-message" class="text-center p-4 my-4 text-muted-foreground" style="{{ (isset($statusDistribution) && count($statusDistribution) > 0) || (isset($licenseStatus) && count($licenseStatus) > 0) ? 'display: none;' : '' }}">
    No data is available for charts. Charts will display default values until data is available.
</div>