@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Drivers</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Driver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Personal Number</th>
                                    <th>Department</th>
                                    <th>Office</th>
                                    <th>Status</th>
                                    <th>License Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drivers as $driver)
                                <tr>
                                    <td>{{ $driver->id }}</td>
                                    <td>{{ $driver->first_name }} {{ $driver->last_name }}</td>
                                    <td>{{ $driver->personal_number }}</td>
                                    <td>{{ $driver->department->name ?? 'N/A' }}</td>
                                    <td>{{ $driver->office->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $driver->status->color ?? 'secondary' }}">
                                            {{ $driver->status->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($driver->licenses->isNotEmpty())
                                        @php
                                        $activeLicense = $driver->licenses->where('status', 'active')->first();
                                        @endphp
                                        @if($activeLicense)
                                        <span class="badge badge-success">Active</span>
                                        @else
                                        <span class="badge badge-danger">Expired</span>
                                        @endif
                                        @else
                                        <span class="badge badge-warning">No License</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.drivers.show', $driver) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.drivers.edit', $driver) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.drivers.destroy', $driver) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this driver?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $drivers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection