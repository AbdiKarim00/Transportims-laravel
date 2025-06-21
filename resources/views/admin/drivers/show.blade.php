@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Driver Details</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn-secondary">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Drivers
            </a>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Personal Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Full Name</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->first_name }} {{ $driver->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Personal Number</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->personal_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Phone Number</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->email }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Professional Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $driver->status->name === 'active' ? 'bg-green-100 text-green-800' : 
                                   ($driver->status->name === 'inactive' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($driver->status->name) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Joining Date</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->joining_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Blood Type</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->blood_type ?? 'Not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Medical Conditions</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->medical_conditions ?? 'None' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="bg-card rounded-lg shadow overflow-hidden md:col-span-2">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Emergency Contact</h2>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Contact Name</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->emergency_contact_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Contact Phone</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->emergency_contact_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Relationship</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $driver->emergency_contact_relationship }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Recent Trips -->
        <div class="bg-card rounded-lg shadow overflow-hidden md:col-span-2">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Recent Trips</h2>
                @if($driver->trips->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Trip ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($driver->trips->take(5) as $trip)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                                    'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="text-primary hover:text-primary-dark">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-muted-foreground">No trips found for this driver.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection