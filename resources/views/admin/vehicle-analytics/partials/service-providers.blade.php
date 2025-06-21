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
    <!-- Total Providers -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Providers</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $totalProviders ?? 0 }}</h2>
                </div>
                <i class="fas fa-building text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Service Partners</span>
        </div>
    </div>

    <!-- Active Providers -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Active Providers</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $activeProviders ?? 0 }}</h2>
                </div>
                <i class="fas fa-check-circle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Currently Active</span>
        </div>
    </div>

    <!-- Average Rating -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Average Rating</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($averageRating ?? 0, 1) }}</h2>
                </div>
                <i class="fas fa-star text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Overall Rating</span>
        </div>
    </div>

    <!-- Total Cost -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Cost</h6>
                    <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($totalCost ?? 0, 2) }}</h2>
                </div>
                <i class="fas fa-dollar-sign text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-muted px-4 py-2">
            <span class="text-sm text-muted-foreground">Total Expenses</span>
        </div>
    </div>
</div>

<!-- Provider Performance -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-chart-bar text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Provider Performance</h3>
            </div>
            <a href="{{ route('admin.export-reports', ['report_type' => 'provider_performance']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Services</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($providerPerformance ?? [] as $provider)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $provider->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $provider->total_services }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        KSh {{ number_format($provider->total_cost, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $provider->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                @endfor
                                <span class="ml-2 text-sm text-gray-500">({{ number_format($provider->rating, 1) }})</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $provider->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No provider performance data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Service History -->
<div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-history text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Service History</h3>
            </div>
            <a href="{{ route('admin.export-reports', ['report_type' => 'service_history']) }}" class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($serviceHistory ?? [] as $service)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->provider->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->vehicle->registration_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->service_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->service_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        KSh {{ number_format($service->cost, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $service->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($service->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($service->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No service history available</td>
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