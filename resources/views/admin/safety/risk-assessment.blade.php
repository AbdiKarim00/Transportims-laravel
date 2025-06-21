@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Risk Metrics -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $riskMetrics['high_risk_vehicles'] }}</h3>
                    <p>High Risk Vehicles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $riskMetrics['high_risk_drivers'] }}</h3>
                    <p>High Risk Drivers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $riskMetrics['total_incidents'] }}</h3>
                    <p>Total Incidents</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $riskMetrics['critical_incidents'] }}</h3>
                    <p>Critical Incidents</p>
                </div>
                <div class="icon">
                    <i class="fas fa-radiation"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Trends -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Incidents by Severity</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Severity</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['incidents_by_severity'] as $severity)
                            <tr>
                                <td>
                                    @php
                                    $severityName = \App\Models\IncidentSeverity::find($severity->severity_id)->name;
                                    $badgeClass = match($severityName) {
                                    'Critical' => 'danger',
                                    'High' => 'warning',
                                    'Medium' => 'info',
                                    'Low' => 'success',
                                    default => 'secondary'
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $severityName }}</span>
                                </td>
                                <td>{{ $severity->count }}</td>
                                <td>
                                    @php
                                    $percentage = $riskMetrics['total_incidents'] > 0
                                    ? round(($severity->count / $riskMetrics['total_incidents']) * 100, 1)
                                    : 0;
                                    @endphp
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $badgeClass }}" role="progressbar"
                                            style="width: {{ $percentage }}%"
                                            aria-valuenow="{{ $percentage }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No incident data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Incidents by Type</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['incidents_by_type'] as $type)
                            <tr>
                                <td>{{ \App\Models\IncidentType::find($type->incident_type_id)->name }}</td>
                                <td>{{ $type->count }}</td>
                                <td>
                                    @php
                                    $percentage = $riskMetrics['total_incidents'] > 0
                                    ? round(($type->count / $riskMetrics['total_incidents']) * 100, 1)
                                    : 0;
                                    @endphp
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $percentage }}%"
                                            aria-valuenow="{{ $percentage }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No incident data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection