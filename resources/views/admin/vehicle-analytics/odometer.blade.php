@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Odometer Tracking & Maintenance</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.vehicle-analytics.odometer') }}" method="GET" class="form-inline">
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
                                    <h3>{{ number_format($odometerStats->avg_reading) }}</h3>
                                    <p>Average Reading</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-tachometer-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($odometerStats->max_reading) }}</h3>
                                    <p>Highest Reading</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($odometerStats->min_reading) }}</h3>
                                    <p>Lowest Reading</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Odometer Trends -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Top Vehicles by Distance Traveled</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Vehicle</th>
                                                    <th>Distance Traveled</th>
                                                    <th>Readings Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($odometerTrends as $trend)
                                                <tr>
                                                    <td>{{ $trend->vehicle->registration_no }}</td>
                                                    <td>{{ number_format($trend->distance_traveled) }} km</td>
                                                    <td>{{ $trend->readings_count }}</td>
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
                                    <h3 class="card-title">Maintenance Schedule</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Vehicle</th>
                                                    <th>Current Reading</th>
                                                    <th>Next Maintenance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($maintenanceSchedule as $schedule)
                                                <tr>
                                                    <td>{{ $schedule['vehicle']->registration_no }}</td>
                                                    <td>{{ number_format($schedule['current_reading']) }} km</td>
                                                    <td>
                                                        @if($schedule['next_maintenance'])
                                                        {{ number_format($schedule['next_maintenance']->odometer_threshold) }} km
                                                        ({{ $schedule['next_maintenance']->maintenance_type }})
                                                        @else
                                                        No maintenance scheduled
                                                        @endif
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
        // Add any additional JavaScript for charts or interactivity here
    });
</script>
@endpush