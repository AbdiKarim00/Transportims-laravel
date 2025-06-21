<!-- Performance Header -->
<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold text-foreground">Driver Performance</h2>
        <p class="text-sm text-muted-foreground">Track and analyze driver performance metrics</p>
    </div>
    <div class="flex space-x-2">
        <button class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
            <i class="fas fa-plus mr-2"></i> Add Rating
        </button>
        <button class="px-4 py-2 border border-border rounded-md hover:bg-muted">
            <i class="fas fa-filter mr-2"></i> Filter
        </button>
    </div>
</div>

<!-- Performance Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Average Rating -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Average Rating</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($performanceStats['average_rating'] ?? 0, 1) }}</h2>
                </div>
                <i class="fas fa-star text-2xl text-yellow-500"></i>
            </div>
        </div>
    </div>

    <!-- Total Trips -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Trips</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $performanceStats['total_trips'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-route text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Safety Score -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Safety Score</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($performanceStats['safety_score'] ?? 0, 1) }}</h2>
                </div>
                <i class="fas fa-shield-alt text-2xl text-green-500"></i>
            </div>
        </div>
    </div>

    <!-- Fuel Efficiency -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Fuel Efficiency</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ number_format($performanceStats['fuel_efficiency'] ?? 0, 1) }} km/L</h2>
                </div>
                <i class="fas fa-gas-pump text-2xl text-blue-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- Performance Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <!-- Rating Trends -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Rating Trends</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="p-6">
            <canvas id="ratingTrendsChart" width="100%" height="300"></canvas>
        </div>
    </div>

    <!-- Performance Distribution -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Performance Distribution</h3>
                </div>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>
        <div class="p-6">
            <canvas id="performanceDistributionChart" width="100%" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-tachometer-alt text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Performance Metrics</h3>
            </div>
            <div class="flex space-x-2">
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-muted">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Safety Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Fuel Efficiency</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Trips</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @if(isset($performanceMetrics) && count($performanceMetrics) > 0)
                    @foreach($performanceMetrics as $metric)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="{{ is_array($metric) ? ($metric['driver']['avatar_url'] ?? asset('images/default-avatar.png')) : ($metric->driver->avatar_url ?? asset('images/default-avatar.png')) }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-foreground">{{ is_array($metric) ? ($metric['driver']['name'] ?? 'Unknown Driver') : ($metric->driver ? $metric->driver->first_name . ' ' . $metric->driver->last_name : 'Unknown Driver') }}</div>
                                    <div class="text-sm text-muted-foreground">{{ is_array($metric) ? ($metric['driver']['employee_id'] ?? 'N/A') : ($metric->driver->employee_id ?? 'N/A') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="flex items-center">
                                <span class="text-yellow-500 mr-1">{{ number_format(is_array($metric) ? ($metric['rating'] ?? 0) : ($metric->rating ?? 0), 1) }}</span>
                                <i class="fas fa-star text-yellow-500"></i>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-1">{{ number_format(is_array($metric) ? ($metric['safety_score'] ?? 0) : ($metric->safety_score ?? 0), 1) }}</span>
                                <i class="fas fa-shield-alt text-green-500"></i>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="flex items-center">
                                <span class="text-blue-500 mr-1">{{ number_format(is_array($metric) ? ($metric['fuel_efficiency'] ?? 0) : ($metric->fuel_efficiency ?? 0), 1) }}</span>
                                <i class="fas fa-gas-pump text-blue-500"></i>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ is_array($metric) ? ($metric['total_trips'] ?? 0) : ($metric->total_trips ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <div class="flex space-x-2">
                                <button class="text-primary hover:text-primary/80">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button class="text-primary hover:text-primary/80">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-muted-foreground">
                            No performance metrics found.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-border">
        @if(isset($performanceMetrics) && method_exists($performanceMetrics, 'links'))
            {{ $performanceMetrics->links() }}
        @endif
    </div>
</div>

<!-- Recent Ratings -->
<div class="bg-card text-card-foreground rounded-lg shadow-md">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-star text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Recent Ratings</h3>
            </div>
            <button class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            @if(isset($recentRatings) && count($recentRatings) > 0)
                @foreach($recentRatings as $rating)
                <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <img class="h-12 w-12 rounded-full" src="{{ is_array($rating) ? ($rating['driver']['avatar_url'] ?? asset('images/default-avatar.png')) : ($rating->driver->avatar_url ?? asset('images/default-avatar.png')) }}" alt="">
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-foreground">{{ is_array($rating) ? ($rating['driver']['name'] ?? 'Unknown Driver') : ($rating->driver ? $rating->driver->first_name . ' ' . $rating->driver->last_name : 'Unknown Driver') }}</h4>
                            <p class="text-sm text-muted-foreground">{{ is_array($rating) ? ($rating['trip']['description'] ?? 'N/A') : ($rating->trip->description ?? 'N/A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="flex items-center justify-end">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= (is_array($rating) ? ($rating['rating'] ?? 0) : ($rating->rating ?? 0)) ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">{{ 
                                is_array($rating) 
                                    ? (isset($rating['trip']['date']) && $rating['trip']['date'] instanceof \DateTime 
                                        ? $rating['trip']['date']->format('M d, Y H:i') 
                                        : 'N/A') 
                                    : ($rating->created_at ? $rating->created_at->format('M d, Y H:i') : 'N/A') 
                            }}</p>
                        </div>
                        <button class="text-primary hover:text-primary/80">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center p-4 text-muted-foreground">
                    No recent ratings available.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- No data message -->
<div id="no-performance-data-message" class="text-center p-4 my-4 text-muted-foreground" style="{{ (isset($performanceMetrics) && count($performanceMetrics) > 0) || (isset($recentRatings) && count($recentRatings) > 0) ? 'display: none;' : '' }}">
    No performance data is available. Data will appear when drivers complete trips and receive ratings.
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rating Trends Chart
        const ratingTrendsElement = document.getElementById('ratingTrendsChart');
        if (ratingTrendsElement) {
            const ratingCtx = ratingTrendsElement.getContext('2d');
            new Chart(ratingCtx, {
                type: 'line',
                data: {
                    labels: @if(isset($ratingTrends) && count($ratingTrends) > 0) {!! json_encode($ratingTrends->pluck('date')) !!} @else ['No Data'] @endif,
                    datasets: [{
                        label: 'Average Rating',
                        data: @if(isset($ratingTrends) && count($ratingTrends) > 0) {!! json_encode($ratingTrends->pluck('rating')) !!} @else [0] @endif,
                        borderColor: '#4e73df',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Performance Distribution Chart
        const performanceElement = document.getElementById('performanceDistributionChart');
        if (performanceElement) {
            const performanceCtx = performanceElement.getContext('2d');
            new Chart(performanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Below Average', 'Poor'],
                    datasets: [{
                        data: @if(isset($performanceDistribution) && !empty($performanceDistribution)) {!! json_encode($performanceDistribution) !!} @else [0, 0, 0, 0, 0] @endif,
                        backgroundColor: [
                            '#1cc88a', // Excellent
                            '#4e73df', // Good
                            '#f6c23e', // Average
                            '#e74a3b', // Below Average
                            '#858796' // Poor
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