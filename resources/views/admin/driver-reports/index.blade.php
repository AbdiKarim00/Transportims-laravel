@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Driver Reports</h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Generate Driver Report</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.driver-reports.generate') }}" method="POST" id="reportForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type">Report Type</label>
                                    <select class="form-control @error('report_type') is-invalid @enderror"
                                        id="report_type" name="report_type" required>
                                        <option value="">Select Report Type</option>
                                        @foreach($reportTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('report_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="driver_id">Driver</label>
                                    <select class="form-control @error('driver_id') is-invalid @enderror"
                                        id="driver_id" name="driver_id" required>
                                        <option value="">Select Driver</option>
                                        @foreach(\App\Models\Driver::all() as $driver)
                                        <option value="{{ $driver->id }}">
                                            {{ $driver->first_name }} {{ $driver->last_name }}
                                            ({{ $driver->personal_number }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date" required>
                                    @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date" required>
                                    @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="format">Export Format</label>
                                    <select class="form-control @error('format') is-invalid @enderror"
                                        id="format" name="format" required>
                                        <option value="">Select Format</option>
                                        <option value="csv">CSV</option>
                                        <option value="excel">Excel</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                    @error('format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-export mr-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default dates
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);

        document.getElementById('start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('end_date').value = today.toISOString().split('T')[0];

        // Form validation
        const form = document.getElementById('reportForm');
        form.addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);

            if (endDate < startDate) {
                e.preventDefault();
                alert('End date cannot be earlier than start date');
            }
        });
    });
</script>
@endpush
@endsection