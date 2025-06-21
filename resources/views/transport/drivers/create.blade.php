@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Add New Driver</h1>
        <a href="{{ route('admin.drivers.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Drivers
        </a>
    </div>

    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.drivers.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Personal Information</h2>
                    
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-muted-foreground">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('first_name')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-muted-foreground">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('last_name')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="personal_number" class="block text-sm font-medium text-muted-foreground">Personal Number</label>
                        <input type="text" name="personal_number" id="personal_number" value="{{ old('personal_number') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('personal_number')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-muted-foreground">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('phone')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-muted-foreground">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('email')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Professional Information</h2>
                    
                    <div>
                        <label for="status_id" class="block text-sm font-medium text-muted-foreground">Status</label>
                        <select name="status_id" id="status_id" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ ucfirst($status->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_id')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="joining_date" class="block text-sm font-medium text-muted-foreground">Joining Date</label>
                        <input type="date" name="joining_date" id="joining_date" value="{{ old('joining_date') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('joining_date')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-muted-foreground">Blood Type</label>
                        <select name="blood_type" id="blood_type"
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Blood Type</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                                <option value="{{ $type }}" {{ old('blood_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('blood_type')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="medical_conditions" class="block text-sm font-medium text-muted-foreground">Medical Conditions</label>
                        <textarea name="medical_conditions" id="medical_conditions" rows="3"
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">{{ old('medical_conditions') }}</textarea>
                        @error('medical_conditions')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="space-y-4 md:col-span-2">
                    <h2 class="text-lg font-medium text-foreground">Emergency Contact</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="emergency_contact_name" class="block text-sm font-medium text-muted-foreground">Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required
                                class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @error('emergency_contact_name')
                                <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="emergency_contact_phone" class="block text-sm font-medium text-muted-foreground">Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required
                                class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @error('emergency_contact_phone')
                                <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="emergency_contact_relationship" class="block text-sm font-medium text-muted-foreground">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" required
                                class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @error('emergency_contact_relationship')
                                <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.drivers.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Driver</button>
            </div>
        </form>
    </div>
</div>
@endsection 