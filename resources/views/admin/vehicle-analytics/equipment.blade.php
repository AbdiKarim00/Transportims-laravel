@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plant Equipment Management</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.vehicle-analytics.equipment') }}" method="GET" class="form-inline">
                            <div class="input-group input-group-sm">
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $equipmentStats->total_equipment }}</h3>
                                    <p>Total Equipment</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $equipmentStats->active_equipment }}</h3>
                                    <p>Active Equipment</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $equipmentStats->in_maintenance }}</h3>
                                    <p>In Maintenance</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wrench"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Utilization -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Equipment Utilization</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Equipment</th>
                                                    <th>Fuel Usage</th>
                                                    <th>Maintenance Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($equipmentUtilization as $utilization)
                                                <tr>
                                                    <td>{{ $utilization['equipment']->name }}</td>
                                                    <td>{{ number_format($utilization['fuel_usage'], 2) }} L</td>
                                                    <td>{{ $utilization['maintenance_count'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Equipment Assignments</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Equipment</th>
                                                    <th>Assigned To</th>
                                                    <th>Assignment Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($equipmentAssignments as $equipment)
                                                @foreach($equipment->assignments as $assignment)
                                                <tr>
                                                    <td>{{ $equipment->name }}</td>
                                                    <td>{{ $assignment->assigned_to }}</td>
                                                    <td>{{ $assignment->assigned_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $assignment->status === 'active' ? 'success' : 'warning' }}">
                                                            {{ $assignment->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any additional JavaScript for charts or interactivity here
    });
</script>
@endpush