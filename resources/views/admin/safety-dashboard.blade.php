@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Safety Dashboard</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Safety Dashboard</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Time Period Filter -->
    <div class="mt-4 flex justify-end">
        <form action="{{ route('admin.safety-dashboard') }}" method="GET" class="flex space-x-2">
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
        <!-- Total Incidents -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Incidents</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['total_incidents'] ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">All Reported Incidents</span>
            </div>
        </div>

        <!-- Active Investigations -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Active Investigations</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['active_investigations'] ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-search text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Ongoing Cases</span>
            </div>
        </div>

        <!-- Resolved Incidents -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Resolved Incidents</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['resolved_incidents'] ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-check-circle text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Closed Cases</span>
            </div>
        </div>

        <!-- Safety Score -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Safety Score</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $stats['safety_score'] ?? 0 }}%</h2>
                    </div>
                    <i class="fas fa-shield-alt text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Overall Rating</span>
            </div>
        </div>
    </div>

    <!-- Analytics Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Incident Distribution Chart -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Incident Distribution</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="incidentDistributionChart" width="100%" height="300"></canvas>
            </div>
        </div>

        <!-- Severity Analysis Chart -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>
                        <h3 class="text-lg font-medium text-foreground">Severity Analysis</h3>
                    </div>
                    <button class="text-sm text-primary hover:text-primary/80">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            <div class="p-6">
                <canvas id="severityAnalysisChart" width="100%" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Incidents -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Recent Incidents</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($recentIncidents ?? [] as $incident)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $incident->incident_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $incident->type->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $incident->location }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $incident->severity->level === 'High' ? 'bg-red-100 text-red-800' : 
                                   ($incident->severity->level === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-green-100 text-green-800') }}">
                                {{ $incident->severity->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $incident->status->name === 'Resolved' ? 'bg-green-100 text-green-800' : 
                                   ($incident->status->name === 'Under Investigation' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-blue-100 text-blue-800') }}">
                                {{ $incident->status->name }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-muted-foreground">
                            No recent incidents found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Incident Distribution Chart
        const incidentDistributionCtx = document.getElementById('incidentDistributionChart').getContext('2d');
        new Chart(incidentDistributionCtx, {
            type: 'pie',
            data: {
                labels: {
                    !!json_encode($incidentDistribution['labels'] ?? []) !!
                },
                datasets: [{
                    data: {
                        !!json_encode($incidentDistribution['data'] ?? []) !!
                    },
                    backgroundColor: [
                        '#3B82F6', // blue
                        '#10B981', // green
                        '#F59E0B', // yellow
                        '#EF4444', // red
                        '#8B5CF6' // purple
                    ]
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

        // Severity Analysis Chart
        const severityAnalysisCtx = document.getElementById('severityAnalysisChart').getContext('2d');
        new Chart(severityAnalysisCtx, {
            type: 'bar',
            data: {
                labels: {
                    !!json_encode($severityAnalysis['labels'] ?? []) !!
                },
                datasets: [{
                    label: 'Number of Incidents',
                    data: {
                        !!json_encode($severityAnalysis['data'] ?? []) !!
                    },
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endpush