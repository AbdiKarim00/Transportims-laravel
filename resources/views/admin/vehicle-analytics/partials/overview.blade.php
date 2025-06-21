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
    <!-- Total Vehicles -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Vehicles</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $totalVehicles ?? 0 }}</h2>
                </div>
                <i class="fas fa-car text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Fleet Size</span>
        </div>
    </div>

    <!-- Active Vehicles -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Active Vehicles</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $activeVehicles ?? 0 }}</h2>
                </div>
                <i class="fas fa-check-circle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">In Service</span>
        </div>
    </div>

    <!-- Maintenance Due -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Maintenance Due</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $maintenanceDue ?? 0 }}</h2>
                </div>
                <i class="fas fa-wrench text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Requires Service</span>
        </div>
    </div>

    <!-- Permits Expiring -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Permits Expiring</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $permitsExpiring ?? 0 }}</h2>
                </div>
                <i class="fas fa-file-alt text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Needs Renewal</span>
        </div>
    </div>
</div>

<!-- Analytics Sections -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
    <!-- Drivers' Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Drivers' Licenses</h3>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Total Licenses</div>
                    <div class="font-medium text-foreground">{{ $totalLicenses ?? 0 }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Expiring Soon</div>
                    <div class="font-medium text-foreground">{{ $expiringLicenses ?? 0 }}</div>
                </div>
            </div>
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'permits']) }}" class="mt-4 block w-full text-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
                View Details
            </a>
        </div>
    </div>

    <!-- Odometer & Maintenance -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Odometer & Maintenance</h3>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Avg. Reading</div>
                    <div class="font-medium text-foreground">{{ number_format($averageOdometer ?? 0) }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Due Soon</div>
                    <div class="font-medium text-foreground">{{ $maintenanceDue ?? 0 }}</div>
                </div>
            </div>
            <a href="#odometer" class="tab-link mt-4 block w-full text-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
                View Details
            </a>
        </div>
    </div>

    <!-- Equipment Management -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-tools text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Equipment Management</h3>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Total Equipment</div>
                    <div class="font-medium text-foreground">{{ $totalEquipment ?? 0 }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">In Maintenance</div>
                    <div class="font-medium text-foreground">{{ $equipmentInMaintenance ?? 0 }}</div>
                </div>
            </div>
            <a href="#equipment" class="tab-link mt-4 block w-full text-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
                View Details
            </a>
        </div>
    </div>

    <!-- Service Providers -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-building text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Service Providers</h3>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Total Providers</div>
                    <div class="font-medium text-foreground">{{ $totalProviders ?? 0 }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">Avg. Rating</div>
                    <div class="font-medium text-foreground">{{ number_format($averageRating ?? 0, 1) }}</div>
                </div>
            </div>
            <a href="#service-providers" class="tab-link mt-4 block w-full text-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
                View Details
            </a>
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
            <a href="{{ route('admin.export-reports', ['report_type' => 'recent_activity']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentActivity ?? [] as $activity)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $activity->status_color }}-100 text-{{ $activity->status_color }}-800">
                            {{ $activity->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No recent activity</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any additional JavaScript functionality here
    });
</script>
@endpush