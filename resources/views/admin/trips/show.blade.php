@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Trip Details</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.trips.edit', $trip) }}" class="btn-secondary">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('admin.trips.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Trips
            </a>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Trip Information -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Trip Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($trip->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Purpose</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->purpose }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Destination</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->destination }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Notes</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->notes ?? 'No notes' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Schedule -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Schedule</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Start Time</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->start_time->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">End Time</dt>
                        <dd class="mt-1 text-sm text-foreground">
                            {{ $trip->end_time ? $trip->end_time->format('M d, Y H:i') : 'Not set' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Duration</dt>
                        <dd class="mt-1 text-sm text-foreground">
                            @if($trip->end_time)
                                {{ $trip->start_time->diffForHumans($trip->end_time, ['parts' => 2]) }}
                            @else
                                Not completed
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Driver Information -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Driver Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Name</dt>
                        <dd class="mt-1 text-sm text-foreground">
                            {{ $trip->driver->first_name }} {{ $trip->driver->last_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Personal Number</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->driver->personal_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Phone</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->driver->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->driver->email }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Vehicle Information -->
        <div class="bg-card rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground mb-4">Vehicle Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Plate Number</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->vehicle->plate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Model</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->vehicle->model }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Type</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ $trip->vehicle->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $trip->vehicle->status === 'available' ? 'bg-green-100 text-green-800' : 
                                   ($trip->vehicle->status === 'maintenance' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($trip->vehicle->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection 