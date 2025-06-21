<!-- Incident Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Filter Incidents</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.safety.incidents') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select class="form-select" id="vehicle_id" name="vehicle_id">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->registration_number }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="driver_id" class="form-label">Driver</label>
                <select class="form-select" id="driver_id" name="driver_id">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                        {{ $driver->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="type_id" class="form-label">Incident Type</label>
                <select class="form-select" id="type_id" name="type_id">
                    <option value="">All Types</option>
                    @foreach($incidentTypes as $type)
                    <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="severity_id" class="form-label">Severity</label>
                <select class="form-select" id="severity_id" name="severity_id">
                    <option value="">All Severities</option>
                    @foreach($incidentSeverities as $severity)
                    <option value="{{ $severity->id }}" {{ request('severity_id') == $severity->id ? 'selected' : '' }}>
                        {{ $severity->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date"
                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date"
                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Incidents List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Incidents</h3>
        <div class="card-tools">
            <a href="{{ route('admin.safety.incidents.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Incident
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $incident)
                    <tr>
                        <td>{{ $incident->incident_date->format('Y-m-d H:i') }}</td>
                        <td>{{ $incident->vehicle->registration_number }}</td>
                        <td>{{ $incident->driver->name }}</td>
                        <td>{{ $incident->type->name }}</td>
                        <td>
                            <span class="badge bg-{{ $incident->severity->name === 'Critical' ? 'danger' : ($incident->severity->name === 'High' ? 'warning' : 'info') }}">
                                {{ $incident->severity->name }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $incident->status->name === 'Resolved' ? 'success' : ($incident->status->name === 'In Progress' ? 'warning' : 'secondary') }}">
                                {{ $incident->status->name }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.safety.incidents.show', $incident) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.safety.incidents.edit', $incident) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No incidents found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $incidents->links() }}
    </div>
</div>