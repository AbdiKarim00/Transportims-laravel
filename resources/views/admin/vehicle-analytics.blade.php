@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Vehicle Analytics Dashboard</h1>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'overview']) }}" id="overview-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab', 'overview') === 'overview' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-chart-line mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'permits']) }}" id="permits-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'permits' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-id-card mr-2"></i> Drivers' Licenses
            </a>
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'odometer']) }}" id="odometer-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'odometer' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i> Odometer & Maintenance
            </a>
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'equipment']) }}" id="equipment-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'equipment' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-tools mr-2"></i> Equipment Management
            </a>
            <a href="{{ route('admin.vehicle-analytics', ['tab' => 'service-providers']) }}" id="service-providers-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'service-providers' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-building mr-2"></i> Service Providers
            </a>
        </nav>
    </div>

    <!-- Tab Content -->
    <div id="vehicleAnalyticsTabContent" class="mt-6">
        <div id="overview" role="tabpanel" class="tab-pane {{ request('tab', 'overview') === 'overview' ? '' : 'hidden' }}">
            @include('admin.vehicle-analytics.partials.overview')
        </div>
        <div id="permits" role="tabpanel" class="tab-pane {{ request('tab') === 'permits' ? '' : 'hidden' }}">
            @include('admin.vehicle-analytics.partials.permits')
        </div>
        <div id="odometer" role="tabpanel" class="tab-pane {{ request('tab') === 'odometer' ? '' : 'hidden' }}">
            @include('admin.vehicle-analytics.partials.odometer')
        </div>
        <div id="equipment" role="tabpanel" class="tab-pane {{ request('tab') === 'equipment' ? '' : 'hidden' }}">
            @include('admin.vehicle-analytics.partials.equipment')
        </div>
        <div id="service-providers" role="tabpanel" class="tab-pane {{ request('tab') === 'service-providers' ? '' : 'hidden' }}">
            @include('admin.vehicle-analytics.partials.service-providers')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab clicks
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                window.location.href = url.toString();
            });
        });
    });
</script>
@endpush