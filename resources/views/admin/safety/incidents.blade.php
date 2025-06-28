@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Incident Reports</h1>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-muted-foreground hover:text-primary">Overview</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <a href="{{ route('admin.safety-dashboard') }}" class="text-muted-foreground hover:text-primary">Safety Dashboard</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-muted-foreground mx-2"></i>
                    <span class="text-foreground">Incident Reports</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Filters Section -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-lg font-medium text-foreground">Filters</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.safety.incidents') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label for="severity" class="block text-sm font-medium text-muted-foreground">Severity</label>
                    <select name="severity" id="severity" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary sm:text-sm">
                        <option value="">All Severities</option>
                        @foreach($severities as $severity)
                        <option value="{{ $severity->id }}" {{ request('severity') == $severity->id ? 'selected' : '' }}>
                            {{ $severity->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label for="type" class="block text-sm font-medium text-muted-foreground">Type</label>
                    <select name="type" id="type" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary sm:text-sm">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label for="status" class="block text-sm font-medium text-muted-foreground">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary sm:text-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label for="date_from" class="block text-sm font-medium text-muted-foreground">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary sm:text-sm" value="{{ request('date_from') }}">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label for="date_to" class="block text-sm font-medium text-muted-foreground">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="mt-1 block w-full px-3 py-2 bg-background border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary sm:text-sm" value="{{ request('date_to') }}">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-filter fa-sm mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Incidents Table Section -->
    <div class="mt-4 bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-list-alt text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Incidents List</h3>
            </div>
            <a href="#" class="text-sm text-primary hover:text-primary/80 flex items-center">
                <i class="fas fa-plus-circle mr-1"></i> Add Incident
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($incidents as $incident)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $incident->incident_date->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $incident->vehicle->registration_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ optional($incident->driver)->name ?: 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $incident->type->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $incident->severity->name === 'Critical' ? 'bg-red-100 text-red-800' :
                                   ($incident->severity->name === 'High' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-green-100 text-green-800') }}">
                                {{ $incident->severity->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $incident->status->name === 'Resolved' ? 'bg-green-100 text-green-800' :
                                   ($incident->status->name === 'Under Investigation' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-blue-100 text-blue-800') }}">
                                {{ $incident->status->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ Str::limit($incident->description, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                            <a href="#" class="text-primary hover:text-primary/80">View</a>
                            <a href="#" class="text-blue-600 hover:text-blue-800">Edit</a>
                            <a href="#" class="text-red-600 hover:text-red-800">Delete</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-muted-foreground">No incidents found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-border">
            {{ $incidents->links() }}
        </div>
    </div>
</div>
@endsection