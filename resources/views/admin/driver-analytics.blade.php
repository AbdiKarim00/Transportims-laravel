@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Driver Analytics Dashboard</h1>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.driver-analytics', ['tab' => 'overview']) }}" id="overview-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab', 'overview') === 'overview' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-chart-line mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.driver-analytics', ['tab' => 'licenses']) }}" id="licenses-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'licenses' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-id-card mr-2"></i> License Management
            </a>
            <a href="{{ route('admin.driver-analytics', ['tab' => 'status']) }}" id="status-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'status' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-user-clock mr-2"></i> Status History
            </a>
            <a href="{{ route('admin.driver-analytics', ['tab' => 'performance']) }}" id="performance-tab" data-toggle="tab" role="tab" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') === 'performance' ? 'border-blue-500 text-blue-600' : '' }}">
                <i class="fas fa-chart-bar mr-2"></i> Performance
            </a>
        </nav>
    </div>

    <!-- Tab Content -->
    <div id="driverAnalyticsTabContent" class="mt-6">
        <div id="overview" role="tabpanel" class="tab-pane {{ request('tab', 'overview') === 'overview' ? '' : 'hidden' }}">
            @include('admin.driver-analytics.partials.overview')
        </div>
        <div id="licenses" role="tabpanel" class="tab-pane {{ request('tab') === 'licenses' ? '' : 'hidden' }}">
            @include('admin.driver-analytics.partials.licenses')
        </div>
        <div id="status" role="tabpanel" class="tab-pane {{ request('tab') === 'status' ? '' : 'hidden' }}">
            @include('admin.driver-analytics.partials.status')
        </div>
        <div id="performance" role="tabpanel" class="tab-pane {{ request('tab') === 'performance' ? '' : 'hidden' }}">
            @include('admin.driver-analytics.partials.performance')
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