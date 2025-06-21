@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">New Maintenance Record</h1>
        <a href="{{ route('admin.maintenance.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Maintenance
        </a>
    </div>

    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.maintenance.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Maintenance Details -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Maintenance Details</h2>
                    
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-muted-foreground">Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_number }} ({{ $vehicle->model }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-muted-foreground">Type</label>
                        <select name="type" id="type" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Type</option>
                            @foreach(['routine', 'repair', 'inspection', 'emergency'] as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-muted-foreground">Status</label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                            @foreach(['scheduled', 'in_progress', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Schedule and Cost -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-foreground">Schedule and Cost</h2>
                    
                    <div>
                        <label for="date" class="block text-sm font-medium text-muted-foreground">Date</label>
                        <input type="date" name="date" id="date" value="{{ old('date') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('date')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cost" class="block text-sm font-medium text-muted-foreground">Cost</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-muted-foreground sm:text-sm">$</span>
                            </div>
                            <input type="number" name="cost" id="cost" value="{{ old('cost') }}" step="0.01" min="0" required
                                class="pl-7 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                        @error('cost')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mileage" class="block text-sm font-medium text-muted-foreground">Mileage</label>
                        <input type="number" name="mileage" id="mileage" value="{{ old('mileage') }}" min="0" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('mileage')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="space-y-4 md:col-span-2">
                    <h2 class="text-lg font-medium text-foreground">Additional Details</h2>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-muted-foreground">Description</label>
                        <textarea name="description" id="description" rows="3" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="service_provider" class="block text-sm font-medium text-muted-foreground">Service Provider</label>
                        <input type="text" name="service_provider" id="service_provider" value="{{ old('service_provider') }}" required
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('service_provider')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="next_maintenance_date" class="block text-sm font-medium text-muted-foreground">Next Maintenance Date</label>
                        <input type="date" name="next_maintenance_date" id="next_maintenance_date" value="{{ old('next_maintenance_date') }}"
                            class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        @error('next_maintenance_date')
                            <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.maintenance.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Maintenance Record</button>
            </div>
        </form>
    </div>
</div>
@endsection 