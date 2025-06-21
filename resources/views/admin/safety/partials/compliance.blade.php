<!-- Compliance Statistics -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $complianceStats['vehicles_with_valid_insurance'] }}</h3>
                <p>Vehicles with Valid Insurance</p>
            </div>
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $complianceStats['vehicles_with_valid_inspection'] }}</h3>
                <p>Vehicles with Valid Inspection</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $complianceStats['drivers_with_valid_licenses'] }}</h3>
                <p>Drivers with Valid Licenses</p>
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $complianceStats['total_vehicles'] - $complianceStats['vehicles_with_valid_insurance'] }}</h3>
                <p>Vehicles Needing Insurance</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Compliance Deadlines -->
<div class="row">
    <!-- Insurance Expirations -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upcoming Insurance Expirations</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingDeadlines['insurance'] as $insurance)
                            <tr>
                                <td>{{ $insurance->vehicle->registration_number }}</td>
                                <td>{{ $insurance->end_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $insurance->days_until_expiry <= 30 ? 'danger' : ($insurance->days_until_expiry <= 60 ? 'warning' : 'info') }}">
                                        {{ $insurance->days_until_expiry }} days
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No upcoming insurance expirations</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- License Expirations -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upcoming License Expirations</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingDeadlines['licenses'] as $license)
                            <tr>
                                <td>{{ $license->driver->name }}</td>
                                <td>{{ $license->expiry_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $license->days_until_expiry <= 30 ? 'danger' : ($license->days_until_expiry <= 60 ? 'warning' : 'info') }}">
                                        {{ $license->days_until_expiry }} days
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No upcoming license expirations</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspection Expirations -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upcoming Inspection Expirations</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingDeadlines['inspections'] as $inspection)
                            <tr>
                                <td>{{ $inspection->vehicle->registration_number }}</td>
                                <td>{{ $inspection->next_service_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $inspection->days_until_expiry <= 30 ? 'danger' : ($inspection->days_until_expiry <= 60 ? 'warning' : 'info') }}">
                                        {{ $inspection->days_until_expiry }} days
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No upcoming inspection expirations</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>