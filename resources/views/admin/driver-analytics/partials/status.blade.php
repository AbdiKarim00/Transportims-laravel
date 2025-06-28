<!-- Status History Header -->
<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-2xl font-bold text-foreground">Status History</h2>
        <p class="text-sm text-muted-foreground">Track and manage driver status changes</p>
    </div>
    <div class="flex space-x-2">
        <button class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">
            <i class="fas fa-plus mr-2"></i> Update Status
        </button>
        <button class="px-4 py-2 border border-border rounded-md hover:bg-muted">
            <i class="fas fa-filter mr-2"></i> Filter
        </button>
    </div>
</div>

<!-- Status Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Active -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Active</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statusStats['active'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-check text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- On Leave -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">On Leave</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statusStats['on_leave'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-clock text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Suspended -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Suspended</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statusStats['suspended'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-slash text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Terminated -->
    <div class="bg-card text-card-foreground rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-sm font-medium text-muted-foreground">Interdiction</h6>
                    <h2 class="text-2xl font-bold text-foreground">{{ $statusStats['terminated'] ?? 0 }}</h2>
                </div>
                <i class="fas fa-user-times text-2xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- Status Timeline -->
<div class="bg-card text-card-foreground rounded-lg shadow-md mb-6">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-history text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Status Timeline</h3>
            </div>
            <button class="text-sm text-primary hover:text-primary/80">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>
    <div class="p-6">
        <div class="space-y-6">
            @if(isset($statusHistory) && count($statusHistory) > 0)
            @foreach($statusHistory as $history)
            <div class="relative pl-8 pb-6">
                <!-- Timeline connector -->
                @if(!$loop->last)
                <div class="absolute left-4 top-4 bottom-0 w-0.5 bg-border"></div>
                @endif

                <!-- Timeline dot -->
                <div class="absolute left-0 top-4 w-8 h-8 rounded-full border-2 border-primary bg-card flex items-center justify-center">
                    <i class="fas fa-circle text-primary text-xs"></i>
                </div>

                <!-- Content -->
                <div class="bg-muted rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-sm font-medium text-foreground">{{ is_array($history) ? $history['driver']['name'] : ($history->driver ? $history->driver->first_name . ' ' . $history->driver->last_name : 'Unknown Driver') }}</h4>
                            <p class="text-sm text-muted-foreground">{{ is_array($history) ? $history['description'] : ('Status changed to ' . $history->status) }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-muted-foreground">{{
                                    is_array($history) 
                                        ? (isset($history['created_at']) && $history['created_at'] instanceof \DateTime 
                                            ? $history['created_at']->format('M d, Y H:i') 
                                            : 'Unknown date') 
                                        : ($history->created_at ? $history->created_at->format('M d, Y H:i') : 'Unknown date') 
                                }}</span>
                            <div class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ 
                                            (is_array($history) ? ($history['new_status'] ?? '') : ($history->status ?? '')) === 'Active' 
                                                ? 'bg-green-100 text-green-800' 
                                                : ((is_array($history) ? ($history['new_status'] ?? '') : ($history->status ?? '')) === 'On Leave' 
                                                    ? 'bg-yellow-100 text-yellow-800' 
                                                    : 'bg-red-100 text-red-800') 
                                        }}">
                                    {{ is_array($history) ? ($history['new_status'] ?? 'Unknown') : ($history->status ?? 'Unknown') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if(is_array($history) ? !empty($history['notes']) : !empty($history->notes))
                    <div class="mt-2 text-sm text-muted-foreground">
                        <i class="fas fa-comment-alt mr-1"></i> {{ is_array($history) ? ($history['notes'] ?? '') : ($history->notes ?? '') }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            @else
            <div class="text-center p-4 text-muted-foreground">
                No status history available.
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Current Status -->
<div class="bg-card text-card-foreground rounded-lg shadow-md">
    <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-user text-primary mr-2"></i>
                <h3 class="text-lg font-medium text-foreground">Current Status</h3>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Current Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Since</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-border">
                @if(isset($currentStatus) && count($currentStatus) > 0)
                @foreach($currentStatus as $status)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="{{ $status->driver->avatar_url ?? asset('images/default-avatar.png') }}" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-foreground">{{ $status->driver ? $status->driver->first_name . ' ' . $status->driver->last_name : 'Unknown Driver' }}</div>
                                <div class="text-sm text-muted-foreground">{{ $status->driver->employee_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $status->status === 'Active' ? 'bg-green-100 text-green-800' : 
                                   ($status->status === 'On Leave' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $status->status ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $status->created_at ? $status->created_at->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-muted-foreground">{{ Str::limit($status->notes ?? '', 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                        <div class="flex space-x-2">
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-history"></i>
                            </button>
                            <button class="text-primary hover:text-primary/80">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-muted-foreground">
                        No current status records found.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-border">
        @if(isset($currentStatus) && method_exists($currentStatus, 'links'))
        {{ $currentStatus->links() }}
        @endif
    </div>
</div>