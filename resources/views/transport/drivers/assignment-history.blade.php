@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Vehicle Assignment History for {{ $driver->first_name }} {{ $driver->last_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Driver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Type</th>
                                    <th>Assigned Date</th>
                                    <th>Unassigned Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Unassignment Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->plate_number }}</td>
                                    <td>{{ $assignment->type->name }}</td>
                                    <td>{{ $assignment->pivot->assigned_at->format('Y-m-d') }}</td>
                                    <td>{{ $assignment->pivot->unassigned_at ? $assignment->pivot->unassigned_at->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        @if($assignment->pivot->unassigned_at)
                                        <span class="badge badge-danger">Unassigned</span>
                                        @else
                                        <span class="badge badge-success">Active</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->pivot->notes ?? 'N/A' }}</td>
                                    <td>{{ $assignment->pivot->unassignment_reason ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No vehicle assignments found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection