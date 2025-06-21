@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Provider Management</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.vehicle-analytics.service-providers') }}" method="GET" class="form-inline">
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
                                    <h3>{{ $providerStats->total_providers }}</h3>
                                    <p>Total Providers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $providerStats->active_providers }}</h3>
                                    <p>Active Providers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($providerStats->avg_rating, 1) }}</h3>
                                    <p>Average Rating</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Provider Performance -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Provider Performance</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Provider</th>
                                                    <th>Total Services</th>
                                                    <th>Total Cost</th>
                                                    <th>Rating</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($providerPerformance as $performance)
                                                <tr>
                                                    <td>{{ $performance['provider']->name }}</td>
                                                    <td>{{ $performance['total_services'] }}</td>
                                                    <td>{{ number_format($performance['total_cost'], 2) }}</td>
                                                    <td>
                                                        <div class="rating">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $performance['avg_rating'] ? 'text-warning' : 'text-muted' }}"></i>
                                                                @endfor
                                                        </div>
                                                    </td>
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
                                    <h3 class="card-title">Service History</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Provider</th>
                                                    <th>Vehicle</th>
                                                    <th>Service Date</th>
                                                    <th>Service Type</th>
                                                    <th>Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($serviceHistory as $provider)
                                                @foreach($provider->maintenanceRecords as $record)
                                                <tr>
                                                    <td>{{ $provider->name }}</td>
                                                    <td>{{ $record->vehicle->registration_no }}</td>
                                                    <td>{{ $record->service_date->format('Y-m-d') }}</td>
                                                    <td>{{ $record->type->name }}</td>
                                                    <td>{{ number_format($record->cost, 2) }}</td>
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