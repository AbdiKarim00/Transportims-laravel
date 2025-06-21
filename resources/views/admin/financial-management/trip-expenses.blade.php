@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Trip Expenses Management</h1>
            <p class="text-sm text-muted-foreground">Monitor and manage trip-related expenses</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-border mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.financial-management') }}" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-chart-pie mr-2"></i> Overview
            </a>
            <a href="{{ route('admin.financial-management.fuel-reports') }}?tab=fuel-reports" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-gas-pump mr-2"></i> Fuel Reports
            </a>
            <a href="{{ route('admin.financial-management.fuel-cards') }}?tab=fuel-cards" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-credit-card mr-2"></i> Fuel Cards
            </a>
            <a href="{{ route('admin.financial-management.trip-expenses') }}?tab=trip-expenses" class="border-primary text-primary whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-receipt mr-2"></i> Trip Expenses
            </a>
            <a href="{{ route('admin.financial-management.insurance') }}?tab=insurance" class="border-transparent text-muted-foreground hover:text-foreground hover:border-border whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-file-contract mr-2"></i> Insurance
            </a>
        </nav>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Trip Expenses -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Trip Expenses</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($summaryMetrics['trip_expenses'], 2) }}</h2>
                    </div>
                    <i class="fas fa-receipt text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Current period</span>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Pending Approvals</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $pendingExpenses->count() }}</h2>
                    </div>
                    <i class="fas fa-clock text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Awaiting review</span>
            </div>
        </div>

        <!-- Total Trips -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Total Trips</h6>
                        <h2 class="text-2xl font-bold text-foreground">{{ $summaryMetrics['total_trips'] }}</h2>
                    </div>
                    <i class="fas fa-route text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Current period</span>
            </div>
        </div>

        <!-- Average Expense -->
        <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-muted-foreground">Average Expense</h6>
                        <h2 class="text-2xl font-bold text-foreground">KSh {{ number_format($summaryMetrics['trip_expenses'] / max(1, $summaryMetrics['total_trips']), 2) }}</h2>
                    </div>
                    <i class="fas fa-calculator text-2xl text-primary"></i>
                </div>
            </div>
            <div class="bg-muted px-4 py-2">
                <span class="text-sm text-muted-foreground">Per trip</span>
            </div>
        </div>
    </div>

    <!-- Expense Categories -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Expense Categories</h3>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($expenseCategories as $category)
                <div class="bg-muted rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-foreground">{{ ucfirst($category->category) }}</span>
                        <span class="text-sm text-muted-foreground">{{ $category->count }} expenses</span>
                    </div>
                    <div class="text-lg font-bold text-foreground">KSh {{ number_format($category->total_amount, 2) }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Recent Expenses</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Trip</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($recentExpenses as $expense)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $expense->trip->trip_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ ucfirst($expense->category) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $expense->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $expense->status === 'approved' ? 'bg-green-100 text-green-800' : ($expense->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border">
            {{ $recentExpenses->appends(['tab' => 'trip-expenses'])->links() }}
        </div>
    </div>

    <!-- Pending Approvals -->
    @if($pendingExpenses->count() > 0)
    <div class="bg-card text-card-foreground rounded-lg shadow-md mb-6 animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clock text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Pending Approvals</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Trip</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($pendingExpenses as $expense)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $expense->trip->trip_number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ ucfirst($expense->category) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">KSh {{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $expense->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            <form action="{{ route('admin.financial-management.approve-expense', $expense->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" name="status" value="approved" class="btn-primary text-sm mr-2">Approve</button>
                                <button type="submit" name="status" value="rejected" class="btn-danger text-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Top Spending Trips -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md animate-fade-in">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar text-primary mr-2"></i>
                    <h3 class="text-lg font-medium text-foreground">Top Spending Trips</h3>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($topSpendingTrips as $trip)
                <div class="bg-muted rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-foreground">Trip #{{ $trip->trip->trip_number ?? 'N/A' }}</span>
                        <span class="text-sm text-muted-foreground">{{ $trip->expense_count }} expenses</span>
                    </div>
                    <div class="text-lg font-bold text-foreground">KSh {{ number_format($trip->total_amount, 2) }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare data for the chart
        const months = {
            !!json_encode($expenseTrends - > keys()) !!
        };
        const categories = {
            !!json_encode(collect($expenseTrends - > first()) - > pluck('category')) !!
        };

        const datasets = categories.map(category => {
            const data = {
                !!json_encode($expenseTrends - > map(function($trend) use($category) {
                    return $trend - > where('category', $category) - > sum('total_amount');
                })) !!
            };

            return {
                label: category,
                data: data,
                borderColor: getRandomColor(),
                tension: 0.1
            };
        });

        // Expense Trends Chart
        const expenseTrendsCtx = document.getElementById('expenseTrendsChart').getContext('2d');
        new Chart(expenseTrendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });

    // Helper function to generate random colors
    function getRandomColor() {
        const colors = [
            'rgb(59, 130, 246)', // blue
            'rgb(16, 185, 129)', // green
            'rgb(245, 158, 11)', // yellow
            'rgb(139, 92, 246)', // purple
            'rgb(239, 68, 68)', // red
            'rgb(14, 165, 233)' // sky
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
</script>
@endpush
@endsection