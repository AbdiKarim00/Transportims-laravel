@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Edit Trip</h1>
        <a href="{{ route('admin.trips.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Trips
        </a>
    </div>

    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.trips.update', $trip) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Trip Details -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Trip Details</h2>
                    
                    <div>
                        <label for="driver_id" class="block text-sm font-medium text-muted-foreground">Driver</label>
                        <select name="driver_id" id="driver_id" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $trip->driver_id) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->first_name }} {{ $driver->last_name }} ({{ $driver->personal_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-muted-foreground">Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $trip->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_number }} ({{ $vehicle->model }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-muted-foreground">Status</label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @foreach(['scheduled', 'in_progress', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ old('status', $trip->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Trip Schedule -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Schedule</h2>
                    
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-muted-foreground">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start_time" 
                            value="{{ old('start_time', $trip->start_time->format('Y-m-d\TH:i')) }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('start_time')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-muted-foreground">End Time</label>
                        <input type="datetime-local" name="end_time" id="end_time" 
                            value="{{ old('end_time', $trip->end_time ? $trip->end_time->format('Y-m-d\TH:i') : '') }}"
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('end_time')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Trip Details -->
                <div class="space-y-4 md:col-span-2">
                    <h2 class="text-lg font-medium text-foreground">Additional Details</h2>
                    
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-muted-foreground">Purpose</label>
                        <input type="text" name="purpose" id="purpose" value="{{ old('purpose', $trip->purpose) }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('purpose')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-sm font-medium text-muted-foreground">Destination</label>
                        <input type="text" name="destination" id="destination" value="{{ old('destination', $trip->destination) }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('destination')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-muted-foreground">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">{{ old('notes', $trip->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.trips.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update Trip</button>
            </div>
        </form>
    </div>
</div>
@endsection 