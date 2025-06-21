@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Driver Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Driver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Personal Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $driver->first_name }} {{ $driver->last_name }}</td>
                                </tr>
                                <tr>
                                    <th>Personal Number</th>
                                    <td>{{ $driver->personal_number }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $driver->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $driver->email }}</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td>{{ $driver->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Office</th>
                                    <td>{{ $driver->office->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $driver->status->color ?? 'secondary' }}">
                                            {{ $driver->status->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Joining Date</th>
                                    <td>{{ $driver->joining_date ? $driver->joining_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>License Information</h4>
                            @if($driver->licenses->isNotEmpty())
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>License Number</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->licenses as $license)
                                    <tr>
                                        <td>{{ $license->license_number }}</td>
                                        <td>{{ $license->license_type }}</td>
                                        <td>
                                            <span class="badge badge-{{ $license->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($license->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $license->expiry_date->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#licenseModal{{ $license->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form action="{{ route('admin.drivers.licenses.destroy', [$driver, $license]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this license?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted">No licenses found.</p>
                            @endif
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addLicenseModal">
                                <i class="fas fa-plus"></i> Add License
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Vehicle Assignments</h4>
                            @if($driver->vehicles->isNotEmpty())
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Assigned Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->vehicles as $vehicle)
                                    <tr>
                                        <td>{{ $vehicle->plate_number }} ({{ $vehicle->type->name }})</td>
                                        <td>{{ $vehicle->pivot->assigned_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if($vehicle->pivot->unassigned_at)
                                            <span class="badge badge-danger">Unassigned</span>
                                            @else
                                            <span class="badge badge-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$vehicle->pivot->unassigned_at)
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#unassignModal{{ $vehicle->id }}">
                                                <i class="fas fa-times"></i> Unassign
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted">No vehicle assignments found.</p>
                            @endif
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#assignVehicleModal">
                                <i class="fas fa-plus"></i> Assign Vehicle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add License Modal -->
<div class="modal fade" id="addLicenseModal" tabindex="-1" role="dialog" aria-labelledby="addLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.drivers.licenses.store', $driver) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addLicenseModalLabel">Add New License</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="license_number">License Number</label>
                        <input type="text" class="form-control" id="license_number" name="license_number" required>
                    </div>
                    <div class="form-group">
                        <label for="license_type">License Type</label>
                        <input type="text" class="form-control" id="license_type" name="license_type" required>
                    </div>
                    <div class="form-group">
                        <label for="issuing_authority">Issuing Authority</label>
                        <input type="text" class="form-control" id="issuing_authority" name="issuing_authority" required>
                    </div>
                    <div class="form-group">
                        <label for="issue_date">Issue Date</label>
                        <input type="date" class="form-control" id="issue_date" name="issue_date" required>
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                    </div>
                    <div class="form-group">
                        <label for="restrictions">Restrictions</label>
                        <textarea class="form-control" id="restrictions" name="restrictions" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add License</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Vehicle Modal -->
<div class="modal fade" id="assignVehicleModal" tabindex="-1" role="dialog" aria-labelledby="assignVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.drivers.assignments.assign', $driver) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="assignVehicleModalLabel">Assign Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="vehicle_id">Vehicle</label>
                        <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                            <option value="">Select Vehicle</option>
                            @foreach($availableVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->plate_number }} ({{ $vehicle->type->name }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignment_date">Assignment Date</label>
                        <input type="date" class="form-control" id="assignment_date" name="assignment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="assignment_notes">Notes</label>
                        <textarea class="form-control" id="assignment_notes" name="assignment_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($driver->vehicles as $vehicle)
@if(!$vehicle->pivot->unassigned_at)
<!-- Unassign Vehicle Modal -->
<div class="modal fade" id="unassignModal{{ $vehicle->id }}" tabindex="-1" role="dialog" aria-labelledby="unassignModalLabel{{ $vehicle->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.drivers.assignments.unassign', [$driver, $vehicle]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="unassignModalLabel{{ $vehicle->id }}">Unassign Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unassignment_date">Unassignment Date</label>
                        <input type="date" class="form-control" id="unassignment_date" name="unassignment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="unassignment_reason">Reason</label>
                        <textarea class="form-control" id="unassignment_reason" name="unassignment_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Unassign Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@foreach($driver->licenses as $license)
<!-- License Details Modal -->
<div class="modal fade" id="licenseModal{{ $license->id }}" tabindex="-1" role="dialog" aria-labelledby="licenseModalLabel{{ $license->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="licenseModalLabel{{ $license->id }}">License Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th>License Number</th>
                        <td>{{ $license->license_number }}</td>
                    </tr>
                    <tr>
                        <th>License Type</th>
                        <td>{{ $license->license_type }}</td>
                    </tr>
                    <tr>
                        <th>Issuing Authority</th>
                        <td>{{ $license->issuing_authority }}</td>
                    </tr>
                    <tr>
                        <th>Issue Date</th>
                        <td>{{ $license->issue_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Expiry Date</th>
                        <td>{{ $license->expiry_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $license->status === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($license->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Restrictions</th>
                        <td>{{ $license->restrictions ?? 'None' }}</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection