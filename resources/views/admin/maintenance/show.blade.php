@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Maintenance Record Details</h1>
        <div class="flex space-x-4">
            <a href="{{ route('admin.maintenance.edit', $maintenance) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                Edit Record
            </a>
            <a href="{{ route('admin.maintenance.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vehicle Information -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Vehicle Information</h2>
                <div>
                    <p class="text-sm text-gray-600">Vehicle</p>
                    <p class="font-medium">{{ $maintenance->vehicle->plate_number }} - {{ $maintenance->vehicle->model }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Current Mileage</p>
                    <p class="font-medium">{{ number_format($maintenance->mileage) }} km</p>
                </div>
            </div>

            <!-- Maintenance Details -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Maintenance Details</h2>
                <div>
                    <p class="text-sm text-gray-600">Type</p>
                    <p class="font-medium capitalize">{{ $maintenance->type }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                        @if($maintenance->status === 'completed') bg-green-100 text-green-800
                        @elseif($maintenance->status === 'cancelled') bg-red-100 text-red-800
                        @elseif($maintenance->status === 'in_progress') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                    </span>
                </div>
            </div>

            <!-- Schedule and Cost -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Schedule and Cost</h2>
                <div>
                    <p class="text-sm text-gray-600">Date</p>
                    <p class="font-medium">{{ $maintenance->date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cost</p>
                    <p class="font-medium">${{ number_format($maintenance->cost, 2) }}</p>
                </div>
                @if($maintenance->next_maintenance_date)
                <div>
                    <p class="text-sm text-gray-600">Next Maintenance</p>
                    <p class="font-medium">{{ $maintenance->next_maintenance_date->format('M d, Y') }}</p>
                </div>
                @endif
            </div>

            <!-- Service Provider -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Service Provider</h2>
                <div>
                    <p class="text-sm text-gray-600">Provider Name</p>
                    <p class="font-medium">{{ $maintenance->service_provider }}</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Description</h2>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700 whitespace-pre-line">{{ $maintenance->description }}</p>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                <div>
                    <p>Created: {{ $maintenance->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p>Last Updated: {{ $maintenance->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 