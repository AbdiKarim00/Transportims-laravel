@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-foreground mt-4">Safety Compliance</h1>
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
                    <span class="text-foreground">Compliance</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Compliance Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Total Vehicles</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $complianceStats['total_vehicles'] }}</h2>
                </div>
                <i class="fas fa-truck text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Vehicles with Valid Insurance</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $complianceStats['vehicles_with_valid_insurance'] }}</h2>
                </div>
                <i class="fas fa-shield-alt text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Vehicles with Valid Inspection</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $complianceStats['vehicles_with_valid_inspection'] }}</h2>
                </div>
                <i class="fas fa-clipboard-check text-2xl text-primary"></i>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Drivers with Valid Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $complianceStats['drivers_with_valid_licenses'] }}</h2>
                </div>
                <i class="fas fa-id-card text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-8">
        <!-- Insurance Expirations -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Insurance Expirations</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($upcomingDeadlines['insurance_expirations'] as $insurance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $insurance->vehicle->registration_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $insurance->expiry_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $insurance->expiry_date->diffInDays(now()) <= 30 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $insurance->expiry_date->diffInDays(now()) }} days
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No upcoming insurance expirations</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- License Expirations -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-id-card text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">License Expirations</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($upcomingDeadlines['license_expirations'] as $license)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $license->driver->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $license->expiry_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $license->expiry_date->diffInDays(now()) <= 30 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $license->expiry_date->diffInDays(now()) }} days
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No upcoming license expirations</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inspection Expirations -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
            <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clipboard-check text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Inspection Expirations</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($upcomingDeadlines['inspection_expirations'] as $inspection)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $inspection->vehicle->registration_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $inspection->expiry_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $inspection->expiry_date->diffInDays(now()) <= 30 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $inspection->expiry_date->diffInDays(now()) }} days
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-muted-foreground">No upcoming inspection expirations</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection