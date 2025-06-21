@extends('layouts.admin')

@section('title', 'Safety Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Date Range Filter</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.safety.dashboard') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                        value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                        value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Incident Reports Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Incident Reports</h3>
            <div class="card-tools">
                <a href="{{ route('admin.safety.incidents.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Incident
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Incident Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $incidentStats['total'] }}</h3>
                            <p>Total Incidents</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $tripSafetyMetrics['incident_related_trips'] }}</h3>
                            <p>Incident-Related Trips</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-route"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $maintenanceSafetyMetrics['pending_maintenance'] }}</h3>
                            <p>Pending Maintenance</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $maintenanceSafetyMetrics['overdue_maintenance'] }}</h3>
                            <p>Overdue Maintenance</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Incidents -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Incidents</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Severity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($incidentStats['recent'] as $incident)
                                        <tr>
                                            <td>{{ $incident->incident_date->format('Y-m-d H:i') }}</td>
                                            <td>{{ $incident->vehicle->registration_number }}</td>
                                            <td>{{ $incident->type->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $incident->severity->name === 'Critical' ? 'danger' : ($incident->severity->name === 'High' ? 'warning' : 'info') }}">
                                                    {{ $incident->severity->name }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No recent incidents</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicles with Safety Concerns -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Vehicles with Safety Concerns</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($vehiclesWithSafetyConcerns as $vehicle)
                                        <tr>
                                            <td>{{ $vehicle->registration_number }}</td>
                                            <td>{{ $vehicle->type->name }}</td>
                                            <td>
                                                <span class="badge bg-warning">Needs Attention</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No vehicles with safety concerns</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Safety Compliance Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Safety Compliance</h3>
        </div>
        <div class="card-body">
            @include('admin.safety.partials.compliance')
        </div>
    </div>

    <!-- Risk Assessment Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Risk Assessment</h3>
        </div>
        <div class="card-body">
            @include('admin.safety.partials.risk-assessment')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize any necessary JavaScript components
    document.addEventListener('DOMContentLoaded', function() {
        // Add any initialization code here
    })
</script>
@endpush