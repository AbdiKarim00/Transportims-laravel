<!-- License Management Header -->
<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold text-foreground">License Management</h2>
        <p class="text-sm text-muted-foreground">Track and manage driver license status and renewals</p>
    </div>
    <button class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
        <i class="fas fa-plus mr-2"></i> Add New License
    </button>
</div>

<!-- License Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- Valid Licenses -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Valid Licenses</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $licenseStats['valid'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-check-circle text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Expiring Soon</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $licenseStats['expiring_soon'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Expired -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Expired</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $licenseStats['expired'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-times-circle text-2xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- License List -->
<div class="bg-card text-card-foreground rounded-lg shadow-md">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-id-card text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Driver Licenses</h3>
            </div>
            <div class="flex space-x-2">
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
                <button class="text-sm text-primary hover:text-primary/80">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border">
            <thead class="bg-muted">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Driver</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">License Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Issue Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expiry Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @if(isset($licenses) && count($licenses) > 0)
                @foreach($licenses as $license)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="{{ $license->driver->avatar_url ?? asset('images/default-avatar.png') }}" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-foreground">{{ $license->driver ? $license->driver->first_name . ' ' . $license->driver->last_name : 'Unknown Driver' }}</div>
                                <div class="text-sm text-muted-foreground">{{ $license->driver->employee_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $license->license_number ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $license->issue_date ? $license->issue_date->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $license->expiry_date ? $license->expiry_date->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $license->status === 'Valid' ? 'bg-green-100 text-green-800' : 
                                   ($license->status === 'Expiring Soon' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $license->status ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        <div class="flex space-x-2">
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-file-download"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-muted-foreground">
                        No license records found.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-border">
        @if(isset($licenses) && method_exists($licenses, 'links'))
        {{ $licenses->links() }}
        @endif
    </div>
</div>

<!-- Renewal Reminders -->
<div class="mt-6 bg-card text-card-foreground rounded-lg shadow-md">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-bell text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Renewal Reminders</h3>
            </div>
            <button class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            @if(isset($renewalReminders) && count($renewalReminders) > 0)
            @foreach($renewalReminders as $reminder)
            <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <img class="h-12 w-12 rounded-full" src="{{ $reminder['driver']['avatar_url'] ?? asset('images/default-avatar.png') }}" alt="">
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-foreground">{{ $reminder['driver']['name'] ?? 'Unknown Driver' }}</h4>
                        <p class="text-sm text-muted-foreground">License expires in {{ $reminder['days_until_expiry'] ?? 'N/A' }} days</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
                        Send Reminder
                    </button>
                    <button class="px-3 py-1 text-sm border border-border rounded-md hover:bg-muted">
                        Schedule Renewal
                    </button>
                </div>
            </div>
            @endforeach
            @else
            <div class="p-4 text-center text-muted-foreground">
                No licenses expiring soon.
            </div>
            @endif
        </div>
    </div>
</div>