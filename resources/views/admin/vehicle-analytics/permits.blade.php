@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Vehicle Permits & Compliance</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.vehicle-analytics.permits') }}" method="GET" class="form-inline">
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
                                    <h3>{{ $permitStats->total_permits }}</h3>
                                    <p>Total Permits</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $permitStats->active_permits }}</h3>
                                    <p>Active Permits</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $permitStats->expiring_soon }}</h3>
                                    <p>Expiring Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permits by Status Chart -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Permits by Status</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="permitsByStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Expiring Permits</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Vehicle</th>
                                                    <th>Permit Type</th>
                                                    <th>Expiry Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($expiringPermits as $permit)
                                                <tr>
                                                    <td>{{ $permit->vehicle->registration_no }}</td>
                                                    <td>{{ $permit->permit_type }}</td>
                                                    <td>{{ $permit->expiry_date->format('Y-m-d') }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $permit->status === 'active' ? 'success' : 'warning' }}">
                                                            {{ $permit->status }}
                                                        </span>
                                                    </td>
                                                </tr>
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
        // Permits by Status Chart
        var ctx = document.getElementById('permitsByStatusChart').getContext('2d');
        var permitsByStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {
                    !!json_encode($permitsByStatus - > pluck('status')) !!
                },
                datasets: [{
                    data: {
                        !!json_encode($permitsByStatus - > pluck('total')) !!
                    },
                    backgroundColor: [
                        '#28a745', // Active
                        '#ffc107', // Pending
                        '#dc3545', // Expired
                        '#17a2b8' // Other
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endpush