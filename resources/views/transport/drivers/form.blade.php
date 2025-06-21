@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($driver) ? 'Edit Driver' : 'Add New Driver' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($driver) ? route('admin.drivers.update', $driver) : route('admin.drivers.store') }}" method="POST">
                        @csrf
                        @if(isset($driver))
                        @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Personal Information</h4>
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                        id="first_name" name="first_name"
                                        value="{{ old('first_name', $driver->first_name ?? '') }}" required>
                                    @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                        id="last_name" name="last_name"
                                        value="{{ old('last_name', $driver->last_name ?? '') }}" required>
                                    @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="personal_number">Personal Number</label>
                                    <input type="text" class="form-control @error('personal_number') is-invalid @enderror"
                                        id="personal_number" name="personal_number"
                                        value="{{ old('personal_number', $driver->personal_number ?? '') }}" required>
                                    @error('personal_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone"
                                        value="{{ old('phone', $driver->phone ?? '') }}" required>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email"
                                        value="{{ old('email', $driver->email ?? '') }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4>Professional Information</h4>
                                <div class="form-group">
                                    <label for="department_id">Department</label>
                                    <select class="form-control @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id', $driver->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="office_id">Office</label>
                                    <select class="form-control @error('office_id') is-invalid @enderror"
                                        id="office_id" name="office_id" required>
                                        <option value="">Select Office</option>
                                        @foreach($offices as $office)
                                        <option value="{{ $office->id }}"
                                            {{ old('office_id', $driver->office_id ?? '') == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('office_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status_id">Status</label>
                                    <select class="form-control @error('status_id') is-invalid @enderror"
                                        id="status_id" name="status_id" required>
                                        <option value="">Select Status</option>
                                        @foreach($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ old('status_id', $driver->status_id ?? '') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="joining_date">Joining Date</label>
                                    <input type="date" class="form-control @error('joining_date') is-invalid @enderror"
                                        id="joining_date" name="joining_date"
                                        value="{{ old('joining_date', isset($driver) ? $driver->joining_date->format('Y-m-d') : '') }}" required>
                                    @error('joining_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="blood_type">Blood Type</label>
                                    <select class="form-control @error('blood_type') is-invalid @enderror"
                                        id="blood_type" name="blood_type">
                                        <option value="">Select Blood Type</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                                        <option value="{{ $type }}"
                                            {{ old('blood_type', $driver->blood_type ?? '') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('blood_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="medical_conditions">Medical Conditions</label>
                                    <textarea class="form-control @error('medical_conditions') is-invalid @enderror"
                                        id="medical_conditions" name="medical_conditions" rows="3">{{ old('medical_conditions', $driver->medical_conditions ?? '') }}</textarea>
                                    @error('medical_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h4>Emergency Contact Information</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="emergency_contact_name">Contact Name</label>
                                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                                id="emergency_contact_name" name="emergency_contact_name"
                                                value="{{ old('emergency_contact_name', $driver->emergency_contact_name ?? '') }}" required>
                                            @error('emergency_contact_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="emergency_contact_phone">Contact Phone</label>
                                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                                id="emergency_contact_phone" name="emergency_contact_phone"
                                                value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone ?? '') }}" required>
                                            @error('emergency_contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="emergency_contact_relationship">Relationship</label>
                                            <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                                id="emergency_contact_relationship" name="emergency_contact_relationship"
                                                value="{{ old('emergency_contact_relationship', $driver->emergency_contact_relationship ?? '') }}" required>
                                            @error('emergency_contact_relationship')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($driver) ? 'Update Driver' : 'Create Driver' }}
                                </button>
                                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection