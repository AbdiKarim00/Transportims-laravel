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
    <!-- Total Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $totalLicenses ?? 0 }}</h2>
                </div>
                <i class="fas fa-id-card text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">All Drivers</span>
        </div>
    </div>

    <!-- Valid Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Valid Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $validLicenses ?? 0 }}</h2>
                </div>
                <i class="fas fa-check-circle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Currently Valid</span>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Expiring Soon</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $expiringLicenses ?? 0 }}</h2>
                </div>
                <i class="fas fa-clock text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Within 30 Days</span>
        </div>
    </div>

    <!-- Expired Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Expired Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $expiredLicenses ?? 0 }}</h2>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Requires Renewal</span>
        </div>
    </div>
</div>

<!-- License Status Chart -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-chart-pie text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">License Status Distribution</h3>
            </div>
            <a href="{{ route('admin.export-reports', ['report_type' => 'license_status_distribution']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="p-4">
        <canvas id="licenseStatusChart" height="300"></canvas>
    </div>
</div>

<!-- Expiring Licenses Table -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-list text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Expiring Licenses</h3>
            </div>
            <a href="{{ route('admin.export-reports', ['report_type' => 'expiring_licenses']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Remaining</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expiringLicenses ?? [] as $license)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $license->driver_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $license->license_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $license->expiry_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $license->days_remaining }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $license->days_remaining <= 7 ? 'bg-red-100 text-red-800' : 
                               ($license->days_remaining <= 30 ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-green-100 text-green-800') }}">
                            {{ $license->days_remaining <= 7 ? 'Critical' : 
                               ($license->days_remaining <= 30 ? 'Expiring Soon' : 'Valid') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No expiring licenses found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize License Status Chart
        const ctx = document.getElementById('licenseStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Valid', 'Expiring Soon', 'Expired'],
                datasets: [{
                    data: [{
                        {
                            $validLicenses ?? 0
                        }
                    }, {
                        {
                            $expiringLicenses ?? 0
                        }
                    }, {
                        {
                            $expiredLicenses ?? 0
                        }
                    }],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)', // Green for valid
                        'rgba(234, 179, 8, 0.8)', // Yellow for expiring soon
                        'rgba(239, 68, 68, 0.8)' // Red for expired
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
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