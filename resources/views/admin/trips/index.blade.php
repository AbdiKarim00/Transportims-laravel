@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Trips</h1>
        <a href="{{ route('admin.trips.create') }}" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>New Trip
        </a>
    </div>

    <!-- Filters -->
    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <div class="p-4">
            <form action="{{ route('admin.trips.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-muted-foreground">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">All Statuses</option>
                        @foreach(['scheduled', 'in_progress', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="driver" class="block text-sm font-medium text-muted-foreground">Driver</label>
                    <select name="driver_id" id="driver" class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->first_name }} {{ $driver->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-muted-foreground">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-muted-foreground">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="mt-1 block w-full rounded-md border-border bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trips Table -->
    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Trip ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($trips as $trip)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $trip->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                {{ $trip->driver->first_name }} {{ $trip->driver->last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                {{ $trip->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                        'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($trip->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                <div class="flex space-x-3">
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="text-primary hover:text-primary-dark">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="text-primary hover:text-primary-dark">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.trips.destroy', $trip) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-destructive hover:text-destructive-dark" onclick="return confirm('Are you sure you want to delete this trip?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-muted-foreground">
                                No trips found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-border">
            {{ $trips->links() }}
        </div>
    </div>
</div>
@endsection 