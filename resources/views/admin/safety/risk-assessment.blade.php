@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Risk Assessment</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <a href="{{ route('admin.safety-dashboard') }}" class="text-muted-foreground hover:text-primary">Safety Dashboard</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Risk Assessment</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Risk Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">High Risk Vehicles</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $riskMetrics['high_risk_vehicles'] }}</h2>
                </div>
                <i class="fas fa-truck text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">High Risk Drivers</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $riskMetrics['high_risk_drivers'] }}</h2>
                </div>
                <i class="fas fa-user text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Incidents</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $riskMetrics['total_incidents'] }}</h2>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Critical Incidents</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $riskMetrics['critical_incidents'] }}</h2>
                </div>
                <i class="fas fa-radiation text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Risk Trends -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Incidents by Severity</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($riskTrends['incidents_by_severity'] as $severity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                @php
                                $severityName = \App\Models\IncidentSeverity::find($severity->incident_severity_id)->name;
                                $badgeClass = match($severityName) {
                                'Critical' => 'bg-red-100 text-red-800',
                                'High' => 'bg-yellow-100 text-yellow-800',
                                'Medium' => 'bg-blue-100 text-blue-800',
                                'Low' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800'
                                };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">{{ $severityName }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $severity->count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                $percentage = $riskMetrics['total_incidents'] > 0
                                ? round(($severity->count / $riskMetrics['total_incidents']) * 100, 1)
                                : 0;
                                @endphp
                                <div class="w-full bg-muted rounded-full h-4">
                                    <div class="h-4 rounded-full {{ $badgeClass }} flex items-center justify-center" style="width: {{$percentage}}%">
                                        <span class="text-xs font-semibold">{{ $percentage }}%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No incident data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list-alt text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Incidents by Type</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($riskTrends['incidents_by_type'] as $type)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ \App\Models\IncidentType::find($type->incident_type_id)->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $type->count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                $percentage = $riskMetrics['total_incidents'] > 0
                                ? round(($type->count / $riskMetrics['total_incidents']) * 100, 1)
                                : 0;
                                @endphp
                                <div class="w-full bg-muted rounded-full h-4">
                                    <div class="h-4 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center" style="width: {{$percentage}}%">
                                        <span class="text-xs font-semibold">{{ $percentage }}%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No incident data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection