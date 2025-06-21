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
    <!-- Total Equipment -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Equipment</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $totalEquipment ?? 0 }}</h2>
                </div>
                <i class="fas fa-tools text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Fleet Equipment</span>
        </div>
    </div>

    <!-- Active Equipment -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Active Equipment</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $activeEquipment ?? 0 }}</h2>
                </div>
                <i class="fas fa-check-circle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">In Service</span>
        </div>
    </div>

    <!-- In Maintenance -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">In Maintenance</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $maintenanceCount ?? 0 }}</h2>
                </div>
                <i class="fas fa-wrench text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Under Service</span>
        </div>
    </div>

    <!-- Utilization Rate -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Utilization Rate</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $utilizationRate ?? 0 }}%</h2>
                </div>
                <i class="fas fa-chart-line text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Equipment Usage</span>
        </div>
    </div>
</div>

<!-- Equipment Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    <!-- Fuel Usage -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-gas-pump text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Fuel Usage</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'fuel_usage']) }}" class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fuel Used</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fuelUsage ?? [] as $usage)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usage->equipment->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($usage->liters, 2) }} L</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($usage->cost, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($usage->efficiency, 2) }} L/hr</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No fuel usage data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Maintenance Count -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-wrench text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Maintenance Count</h3>
                </div>
                <a href="{{ route('admin.export-reports', ['report_type' => 'maintenance_count']) }}" class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Maintenance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Service</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($maintenanceCounts ?? [] as $count)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $count->equipment->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $count->total_maintenance }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $count->last_service_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $count->status === 'active' ? 'green' : ($count->status === 'maintenance' ? 'yellow' : 'red') }}-100 text-{{ $count->status === 'active' ? 'green' : ($count->status === 'maintenance' ? 'yellow' : 'red') }}-800">
                                {{ ucfirst($count->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No maintenance data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Equipment Assignments -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-tasks text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Equipment Assignments</h3>
            </div>
            <a href="{{ route('admin.export-reports', ['report_type' => 'equipment_assignments']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($equipmentAssignments ?? [] as $assignment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assignment->equipment->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assignment->assigned_to }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assignment->assignment_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $assignment->status === 'active' ? 'green' : 'yellow' }}-100 text-{{ $assignment->status === 'active' ? 'green' : 'yellow' }}-800">
                            {{ ucfirst($assignment->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No equipment assignments available</td>
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