@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Incident Reports</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form action="{{ route('admin.safety.incidents') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="severity">Severity</label>
                                    <select name="severity" id="severity" class="form-control">
                                        <option value="">All Severities</option>
                                        @foreach($severities as $severity)
                                        <option value="{{ $severity->id }}" {{ request('severity') == $severity->id ? 'selected' : '' }}>
                                            {{ $severity->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">All Types</option>
                                        @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Incidents Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Description</th>
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
                                        <span class="badge badge-{{ $incident->severity->name === 'Critical' ? 'danger' : ($incident->severity->name === 'High' ? 'warning' : 'info') }}">
                                            {{ $incident->severity->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $incident->status->name === 'Resolved' ? 'success' : ($incident->status->name === 'In Progress' ? 'warning' : 'secondary') }}">
                                            {{ $incident->status->name }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($incident->description, 100) }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No incidents found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $incidents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection