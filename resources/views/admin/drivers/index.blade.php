@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-foreground">Drivers</h1>
        <a href="{{ route('admin.drivers.create') }}" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Add Driver
        </a>
    </div>

    <div class="mt-4 bg-card rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Personal Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($drivers as $driver)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $driver->first_name }} {{ $driver->last_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-muted-foreground">{{ $driver->personal_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-muted-foreground">{{ $driver->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-muted-foreground">{{ $driver->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $driver->status->name === 'active' ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">
                                {{ ucfirst($driver->status->name) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.drivers.show', $driver) }}" class="text-primary hover:text-primary/80">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.drivers.edit', $driver) }}" class="text-primary hover:text-primary/80">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-destructive hover:text-destructive/80" onclick="return confirm('Are you sure you want to delete this driver?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-muted-foreground">
                            No drivers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-muted border-t border-border">
            {{ $drivers->links() }}
        </div>
    </div>
</div>
@endsection