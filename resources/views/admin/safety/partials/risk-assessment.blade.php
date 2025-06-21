<!-- Risk Metrics -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $riskMetrics['high_risk_vehicles'] }}</h3>
                <p>High Risk Vehicles</p>
            </div>
            <div class="icon">
                <i class="fas fa-car-crash"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $riskMetrics['high_risk_drivers'] }}</h3>
                <p>High Risk Drivers</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
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
    <div class="col-lg-3 col-6">
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

<!-- Risk Analysis -->
<div class="row">
    <!-- Incident Severity Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Incident Severity Distribution</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Severity</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['severity_distribution'] as $severity)
                            <tr>
                                <td>{{ $severity->name }}</td>
                                <td>{{ $severity->count }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $severity->name === 'Critical' ? 'danger' : ($severity->name === 'High' ? 'warning' : 'info') }}"
                                            role="progressbar" style="width: {{ $severity->percentage }}%"
                                            aria-valuenow="{{ $severity->percentage }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ number_format($severity->percentage, 1) }}%
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

    <!-- Incident Type Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Incident Type Distribution</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['type_distribution'] as $type)
                            <tr>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->count }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $type->percentage }}%" aria-valuenow="{{ $type->percentage }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            {{ number_format($type->percentage, 1) }}%
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

<!-- High Risk Vehicles and Drivers -->
<div class="row">
    <!-- High Risk Vehicles -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">High Risk Vehicles</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Critical Incidents</th>
                                <th>Last Incident</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['high_risk_vehicles'] as $vehicle)
                            <tr>
                                <td>{{ $vehicle->registration_number }}</td>
                                <td>{{ $vehicle->critical_incidents_count }}</td>
                                <td>{{ $vehicle->last_incident_date->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No high risk vehicles found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- High Risk Drivers -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">High Risk Drivers</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Critical Incidents</th>
                                <th>Last Incident</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riskTrends['high_risk_drivers'] as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->critical_incidents_count }}</td>
                                <td>{{ $driver->last_incident_date->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No high risk drivers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>